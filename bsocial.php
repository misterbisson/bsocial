<?php
/*
Plugin Name: bSocial Connected Blogging Tools
Plugin URI: http://maisonbisson.com/bsuite/
Description: Social widgets and connectivity.
Version: 5.2
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/


require_once __DIR__ . '/components/class-bsocial.php';
bsocial();

// Social anayltics
//require_once( dirname( __FILE__ ) .'/components/social-analytics.php' );


// override the URL path by setting it in the object as such:
// $postloops->path_web = 
