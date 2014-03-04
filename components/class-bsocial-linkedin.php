<?php
/**
 * wrapper for our connection to linkedin's services
 */
class bSocial_LinkedIn
{
	public $oauth = NULL;
	public $base_url = 'http://api.linkedin.com/v1/people/';
	public $errors = array();

	/**
	 * get an oauth instance
	 */
	public function oauth()
	{
		if ( $this->oauth )
		{
			return $this->oauth;
		}

		// We don't care to pass any credentials because we're going to require Keyring for Linkedin to work
		$this->oauth = bsocial()->new_oauth(
			NULL,
			NULL,
			NULL,
			NULL,
			'linkedin'
		);

		return $this->oauth;
	}//END oauth

	/**
	 * @param $id user's profile url or id #
	 * @param $by 'url', 'token' or 'self'. linkedIn uses "member id" and
	 *  "member token" interchangeably, and it should not to be confused
	 *  with the numeric ID found on a member's profile page. Also note that
	 *  request by token does not work on out-of-network users.
	 * @param $fields (string) fields to request for
	 * @param $user_id WP user_id of the user you want to act a
	 */
	public function get_user_info( $id, $by, $fields = NULL, $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		switch( $by )
		{
			case 'url':
				$url = $this->base_url . 'url=' . urlencode( $id );
				break;

			case 'token':
				$url = $this->base_url . 'id=' . $id;
				break;

			case 'self':
				$url = $this->base_url . '~';
				break;

			default:
				return FALSE;
		}//END switch

		if ( $fields )
		{
			$url .= ':' . $fields;
		}

		// check the cache for the user info
		if ( ! $user = wp_cache_get( $id, 'linkedin_' . $by ) )
		{
			$user = $this->oauth()->get_http( $url );

			if ( is_wp_error( $user ) )
			{
				$this->errors[] = $user;
				return FALSE;
			}

			if ( empty( $user->errors ) )
			{
				wp_cache_set( $url, $user, 'linkedin_' . $by, 604801 ); // cache for 7 days
			}
		}//END if

		return $user;
	}//END get_user_info

	/**
	 * @param $id a user token/id or url
	 * @param $by 'token', 'url' or 'self'
	 * @param $count how many updates to fetch
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function get_updates( $id, $by = 'token', $count = 25, $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		switch ( $by )
		{
			case 'token':
				$url = $this->base_url . 'id=' . $id;
				break;

			case 'url':
				$url = $this->base_url . 'url=' . urlencode( $id );
				break;

			case 'self':
				$url = $this->base_url . '~';
				break;

			default:
				return FALSE;
		}//END switch

		$url .= '/network/updates';

		$params = array(
			'count' => $count,
			'scope' => 'self',
		);

		return $this->oauth()->get_http( $url, $params );
	}//END get_updates

	/**
	 * share something to a user's linkedin feed. this can take a minute
	 * to show up after a successful call.
	 *
	 * @param $parameters array of possible parameters from linkedin's
	 *  share api:
	 *  'comment': the status update text
	 *  'title': title of the shared content (image, url, etc.)
	 *  'description': description of the shared content
	 *  'submitted-url': url of shared content
	 *  'submitted-image-url': an image to go with the shared content url
	 *  'visibility': who can see this comment ('connections-only' or 'anyone'
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function share( $parameters, $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		$url = $this->base_url . '~/shares';

		// convert contents of $parameters into an object that can be
		// converted to json later
		$json = new stdClass;
		if ( isset( $parameters['comment'] ) )
		{
			$json->comment = $parameters['comment'];
		}

		$json->content = new stdClass;
		if ( isset( $parameters['title'] ) )
		{
			$json->content->title = $parameters['title'];
		}
		if ( isset( $parameters['description'] ) )
		{
			$json->content->description = $parameters['description'];
		}
		if ( isset( $parameters['submitted-url'] ) )
		{
			$json->content->{'submitted-url'} = $parameters['submitted-url'];
		}
		if ( isset( $parameters['submitted-image-url'] ) )
		{
			$json->content->{'submitted-image-url'} = $parameters['submitted-image-url'];
		}

		if ( isset( $parameters['visibility'] ) )
		{
			$json->visibility = new stdClass;
			$json->visibility->code = $parameters['visibility'];
		}

		return $this->oauth()->post_http( $url, array(
			'body' => json_encode( $json ),
			'headers' => array(
				'Content-Type' => 'application/json',
				'x-li-format' => 'json',
			),
			'sign_parameters' => FALSE,
		) );
	}//END share
}//END class