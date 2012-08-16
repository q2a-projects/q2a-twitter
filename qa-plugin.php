<?php

/*
	Plugin Name: Twitter Recent posts Widget
	Plugin URI: https://github.com/Towhidn/q2a-twitter
	Plugin Description: lists your recent tweets
	Plugin Version: 1.0.0
	Plugin Date: 2012-8-16
	Plugin Author: QA-Themes
	Plugin Author URI: http://www.qa-themes.com/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.3
	Plugin Update Check URI: 
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}


	qa_register_plugin_module('widget', 'qa-twitter.php', 'qa_twitter', 'Twitter Widget');
	

/*
	Omit PHP closing tag to help avoid accidental output
*/