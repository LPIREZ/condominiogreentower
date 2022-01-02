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

$admin_url = "http://{$_SERVER['HTTP_HOST']}".rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/ffgb_admin.php";

/* Validate login information */
if(!isset($_COOKIE['username']) || !isset($_COOKIE['password'])
	|| $_COOKIE['username']!=ADMIN_USER || $_COOKIE['password']!=md5(ADMIN_PASS))
{
	header("Location: {$admin_url}");
	die();
}
else
{
	define("IS_LOGGED_IN", true);
}

$comment_found = false;

/* Comment reading function for editing */
$all_comments = array();
function ffgb_add_comment($active, $removed, $ip, $time, $name, $email, $url, $subject, $message) {
	global $all_comments;
	$all_comments[] = array('active'=>$active, 'removed'=>$removed, 'ip'=>$ip, 'time'=>$time,
		'name'=>$name, 'email'=>$email, 'url'=>$url, 'subject'=>$subject, 'message'=>$message);
	return (count($all_comments)-1);
}

if(isset($_GET['book']) && is_numeric($_GET['book']) && isset($_GET['comment']) && is_numeric($_GET['comment']))
{
	if(!file_exists('ffgb_comments/ffgb_'.$_GET['book'].'.php'))
	{
		header("Location: {$admin_url}");
		die();
	}
	else
	{
		include('ffgb_comments/ffgb_'.$_GET['book'].'.php');
		if(!isset($all_comments[$_GET['comment']]))
		{
			header("Location: {$admin_url}");
			die();
		}
		else
		{
			/* Fetch the specific comment for editing */
			$c = $all_comments[$_GET['comment']];
		}
	}
}

/* If a comment has been updated, save the changes */
if(isset($_POST['ffgb_name']))
{
	$all_comments[$_GET['comment']] = array(
		'active' => $c['active'],
		'removed' => $c['removed'],
		'ip' => $c['ip'],
		'time' => $c['time'],
		'name' => addslashes($_POST['ffgb_name']),
		'email' => addslashes($_POST['ffgb_email']),
		'url' => addslashes($_POST['ffgb_url']),
		'subject' => addslashes($_POST['ffgb_subject']),
		'message' => addslashes($_POST['ffgb_message'])
	);
	
	$fp = fopen('ffgb_comments/ffgb_'.$_GET['book'].'.php', 'w');
	fwrite($fp, '<'."?php\n");
	foreach($all_comments as $v)
	{
		fwrite($fp, "ffgb_add_comment({$v['active']}, {$v['removed']}, '{$v['ip']}', '{$v['time']}', \"{$v['name']}\", \"{$v['email']}\", \"{$v['url']}\", \"{$v['subject']}\", \"{$v['message']}\");\n");
	}
	fwrite($fp, '?'.">");
	fclose($fp);
	
	header("Location: {$admin_url}?book_id={$_GET['book']}");
	die();
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>

<title>Administration :: Flat File Guestbook from AdvanceByDesign</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="ffgb_styles.css">

</head>
<body>

<div class="edit_comment_form">
	<h1>Edit Guestbook Comment</h1>
	<form method="post" action="ffgb_edit.php?book=<?php echo $_GET['book']; ?>&comment=<?php echo $_GET['comment']; ?>">
	<table border="0" cellpadding="2px" cellspacing="0" class="comment_form">
	<tr><td>Name</td>
		<td align="right"><input type="text" name="ffgb_name" value="<?php echo htmlspecialchars($c['name']); ?>"></td></tr>
	<tr><td>Email</td>
		<td align="right"><input type="text" name="ffgb_email" value="<?php echo htmlspecialchars($c['email']); ?>"></td></tr>
	<tr><td>URL</td>
		<td align="right"><input type="text" name="ffgb_url" value="<?php echo htmlspecialchars($c['url']); ?>"></td></tr>
	<tr><td>Subject</td>
		<td align="right"><input type="text" name="ffgb_subject" value="<?php echo htmlspecialchars(stripslashes($c['subject'])); ?>"></td></tr>
	<tr><td colspan="2"><textarea cols="40" rows="5" name="ffgb_message"><?php echo htmlspecialchars(stripslashes($c['message'])); ?></textarea></td></tr>
	<tr><td><a href="<?php echo $admin_url."?book_id=".$_GET['book']; ?>">Cancel editing</a></td>
		<td align="right"><input type="submit" value="Save Comment" class="submitbutton"></td></tr>
	</table>
	</form>
</div>

</body>
</html>