<?php
/*
 * Twitter rest API glue
 * 
 * Don't include this file or directly call it's methods.
 * See bsocial()->twitter_user_info() instead.
 *
 */

/*
 * bSocial_Twitter_User_Info class
 * 
 * Get the public information for a given user
 * Example: bsocial_twitter_user_info()->get( 'misterbisson' ) 
 * 
 * @author Casey Bisson
 */

class bSocial_Twitter_User_Info
{
	function get( $screen_name , $by = 'screen_name' )
	{
		// Look up info about the twitter user by their screen name or ID
		// Note: the ID here is not compatible with the user ID returned from the search API. This is a Twitter limitation.
		// method docs: http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-users%C2%A0show
		// useful: $user->name, $user->screen_name, $user->id_str, $user->followers_count 
	
		// are we searching by screen name or ID?
		$by = in_array( $by , array( 'screen_name' , 'id' )) ? $by : 'screen_name';
	
		// check the cache for the user info
		if ( ! $user = wp_cache_get( (string) $screen_name , 'twitter_'. $by ))
		{
			// check Twitter for the user info
			$temp_results = wp_remote_get( 'http://api.twitter.com/1/users/show.json?'. $by .'='. urlencode( $screen_name ) );
			if ( is_wp_error( $temp_results ))
				return FALSE;
	
			$user = json_decode( wp_remote_retrieve_body( $temp_results ));
	
			if( empty( $user->error ))
				wp_cache_set( (string) $screen_name , $user, 'twitter_screen_name' , 604801 ); // cache for 7 days
		}
	
		return $user;
	}	
}
