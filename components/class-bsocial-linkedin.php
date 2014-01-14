<?php
/**
 * wrapper for our connection to linkedin's services
 */
class bSocial_LinkedIn
{
	public $oauth = NULL;
	public $config = NULL;
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

		// check if we have the user token and secret or not
		if ( ! empty( bsocial()->options()->linkedin->user_token ) && ! empty( bsocial()->options()->linkedin->user_secret ) )
		{
			$this->oauth = bsocial()->new_oauth(
				bsocial()->options()->linkedin->consumer_key,
				bsocial()->options()->linkedin->consumer_secret,
				bsocial()->options()->linkedin->user_token,
				bsocial()->options()->linkedin->user_secret
			);
		}
		else
		{
			$this->oauth = bsocial()->new_oauth(
				bsocial()->options()->linkedin->consumer_key,
				bsocial()->options()->linkedin->consumer_secret
			);
		}

		return $this->oauth;
	}//END oauth

	/**
	 * @param $user_id user's profile url or id #
	 * @param $by 'url', 'token' or 'self'. linkedIn uses "member id" and
	 *  "member token" interchangeably, and it should not to be confused
	 *  with the numeric ID found on a member's profile page. Also note that
	 *  request by token does not work on out-of-network users.
	 * @fields (string) fields to request for
	 */
	public function get_user_info( $user_id, $by, $fields = NULL )
	{
		switch( $by )
		{
			case 'url':
				$url = $this->base_url . 'url=' . urlencode( $user_id );
				break;

			case 'token':
				$url = $this->base_url . 'id=' . $user_id;
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
		if ( ! $user = wp_cache_get( $user_id, 'linkedin_' . $by ) )
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
	 * @param $user_id a user token/id or url
	 * @param $by 'token', 'url' or 'self'
	 * @param $count how many updates to fetch
	 */
	public function get_updates( $user_id, $by = 'token', $count = 25 )
	{
		switch ( $by )
		{
			case 'token':
				$url = $this->base_url . 'id=' . $user_id;
				break;

			case 'url':
				$url = $this->base_url . 'url=' . urlencode( $user_id );
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
	 */
	public function share( $parameters )
	{
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

		return $this->oauth()->post_http( $url, array( 'custom_post_type' => 'json', 'post_data' => json_encode( $json ) ) );
	}//END share
}//END class