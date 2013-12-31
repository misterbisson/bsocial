<?php
/*
 * Don't include this file or directly call it's methods.
 * Use bsocial()->new_linkedin_user_stream() instead.
 */

if ( ! class_exists( 'bSocial_LinkedIn' ) )
{
	require __DIR__ .'/class-bsocial-linkedin.php';
}

/**
 *
 * bSocial_LinkedIn_User_Stream class
 *
 * Get the public LinkedIn stream for a given user
 *
 * Available query args:
 *   http://developer.linkedin.com/documents/get-network-updates-and-statistics-api
 */
class bSocial_LinkedIn_User_Stream extends bSocial_LinkedIn
{
	/**
	 * @param $user_id a user token/id or url
	 * @param $by 'token' or 'url' 
	 * @param $count how many updates to fetch
	 */
	public function get_updates( $user_id, $by = 'token', $count = 25 )
	{
		$url = 'http://api.linkedin.com/v1/people/';

		switch ( $by )
		{
			case 'token':
				$url .= 'id=' . $user_id;
				break;

			case 'url':
				$url .= 'url=' . urlencode( $user_id );
				break;

			default:
				return FALSE;
		}//END switch

		$url .= '/network/updates';

		$params = array(
			'count' => $count,
			'scope' => 'self',
		);

		return $this->get_http( $url, $params );
	}//END get_updates
}//END class