<?php
/*
Plugin Name: bSocial
Plugin URI: http://maisonbisson.com/bsuite/
Description: .
Version: 5.0
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/

// the admin menu
//if ( is_admin() )
//	require_once dirname( __FILE__ ) . '/admin.php';

// Better describe your content to social sites
require_once( dirname( __FILE__ ) .'/components/open-graph.php' );

// Feature your comments
require_once( dirname( __FILE__ ) .'/components/featured-comments.php' );

// Twitter components
require_once( dirname( __FILE__ ) .'/components/common-functions.php' );
require_once( dirname( __FILE__ ) .'/components/twitter-api.php' );
require_once( dirname( __FILE__ ) .'/components/twitter-comments.php' );

// Facebook components
require_once( dirname( __FILE__ ) .'/components/fb-api.php' );
require_once( dirname( __FILE__ ) .'/components/fb-widgets.php' );
require_once( dirname( __FILE__ ) .'/components/fb-comments.php' );

// Social anayltics
//require_once( dirname( __FILE__ ) .'/components/social-analytics.php' );


// override the URL path by setting it in the object as such:
// $postloops->path_web = 
