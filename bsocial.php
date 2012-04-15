<?php
/*
Plugin Name: bSocial
Plugin URI: http://maisonbisson.com/bsuite/
Description: .
Version: 5.0
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/

$bsoptions = array(
	'twitter-api' => 1,
	'twitter-comments' => 1,
	'facebook-api' => 1,
	'facebook-comments' => 1,
);

// the admin menu
//if ( is_admin() )
//	require_once dirname( __FILE__ ) . '/admin.php';

// Better describe your content to social sites
require_once( dirname( __FILE__ ) .'/components/open-graph.php' );

// Feature your comments
require_once( dirname( __FILE__ ) .'/components/featured-comments.php' );

// Components shared by both Twitter API and Facebook Comments
if( $bsoptions['twitter-api'] || $bsoptions['facebook-comments'] )
	require_once( dirname( __FILE__ ) .'/components/common-functions.php' );

// Twitter components
if( $bsoptions['twitter-api'] )
{
	require_once( dirname( __FILE__ ) .'/components/twitter-api.php' );
	new bSocial_TwitterApi;

	if( $bsoptions['twitter-comments'] )
		require_once( dirname( __FILE__ ) .'/components/twitter-comments.php' );
}	

// Facebook components
if( $bsoptions['facebook-api'] )
{
	require_once( dirname( __FILE__ ) .'/components/fb-api.php' );
	new bSocial_FacebookApi;

	require_once( dirname( __FILE__ ) .'/components/fb-widgets.php' );

	if( $bsoptions['facebook-comments'] )
	{
		require_once( dirname( __FILE__ ) .'/components/fb-comments.php' );
		new bSocial_FacebookComments;
	}
}

// Social anayltics
//require_once( dirname( __FILE__ ) .'/components/social-analytics.php' );


// override the URL path by setting it in the object as such:
// $postloops->path_web = 
