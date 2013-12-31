<?php
/*
 * Twitter rest API glue
 * 
 * Don't include this file or directly call it's methods.
 * See bsocial()->twitter_user_info() instead.
 *
 */

if ( ! class_exists( 'bSocial_Twitter' ) )
{
	require __DIR__ .'/class-bsocial-twitter.php';
}

/*
 * bSocial_Twitter_User_Info class
 * 
 * Get the public information for a given user
 *
 * Example: bsocial_twitter_user_info()->get( 'misterbisson' ) 
 * 
 * @author Casey Bisson
 */

class bSocial_Twitter_User_Info extends bSocial_Twitter
{
	/**
	 * @param $screen_name user screen name or id
	 * @param $by 'screen_name' or 'id'
	 */
	function get( $screen_name, $by = 'screen_name' )
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
			$user = $this->get_http( 'users/show', array( $by => $screen_name ) );

			if ( empty( $user->errors ) )
			{
				wp_cache_set( (string) $screen_name, $user, 'twitter_screen_name', 604801 ); // cache for 7 days
			}
		}//END if

		return $user;
	}//END get
}//END class