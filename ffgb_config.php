<?php
/*
Flat File Guestbook PHP script from AdvanceByDesign
Copyright (c) 2011 Robert Rook
Released under the terms of the ABD free software licence.

Configuration file
*/

/* Admin configuration */
define("ADMIN_USER", "username");
define("ADMIN_PASS", "password");
/* End Admin configuration */

define("COMMENTS_REQUIRE_VALIDATION", false);
define("COMMENTS_REQUIRE_CAPTCHA", false);
define("COMMENTS_CAPTCHA_PUBLICKEY", "");
define("COMMENTS_CAPTCHA_PRIVATEKEY", "");
define("COMMENTS_REQUIRE_EMAIL", false);
define("COMMENTS_SHOW_EMAIL", true);
define("COMMENTS_ACCEPT_URL", true);
define("COMMENTS_SHOW_URL", true);
define("COMMENT_MAX_LENGTH", 2048);
define("SUBJECT_MAX_LENGTH", 30);

define("ACCEPT_NEW_COMMENTS", true);
define("COMMENTS_PER_BOOK", 20);

define("EMAIL_NEW_COMMENTS", false);
define("EMAIL_TO", "your.email@your-domain.com");

/* This code is to support smilies, etc */
$ffgb_replace_from = array();
$ffgb_replace_to = array();
function add_replacement($from, $to) {
	global $ffgb_replace_from,$ffgb_replace_to;
	array_push($ffgb_replace_from, $from);
	array_push($ffgb_replace_to, $to);
	return;
}
?>