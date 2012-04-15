<?php
/*
Plugin Name: bSocial Connected Blogging Tools
Plugin URI: http://maisonbisson.com/bsuite/
Description: Social widgets and connectivity.
Version: 5.0
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/

// get options
$bsoptions = get_option('bsocial-options');

// insert default options if the options array is empty
if( empty( $bsoptions ))
{
	$bsoptions = array( 
		'open-graph' => 1,
		'featured-comments' => 1,
		'twitter-api' => 1,
		'twitter-comments' => 1,
		'twitter-app_id' => '',
		'facebook-api' => 1,
		'facebook-add_button' => 1,
		'facebook-comments' => 0,
		'facebook-admins' => '',
		'facebook-app_id' => '',
		'facebook-secret' => '',
	);

	update_option( 'bsocial-options' , $bsoptions );
}

// the admin menu
if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';

// Better describe your content to social sites
if( $bsoptions['open-graph'] )
	require_once( dirname( __FILE__ ) .'/components/open-graph.php' );

// Feature your comments
if( $bsoptions['featured-comments'] )
	require_once( dirname( __FILE__ ) .'/components/featured-comments.php' );

// Components shared by both Twitter API and Facebook Comments
if( $bsoptions['twitter-api'] || $bsoptions['facebook-comments'] )
	require_once( dirname( __FILE__ ) .'/components/common-functions.php' );

// Twitter components
if( $bsoptions['twitter-api'] )
{
	require_once( dirname( __FILE__ ) .'/components/twitter-api.php' );
	$twitter_api = new bSocial_TwitterApi;
	$twitter_api->app_id = $bsoptions['twitter-app_id'];

	if( $bsoptions['twitter-comments'] )
		require_once( dirname( __FILE__ ) .'/components/twitter-comments.php' );
}	

// Facebook components
if( $bsoptions['facebook-api'] && $bsoptions['facebook-app_id'] )
{
	require_once( dirname( __FILE__ ) .'/components/facebook-api.php' );
	$facebook_api = new bSocial_FacebookApi;
	$facebook_api->options->add_like_button = $bsoptions['facebook-add_button'];
	$facebook_api->admins = $bsoptions['facebook-admins'];
	$facebook_api->app_id = $bsoptions['facebook-app_id'];

	require_once( dirname( __FILE__ ) .'/components/facebook-widgets.php' );

	if( $bsoptions['facebook-comments'] && $bsoptions['facebook-secret'])
	{
		require_once( dirname( __FILE__ ) .'/components/facebook-comments.php' );
		$facebook_comments = new bSocial_FacebookComments;
		$facebook_comments->app_id = $bsoptions['facebook-app_id'];
		$facebook_comments->app_secret = $bsoptions['facebook-secret'];
	}
}

// Social anayltics
//require_once( dirname( __FILE__ ) .'/components/social-analytics.php' );


// override the URL path by setting it in the object as such:
// $postloops->path_web = 
