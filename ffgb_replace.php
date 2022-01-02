<?php
/*
Flat File Guestbook PHP script from AdvanceByDesign
Copyright (c) 2011 Robert Rook
Released under the terms of the ABD free software licence.

Replacement file, for smilies, etc

To add a new smily, add the following line to this file
before the line that reads "?>":

add_replacement(":-)", "<img src=\"imgs/smile.gif\">");

Where ":-)" is the text that will be replaced, and
"<img src=\"imgs/smile.gif\">" is what the text will be
replaced with.  This could simply be two words, for example:

add_replacement("yellow", "blue");
*/

add_replacement(":-)", "<img src=\"ffgb_imgs/smile.gif\">");
add_replacement(";-)", "<img src=\"ffgb_imgs/wink.gif\">");
add_replacement(":-D", "<img src=\"ffgb_imgs/grin.gif\">");
add_replacement("B-)", "<img src=\"ffgb_imgs/cool.gif\">");
add_replacement(":-(", "<img src=\"ffgb_imgs/sad.gif\">");
add_replacement(";-(", "<img src=\"ffgb_imgs/cry.gif\">");
add_replacement(":-Z", "<img src=\"ffgb_imgs/angry.gif\">");

?>