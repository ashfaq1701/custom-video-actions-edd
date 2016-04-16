<?php

/*
 Plugin Name: Custom Video Actions
 Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 Description: Plugin to store a frame as thumbnail from video
 Version: 1.0
 Author: ashfaq
 Author URI: http://URI_Of_The_Plugin_Author
 License: A "Slug" license name e.g. GPL2
 */

include 'libs/store-thumbnail-from-video.php';
include 'libs/convert-video.php';

add_action('plugins_loaded', array( 'StoreThumbnailFromVideo', 'getInstance' ));
add_action('plugins_loaded', array('ConvertVideo', 'getInstance'));