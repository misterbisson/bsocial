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
 *
 * Example: bsocial_twitter_user_info()->get( $connection, 'misterbisson' ) 
 * 
 * @author Casey Bisson
 */

class bSocial_Twitter_User_Info
{
	/**
	 * @param $connection TwitterOAuth object
	 * @param $screen_name user screen name or id
	 * @param $by 'screen_name' or 'id'
	 */
	function get( $connection, $screen_name, $by = 'screen_name' )
	{
		// Look up info about the twitter user by their screen name or ID
		// Note: the ID here is not compatible with the user ID returned from the search API. This is a Twitter limitation.
		// method docs: https://dev.twitter.com/docs/api/1.1/get/users/show
		// useful: $user->name, $user->screen_name, $user->id_str, $user->followers_count 

		// are we searching by screen name or ID?
		$by = in_array( $by, array( 'screen_name', 'id' )) ? $by : 'screen_name';

		// check the cache for the user info
		if ( ! $user = wp_cache_get( (string) $screen_name, 'twitter_' . $by ) )
		{
			// check Twitter for the user info
			$user = $connection->get( 'users/show', array( $by => $screen_name ) );

			if ( is_wp_error( $user ) )
			{
				return FALSE;
			}
	
			if ( empty( $user->errors ) )
			{
				wp_cache_set( (string) $screen_name, $user, 'twitter_screen_name', 604801 ); // cache for 7 days
			}
		}//END if
	
		return $user;
	}//END get
}//END class