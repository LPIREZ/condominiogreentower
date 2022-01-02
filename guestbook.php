<?php
/*
Flat File Guestbook PHP script from AdvanceByDesign
Copyright (c) 2011 Robert Rook
Released under the terms of the ABD free software licence.

This is the main Guestbook file.  By default it generates all its
own header HTML, etc.  If you do not wish it to (for example, if
you are including it within a PHP page on your website yourself),
add a definition prior to including this file as follows:

define("FFGB_NO_HEADERS", true);
*/
require('ffgb_config.php');
require('ffgb_replace.php');

$err = "";
$msg = "";

require('ffgb_count.php');
$ffgb_view = $ffgb_count;

if(!defined("FFGB_NO_HEADERS"))
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>

<title>Flat File Guestbook from AdvanceByDesign</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="ffgb_styles.css">


<style>
ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: #4682B4;
}

li {
  float: left;
  color: yellow;
}

li a {
  display: block;
  color: white;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
}

li a:hover:not(.active) {
  background-color: #87CEFA;
}

.active {
  background-color: #191970;
}
</style>


<body>

<ul>
  <li><a class="active" href="#home" style="color: white">Home</a></li>
  <li><a href="cadastro.html" style="color: white">Livro</a></li>
  <li><a href="busca.html" style="color: white">Consutar</a></li>
  <li><a href="alterar.html" style="color: white">Alterar</a></li>
  <li><a href="deletar.html" style="color: white">Deletar</a></li>
</ul>

</head>
<body>
<center>
<?php
}

$comment_count = 0;

function ffgb_add_comment($is_shown, $is_removed, $ip, $ctime, $name, $email, $url, $subject, $message)
{
	global $comment_count, $ffgb_replace_from, $ffgb_replace_to;
	$comment_count++;
	if(!$is_shown || $is_removed) { return false; }
	
	echo "<div class=\"comment_post\">\n";
	echo (strlen($subject)?"<h2>".stripslashes($subject)."</h2>":"");
	echo "<i>Posted on ".date('F jS, Y (h:i:sa)', $ctime)."</i><br>\n";
	echo (COMMENTS_SHOW_URL && strlen($url)?"<a href=\"{$url}\" target=\"_blank\">{$url}</a>":"");
	echo "<p>".str_replace($ffgb_replace_from, $ffgb_replace_to, stripslashes($message));
	echo "</p>\n<div class=\"label_author\">";
	echo (COMMENTS_SHOW_EMAIL && strlen($email)?"<a href=\"mailto:{$email}\">":"");
	echo $name;
	echo (COMMENTS_SHOW_EMAIL && strlen($email)?" ({$email})</a>":"");
	echo "</div>\n";
	echo "</div>\n";
}

if(isset($_GET['guestbook']) && is_numeric($_GET['guestbook']) &&
	$_GET['guestbook']>=1 && $_GET['guestbook']<=$ffgb_count)
{
	$ffgb_view = round($_GET['guestbook']);
}

/* Show a pagelist if more than one book */
if($ffgb_count>1)
{
	echo "<div class=\"pagelist\">\nGuestbook pages: &nbsp;";
	if($ffgb_view>1) { echo "<a href=\"{$_SERVER['PHP_SELF']}?guestbook=".($ffgb_view-1)."\">Back</a> "; }
	for($i=1;$i<=$ffgb_count;$i++)
	{
		if($i==$ffgb_view) { echo "<b>{$i}</b> "; }
		else { echo "<a href=\"{$_SERVER['PHP_SELF']}?guestbook={$i}\">{$i}</a> "; }
	}	
	if($ffgb_view<$ffgb_count) { echo "<a href=\"{$_SERVER['PHP_SELF']}?guestbook=".($ffgb_view+1)."\">Next</a> "; }
	echo "</div>\n";
}

require('ffgb_comments/ffgb_'.$ffgb_view.'.php');

/* If new comments are accepted, show the comments form */
if(ACCEPT_NEW_COMMENTS)
{
	/* Attempt to post a new message */
	if(isset($_POST['ffgb_name']))
	{
		$ffgb_post = array();
		$ffgb_post['name'] = htmlspecialchars(trim(strip_tags($_POST['ffgb_name'])));
		$ffgb_post['email'] = $_POST['ffgb_email'];
		$ffgb_post['url'] = (COMMENTS_ACCEPT_URL && isset($_POST['ffgb_url'])?strip_tags($_POST['ffgb_url']):"");
		$ffgb_post['subject'] = trim(htmlspecialchars($_POST['ffgb_subject']));
		$ffgb_post['message'] = wordwrap(preg_replace('/[(\r\n)\n]{2,}/', "\n", $_POST['ffgb_message']), 20, " ", true);
		$ffgb_post['message'] = trim(htmlspecialchars($ffgb_post['message']));
		$ffgb_post['message'] = str_replace("\n","<br>",$ffgb_post['message']);
		
		if(COMMENTS_REQUIRE_CAPTCHA) {
			require_once('recaptchalib.php');
			$resp = recaptcha_check_answer (COMMENTS_CAPTCHA_PRIVATEKEY, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
			
			if(!$resp->is_valid) {
				$err.= "<li>Please enter the Captcha words correctly (reCAPTCHA error: ".$resp->error;
				$err.= ")</li>\n";
			}
		}
		
		if(!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$/', $ffgb_post['email']) &&
			(strlen($ffgb_post['email']) || COMMENTS_REQUIRE_EMAIL))
		{
			$err.= "<li>Please provide a valid email address";
			$err.= (COMMENTS_REQUIRE_EMAIL?"":" or leave the email field blank");
			$err.= ".</li>\n";
		}
		if(!preg_match('/^(http\:\/\/|ftp\:\/\/)[[:alpha:]]+([-[:digit:][:alpha:]]*[[:digit:][:alpha:]])*(\.[[:alpha:]]+([-[:digit:][:alpha:]]*[[:digit:][:alpha:]])*)*/', $ffgb_post['url']) && strlen($ffgb_post['url']))
		{
			$err.= "<li>Please provide a valid URL or leave the URL field blank.</li>\n";
		}
		if($ffgb_post['name']!=$_POST['ffgb_name'] || strlen($_POST['ffgb_name'])<3 ||
			strlen($_POST['ffgb_name'])>30)
		{
			$err.= "<li>Please provide a name which does not include any special characters and is ";
			$err.= "between 3 and 30 characters in length.</li>\n";
		}
		if(strlen($_POST['ffgb_subject'])>SUBJECT_MAX_LENGTH)
		{
			$err.= "<li>Your comment subject cannot be longer than ".SUBJECT_MAX_LENGTH." characters.</li>\n";
		}
		if(strlen($_POST['ffgb_message'])>COMMENT_MAX_LENGTH || strlen($_POST['ffgb_message'])<4)
		{
			$err.= "<li>Your comment must be between 4 and ".number_format(COMMENT_MAX_LENGTH);
			$err.= " characters in length.</li>\n";
		}
		
		if(!strlen($err))
		{
			$out_book = ($comment_count>=COMMENTS_PER_BOOK?($ffgb_count+1):$ffgb_count);
			$msg = "Your comment has been posted. ";
			$msg.= (COMMENTS_REQUIRE_VALIDATION?"Your comment must be validated by an administrator before
				it will be visible. ":"");
			$msg.= "<i>Thank you!</i>\n";
			
			$fp_post = @fopen('ffgb_comments/ffgb_'.$out_book.'_tmp.php', "w");
			@fwrite($fp_post, "<?php\n");
			@fwrite($fp_post, "ffgb_add_comment(".(COMMENTS_REQUIRE_VALIDATION?0:1).", 0, \"{$_SERVER['REMOTE_ADDR']}\", \"".time()."\", \"{$ffgb_post['name']}\", \"{$ffgb_post['email']}\", \"{$ffgb_post['url']}\", \"".addslashes($ffgb_post['subject'])."\", \"".$ffgb_post['message']."\");\n");
			
			if(file_exists('ffgb_comments/ffgb_'.$out_book.'.php'))
			{
				$fp_read = @fopen('ffgb_comments/ffgb_'.$out_book.'.php', "r");
				while(($tmp = fgets($fp_read))!==false)
				{
					if(trim($tmp)=="<?php") { continue; }
					@fwrite($fp_post, $tmp);
				}
				fclose($fp_read);
			}
			else
			{
				@fwrite($fp_post, "?>");
				
				$fp_tmp = @fopen('ffgb_count.php', "w");
				@fwrite($fp_tmp, "<?php \$ffgb_count = {$out_book}; ?>");
				fclose($fp_tmp);
			}
			
			fclose($fp_post);
			
			@rename('ffgb_comments/ffgb_'.$out_book.'_tmp.php', 'ffgb_comments/ffgb_'.$out_book.'.php');
			
			if(EMAIL_NEW_COMMENTS)
			{
				$replace_from = array("content-type:", "mime-version:", "multipart/mixed",
				"content-transfer-encoding:", "to:", "bcc:", "cc:");
				$replace_to = array("content-type;", "mime-version;", "multipart ./ mixed",
				"content-transfer-encoding;", "to;", "bcc;", "cc;");
				$content = str_ireplace($replace_from,$replace_to,$ffgb_post['message']);
		
				@mail(EMAIL_TO, "Guestbook: New comment from {$ffgb_post['name']}", "Your guestbook at {$_SERVER['SERVER_NAME']} has received a new comment!".(COMMENTS_REQUIRE_VALIDATION?" As new comments require validation, you must login to the Administration Area to approve this comment.":"")." The comment is as follows:\n\nFrom: {$ffgb_post['name']}\nEmail: {$ffgb_post['email']}\nURL: {$ffgb_post['url']}\nSubject: {$ffgb_post['subject']}\n\nMessage:\n{$ffgb_post['message']}", "From: donotreply@{$_SERVER['SERVER_NAME']}");
			}
		}
	}
	
	if(!isset($ffgb_post))
	{
		$ffgb_post = array("name"=>"", "email"=>"", "url"=>"", "subject"=>"", "message"=>"");
	}

	$htmurl = "";
	$htm_ml_sub = SUBJECT_MAX_LENGTH;
	$htm_rf_email = (COMMENTS_REQUIRE_EMAIL?"<span class=\"reqfield\">*</span>":"");
	if(COMMENTS_ACCEPT_URL)
	{
		$htmurl = "<tr><td>Your website</td><td align=\"right\">";
		$htmurl.= "<input type=\"text\" maxlength=\"1024\" value=\"{$ffgb_post['url']}\" name=\"ffgb_url\"></td></tr>\n";
	}
	
	echo "<a name=\"newcomment\"></a>\n";
	
	if(strlen($msg))
	{
echo <<<ENDHTML
	<table border="0" cellpadding="2px" cellspacing="0" class="comment_accepted">
	<tr><td><h2>Comment Posted</h2>
		<p>{$msg}</p>
		<ul><li><a href="{$_SERVER['PHP_SELF']}">Click here to update the Guestbook</a></li></ul>
		</td></tr>
	</table>
ENDHTML;
	}
	else
	{
		if(strlen($err))
		{
echo <<<ENDHTML
	<table border="0" cellpadding="2px" cellspacing="0" class="comment_error">
	<tr><td><h2>Comment Refused</h2>
		<p>The following error(s) have prevented your comment being posted:\n
		<ul>{$err}</ul></p>
		</td></tr>
	</table>
ENDHTML;
		}

echo <<<ENDHTML
	<fieldset><legend>Post a new comment</legend>
	<form method="post" action="{$_SERVER['PHP_SELF']}#newcomment">
	<table border="0" cellpadding="2px" cellspacing="0" class="comment_form">
	<tr><td>Your name <span class="reqfield">*</span></td>
		<td align="right"><input type="text" maxlength="100" value="{$ffgb_post['name']}" name="ffgb_name"></td></tr>
	<tr><td>Email address {$htm_rf_email}</td>
		<td align="right"><input type="text" maxlength="250" value="{$ffgb_post['email']}" name="ffgb_email"></td></tr>
	{$htmurl}
	<tr><td>Subject</td>
		<td align="right"><input type="text" maxlength="{$htm_ml_sub}" value="{$ffgb_post['subject']}" name="ffgb_subject"></td></tr>
	<tr><td colspan="2"><textarea cols="40" rows="5" name="ffgb_message">{$ffgb_post['message']}</textarea></td></tr>
ENDHTML;

if(COMMENTS_REQUIRE_CAPTCHA) {
	echo "<tr><td colspan=\"2\" align=\"right\">";
	require_once('recaptchalib.php');
	$publickey = COMMENTS_CAPTCHA_PUBLICKEY;
	echo recaptcha_get_html($publickey);
	echo "</td></tr>\n";
}

echo <<<ENDHTML
	<tr><td>&nbsp;</td>
		<td align="right"><input type="submit" value="Post Comment" class="submitbutton"></td></tr>
	</table>
	</form>
	</fieldset>
ENDHTML;
	}
}

echo <<<ENDHTML
	<br>
	<span class="about_text">
		<a href="http://www.advancebydesign.com" target="_blank">PHP Flat File Guestbook
		Script</a> from AdvanceByDesign.
		<a href="ffgb_admin.php"><i>Manage this Guestbook</i></a>
	</span>
ENDHTML;

if(!defined("FFGB_NO_HEADERS"))
{
?>
</center>
</body>
</html>
<?php
}
?>