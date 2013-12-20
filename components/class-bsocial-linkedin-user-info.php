<?php
/*
 * LinkedIn rest API glue
 * 
 * Don't include this file or directly call it's methods.
 * See bsocial()->linkedin_user_info() instead.
 *
 */

if ( ! class_exists( 'bSocial_LinkedIn' ) )
{
	require __DIR__ .'/class-bsocial-linkedin.php';
}

/*
 * bSocial_LinkedIn_User_Info class
 * 
 * Get the public information for a given user
 *
 * Example: bsocial_linkedin_user_info()->get( 'user_token', 'token' ) 
 */
class bSocial_LinkedIn_User_Info extends bSocial_LinkedIn
{
	public $base_url = 'http://api.linkedin.com/v1/people/';

	/**
	 * @param $user_id user's profile url or id #
	 * @param $by 'url' or 'token'. linkedin uses member id and member token
	 *  interchangeably, and this is not to be confused with the numeric
	 *  ID found on a member's profile page. Also note that request by
	 *  token does not work on out-of-network users.
	 * @fields (string) fields to request for
	 */
	function get( $user_id, $by, $fields=NULL )
	{
		switch( $by )
		{
			case 'url':
				$url = $this->base_url . 'url=' . urlencode( $user_id );
				break;

			case 'token':
				$url = $this->base_url . 'id=' . $user_id;
				break;

			default:
				return FALSE;
		}//END switch

		if ( $fields )
		{
			$url .= ':' . $fields;
		}

		// check the cache for the user info
		if ( ! $user = wp_cache_get( $user_id, 'linkedin_' . $by ) )
		{
			$user = $this->get_http( $url );

			if ( is_wp_error( $user ) )
			{
				return FALSE;
			}

			if ( empty( $user->errors ) )
			{
				wp_cache_set( $url, $user, 'linkedin_' . $by, 604801 ); // cache for 7 days
			}
		}//END if

		return $user;
	}//END get_by_url

	/**
	 * @fields (string) fields to request for
	 */
	function get_own_profile( $fields=NULL )
	{
		$url = 'http://api.linkedin.com/v1/people/~';

		if ( $fields )
		{
			$url .= ':' . $fields;
		}

		return $this->get_http( $url );
	}//END get

}//END class