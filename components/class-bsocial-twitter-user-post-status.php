<?php
/*
 * Twitter rest API glue
 * 
 * Don't include this file or directly call it's methods.
 * See bsocial()->twitter_user_info() instead.
 */

if ( ! class_exists( 'bSocial_Twitter' ) )
{
	require __DIR__ .'/class-bsocial-twitter.php';
}

/*
 * bSocial_Twitter_User_Post_Status class
 * 
 * post a status update to the authenticated user's account
 *
 * Example: bsocial_twitter_user_post_status()->post( ... ) 
 */

class bSocial_Twitter_User_Post_Status extends bSocial_Twitter
{
	/**
	 * @param $status the status to update (tweet)
	 */
	function post( $status )
	{
		return $this->post_http( 'statuses/update', array( 'status' => $status ) );
	}//END get
}//END class