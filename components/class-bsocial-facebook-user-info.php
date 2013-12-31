<?php
/*
 * Facebook user info
 *
 * Don't include this file or directly call its methods.
 * See bsocial()->facebook_user_info() instead.
 */

if ( ! class_exists( 'bSocial_Facebook' ) )
{
	require __DIR__ . '/class-bsocial-facebook.php';
}

/*
 * bSocial_Facebook_User_Info class
 *
 * Get the public information for a given user
 *
 * Example: bsocial_facebook_user_info()->get( 'user_token', 'token' )
 */
class bSocial_Facebook_User_Info extends bSocial_Facebook
{
	/**
	 * @param $user_id id of a user to get
	 */
	public function get_user_profile( $user_id )
	{
		if ( ! $this->facebook )
		{
			return new WP_Error( 'facebook auth error', 'error instantiating a Facebook instance.');
		}

		try
		{
			return $this->facebook->api( '/' . $user_id, 'GET' );
		}
		catch ( FacebookApiException $e )
		{
			return new WP_Error( $e->getType(), $e->getMessage() );
		}
	}//END get_user_profile

	/**
	 * get the fb application's own user profile
	 */
	public function get_own_profile()
	{
		$user_id = $this->get_user_id();

		if ( is_wp_error( $user_id ) )
		{
			return $user_id;
		}

		return $this->get_user_profile( $user_id );
	}//END get_own_profile

	/**
	 * get profile of a facebook 'page'. if the page is public then
	 * we won't need to authenticate the user.
	 */
	public function get_page_profile( $page_name )
	{
		if ( 0 !== strpos( $page_name, '/' ) )
		{
			$page_name = '/' . $page_name;
		}
		return $this->facebook->api( $page_name, 'GET' );
	}//END get_page_profile
}//END class