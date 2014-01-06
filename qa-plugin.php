<?php

/*
	Plugin Name: Q2A Twitter Widget
	Plugin URI: https://github.com/Towhidn/q2a-twitter
	Plugin Description: lists your recent tweets
	Plugin Version: 1.1.0
	Plugin Date: 2014-1-6
	Plugin Author: QA-Themes
	Plugin Author URI: http://www.qa-themes.com/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version:
	Plugin Update Check URI: https://raw.github.com/Towhidn/q2a-twitter/master/qa-plugin.php
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}


	qa_register_plugin_module('widget', 'qa-twitter.php', 'qa_twitter', 'Twitter Widget');
	

/*
	Omit PHP closing tag to help avoid accidental output
*/