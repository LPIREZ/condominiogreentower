<?php
/*
Flat File Guestbook PHP script from AdvanceByDesign
Copyright (c) 2011 Robert Rook
Released under the terms of the ABD free software licence.

This file provides an administration area for the guestbook.
*/
require('ffgb_config.php');

$err = "";
$msg = "";

$METHOD_FETCH_ENTRIES = "list";
$COMMENT_COUNT = 0;
$LIST_COUNT = 0;
$FILTER_BY = "name";
$FILTER_MATCH = "";

$EDIT_BULK = false;
$FP_OUT = null;

$BOOK_NUM = 1;
$C_BOOK_NUM = 0;
$COMMENT_IN_BOOK = 0;

/* Logout */
if(isset($_GET['cmd']) && $_GET['cmd']=="logout")
{
	setcookie("username", false);
	setcookie("password", false);
	unset($_COOKIE['username']);
	unset($_COOKIE['password']);
}

/* Attempt a new login */
if(isset($_POST['ffgb_user']) && isset($_POST['ffgb_pass']))
{
	if($_POST['ffgb_user'] && $_POST['ffgb_user']==ADMIN_USER &&
		$_POST['ffgb_pass'] && $_POST['ffgb_pass']==ADMIN_PASS)
	{
		setcookie("username", $_POST['ffgb_user']);
		setcookie("password", md5($_POST['ffgb_pass']));
		$_COOKIE['username'] = $_POST['ffgb_user'];
		$_COOKIE['password'] = md5($_POST['ffgb_pass']);
	}
	else
	{
		$err = "<li>The login information you have provided is incorrect.</li>\n";
	}
}

/* Validate login information */
if(!isset($_COOKIE['username']) || !isset($_COOKIE['password'])
	|| $_COOKIE['username']!=ADMIN_USER || $_COOKIE['password']!=md5(ADMIN_PASS))
{
	define("IS_LOGGED_IN", false);
}
else
{
	define("IS_LOGGED_IN", true);
}

/* Function for displaying/searching entries */
function ffgb_add_comment($is_shown, $is_removed, $ip, $ctime, $name, $email, $url, $subject, $message)
{
	global $LIST_COUNT, $COMMENT_COUNT, $METHOD_FETCH_ENTRIES, $FILTER_BY, $FILTER_MATCH;
	global $EDIT_BULK, $FP_OUT, $_POST, $BOOK_NUM, $COMMENT_IN_BOOK, $C_BOOK_NUM;
	
	if($C_BOOK_NUM!=$BOOK_NUM)
	{
		$C_BOOK_NUM = $BOOK_NUM;
		$COMMENT_IN_BOOK = 0;
	}
	else $COMMENT_IN_BOOK++;
	
	if($METHOD_FETCH_ENTRIES=="list")
	{
		if(isset($_POST['comment_'.$LIST_COUNT]) && $_POST['comment_'.$LIST_COUNT]==$COMMENT_COUNT)
		{
			if($_POST['comment_action']=="activate") { $is_shown = 1; }
			else if($_POST['comment_action']=="deactivate") { $is_shown = 0; }
			else if($_POST['comment_action']=="remove") { $is_removed = 1; }
			else if($_POST['comment_action']=="unremove") { $is_removed = 0; }
		}

		echo "<tr class=\"".($is_removed?"comment_removed":($is_shown?"":"comment_inactive"))."\">";
		echo "<td><input type=\"checkbox\" name=\"comment_{$LIST_COUNT}\" value=\"{$COMMENT_COUNT}\">";
		echo "</td><td>{$name}&nbsp;</td><td>{$email}&nbsp;</td><td>".date('d/m/Y h:i:sa', $ctime)."</td>";
		echo "<td>{$ip}</td><td><a href=\"ffgb_edit.php?book={$BOOK_NUM}&comment={$COMMENT_IN_BOOK}\">edit</a></td><td>&nbsp;</td></tr>\n";
		echo "<tr><td colspan=\"7\"><blockquote><b>".stripslashes($subject)."</b><br>\n";
		echo stripslashes($message)."</blockquote></td></tr>\n";
		$LIST_COUNT++;
	}
	else if($METHOD_FETCH_ENTRIES=="search")
	{
		if(preg_match('/^'.preg_replace('/\*/',".*",urldecode($FILTER_MATCH)).'$/i',${$FILTER_BY}))
		{
			if(isset($_POST['comment_'.$LIST_COUNT]) && $_POST['comment_'.$LIST_COUNT]==$COMMENT_COUNT)
			{
				if($_POST['comment_action']=="activate") { $is_shown = 1; }
				else if($_POST['comment_action']=="deactivate") { $is_shown = 0; }
				else if($_POST['comment_action']=="remove") { $is_removed = 1; }
				else if($_POST['comment_action']=="unremove") { $is_removed = 0; }
			}

			echo "<tr class=\"".($is_removed?"comment_removed":($is_shown?"":"comment_inactive"))."\">";
			echo "<td><input type=\"checkbox\" name=\"comment_{$LIST_COUNT}\" value=\"{$COMMENT_COUNT}\">";
			echo "</td><td>{$name}&nbsp;</td><td>{$email}&nbsp;</td><td>".date('d/m/Y h:i:sa', $ctime)."</td>";
			echo "<td>{$ip}</td><td><a href=\"ffgb_edit.php?book={$BOOK_NUM}&comment={$COMMENT_IN_BOOK}\">edit</a></td><td>&nbsp;</td></tr>\n";
			echo "<tr><td colspan=\"7\"><blockquote><b>".stripslashes($subject)."</b><br>\n";
			echo stripslashes($message)."</blockquote></td></tr>\n";
			$LIST_COUNT++;
		}
	}
	
	if($EDIT_BULK)
	{
		@fwrite($FP_OUT, "ffgb_add_comment({$is_shown}, {$is_removed}, \"{$ip}\", \"{$ctime}\", \"{$name}\", \"{$email}\", \"{$url}\", \"{$subject}\", \"{$message}\");\n");
	}

	$COMMENT_COUNT++;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>

<title>Administration :: Flat File Guestbook from AdvanceByDesign</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="ffgb_styles.css">

</head>
<body>
<?php
if(!IS_LOGGED_IN)
{
	/* Show the login form */
	$htmerr = (strlen($err)?"<ul>{$err}</ul>":"");
	
echo <<<ENDHTML
	<center>
	<form method="post" action="{$_SERVER['PHP_SELF']}">
	<table border="0" cellpadding="4px" cellspacing="0" class="login_form">
	<tr><td colspan="2"><b>Administration login</b>
		<p>Please enter the administration area login information to
		manage the guestbook.{$htmerr}</p></td></tr>
	<tr><td>Username</td>
		<td align="right"><input type="text" value="" name="ffgb_user"></td></tr>
	<tr><td>Password</td>
		<td align="right"><input type="password" value="" name="ffgb_pass"></td></tr>
	<tr><td>&nbsp;</td>
		<td align="right"><input type="submit" value="Log in" class="submitbutton"></td></tr>
	</table>
	</form>
	<a href="guestbook.php">Return to the Guestbook</a><br>&nbsp;<br>
	<span class="about_text">
		<a href="http://www.advancebydesign.com" target="_blank">PHP Flat File Guestbook
		Script</a> from AdvanceByDesign.
	</span>
	</center>
ENDHTML;
}
else
{
	/* Administrative content */
	echo "<div class=\"logout_link\">";
	echo "<a href=\"{$_SERVER['PHP_SELF']}?cmd=reorder\">Permanently delete removed Comments</a>";
	echo "<a href=\"{$_SERVER['PHP_SELF']}?cmd=logout\">Log out of Administration area</a>";
	echo "</div>\n";
	
	require('ffgb_count.php');
	
	/* Permanently delete all removed comments */
	if(isset($_GET['cmd']) && $_GET['cmd']=="reorder")
	{
		if($ffgb_count==1)
		{
			$fp = @fopen('ffgb_comments/ffgb_1.php', "r");
			$fp_out = @fopen('ffgb_comments/ffgb_1_tmp.php', "w");
			
			while(($tmp = @fgets($fp, 8192))!==false)
			{
				if(substr($tmp, 0, 16)=="ffgb_add_comment")
				{
					$tmp_a = explode(",", $tmp);
					if($tmp_a[1]==1) { continue; }
				}
				@fwrite($fp_out, $tmp);
			}
			
			fclose($fp);
			fclose($fp_out);
			
			rename('ffgb_comments/ffgb_1_tmp.php', 'ffgb_comments/ffgb_1.php');
		}
		else
		{
			$total_comments = 0;
			$read_comment = 0;
			$fp_out = @fopen('ffgb_comments/ffgb_long.php', "w");
			
			for($i=1;$i<=$ffgb_count;$i++)
			{
				$fp = @fopen('ffgb_comments/ffgb_'.$i.'.php', "r");
				$f_lines = array();
				
				while(($tmp = @fgets($fp, 8192))!==false)
				{
					if(trim($tmp)=="<?php" || trim($tmp)=="?>" || !strlen(trim($tmp))) { continue; }
					if(substr($tmp, 0, 16)=="ffgb_add_comment")
					{
						$tmp_a = explode(",", $tmp);
						if($tmp_a[1]==1) { continue; }
						else { $total_comments++; }
					}
					array_push($f_lines, $tmp);
				}
				
				$f_lines = array_reverse($f_lines);
				@fwrite($fp_out, implode("", $f_lines));
				unset($f_lines);
				
				fclose($fp);
			}
			
			fclose($fp_out);
			
			$fp = @fopen('ffgb_comments/ffgb_long.php', "r");
			
			for($i=1;$i<=ceil($total_comments/COMMENTS_PER_BOOK);$i++)
			{
				$fp_out = @fopen('ffgb_comments/ffgb_'.$i.'.php', "w");
				@fwrite($fp_out, "<?php\n");
				$f_lines = array();
				
				for($j=0;$j<COMMENTS_PER_BOOK;$j++)
				{
					array_push($f_lines, @fgets($fp, 8192));
					$read_comment++;
					if($read_comment>=$total_comments) { break; }
				}
				
				$f_lines = array_reverse($f_lines);
				@fwrite($fp_out, implode("", $f_lines));
				unset($f_lines);
				@fwrite($fp_out, "?>");
				fclose($fp_out);
			}
			
			fclose($fp);
			@unlink('ffgb_comments/ffgb_long.php');
			
			if($ffgb_count>ceil($total_comments/COMMENTS_PER_BOOK))
			{
				for($i=(ceil($total_comments/COMMENTS_PER_BOOK)+1);$i<=$ffgb_count;$i++)
				{
					@unlink('ffgb_comments/ffgb_'.$i.'.php');
				}
				
				$fp = @fopen('ffgb_count.php', "w");
				@fwrite($fp, "<?php \$ffgb_count = ".ceil($total_comments/COMMENTS_PER_BOOK)."; ?>");
				fclose($fp);
			}
		}
echo <<<ENDHTML
	<table border="0" cellpadding="2px" cellspacing="0" class="admin_form">
	<tr><td><h2>Comments purged</h2>
		<p>The removed comments have been purged.</p></td></tr>
	</table>
ENDHTML;
	}
	
	$htmbooks = "";
	$htmmatch = (isset($_GET['filter_match'])?urldecode($_GET['filter_match']):"");
	for($i=1;$i<=$ffgb_count;$i++)
	{
		$htmbooks.= "<option value=\"{$i}\"";
		$htmbooks.= (isset($_GET['book_id']) && $_GET['book_id']==$i?" selected=\"selected\"":"");
		$htmbooks.= ">{$i}</option>\n";
	}

	$g = "";
	/* fetch comments by book */
	if(isset($_GET['book_id']) && is_numeric($_GET['book_id']) &&
		file_exists('ffgb_comments/ffgb_'.$_GET['book_id'].'.php'))
	{
		$g = "?book_id={$_GET['book_id']}";
	}
	/* fetch comments by search */
	else if(isset($_GET['filter_by']))
	{
		$g = "?filter_by={$_GET['filter_by']}&filter_match={$_GET['filter_match']}";
	}
	
echo <<<ENDHTML
	<form method="get" action="{$_SERVER['PHP_SELF']}">
	<table border="0" cellpadding="2px" cellspacing="0" class="admin_form">
	<tr><td colspan="2"><b>Manage comments</b>
		<p>Filter or search for specific posts below, or view post by book (page).</p>
		</td></tr>
	<tr><td>View all posts in book </td>
		<td align="right"><select name="book_id">
			{$htmbooks}</select></td></tr>
	<tr><td colspan="2" align="right">
		<input type="submit" value="View Book" class="submitbutton">
		</td></tr>
	</table>
	</form>
	
	<form method="get" action="{$_SERVER['PHP_SELF']}">	
	<table border="0" cellpadding="2px" cellspacing="0" class="admin_form">
	<tr><td>Search for posts where </td>
		<td><select name="filter_by">
			<option value="name">Name</option>
			<option value="ip">IP address</option>
			<option value="email">Email</option>
			<option value="subject">Subject</option></select></td>
		<td> is </td>
		<td align="right"><input type="text" value="{$htmmatch}" name="filter_match"></td></tr>
	<tr><td colspan="3">
			<small>(The asterisk (*) wildcard can be used)</small>
		</td>
		<td align="right"><input type="submit" value="Search" class="submitbutton"></td></tr>
	</table>
	</form>
	
	<form method="post" action="{$_SERVER['PHP_SELF']}{$g}">
	<table border="0" cellpadding="2px" cellspacing="0" class="admin_list">
	<tr><th>&nbsp;</th>
		<th>Name</th>
		<th>Email</th>
		<th>Date/Time</th>
		<th>IP</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th></tr>
ENDHTML;

	/* fetch comments by book */
	if(isset($_GET['book_id']) && is_numeric($_GET['book_id']) &&
		file_exists('ffgb_comments/ffgb_'.$_GET['book_id'].'.php'))
	{
		$BOOK_NUM = $_GET['book_id'];
		
		if(isset($_POST['comment_action']))
		{
			$EDIT_BULK = true;
			$FP_OUT = @fopen('ffgb_comments/ffgb_'.$_GET['book_id'].'_tmp.php', "w");
			@fwrite($FP_OUT, "<?php\n");
		}
		
		require('ffgb_comments/ffgb_'.$_GET['book_id'].'.php');
		
		if(isset($_POST['comment_action']))
		{
			@fwrite($FP_OUT, "?>");
			fclose($FP_OUT);
			unlink('ffgb_comments/ffgb_'.$_GET['book_id'].'.php');
			rename('ffgb_comments/ffgb_'.$_GET['book_id'].'_tmp.php','ffgb_comments/ffgb_'.$_GET['book_id'].'.php');
		}
	}
	/* fetch comments by search */
	else if(isset($_GET['filter_by']))
	{
		$FILTER_BY = $_GET['filter_by'];
		$FILTER_MATCH = $_GET['filter_match'];
		$METHOD_FETCH_ENTRIES = "search";
		for($i=1;$i<=$ffgb_count;$i++)
		{
			$BOOK_NUM = $i;
			
			if(isset($_POST['comment_action']))
			{
				$EDIT_BULK = true;
				$FP_OUT = @fopen('ffgb_comments/ffgb_'.$i.'_tmp.php', "w");
				@fwrite($FP_OUT, "<?php\n");
			}
			
			require('ffgb_comments/ffgb_'.$i.'.php');
			
			if(isset($_POST['comment_action']))
			{
				@fwrite($FP_OUT, "?>");
				fclose($FP_OUT);
				unlink('ffgb_comments/ffgb_'.$i.'.php');
				rename('ffgb_comments/ffgb_'.$i.'_tmp.php','ffgb_comments/ffgb_'.$i.'.php');
			}
		}
	}

echo <<<ENDHTML
	</table>
	<input type="hidden" name="list_total" value="{$LIST_COUNT}">
	
	<table border="0" cellpadding="2px" cellspacing="0" class="admin_form">
	<tr><td>Selected comments </td>
		<td><select name="comment_action">
			<option value="activate">Activate / Accept</option>
			<option value="deactivate">Deactivate</option>
			<option value="remove">Remove</option>
			<option value="unremove">Un-remove</option>
			</select></td>
		<td align="right"><input type="submit" value="Update" class="submitbutton"></td></tr>
	</table>
	</form>
ENDHTML;
}

?>
</body>
</html>