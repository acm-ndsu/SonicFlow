#!/usr/local/bin/php -q
<?php
//ini_set('memory_limit', '-1');
dl('tk.so');

$root = new Tk();
$root->wmTitle('"Hello World"');

$label = new Label($root, '-text "Hello, world!"');
$label->pack('-side left', '-padx 15', '-pady 15');

Tk_MainLoop();
?>