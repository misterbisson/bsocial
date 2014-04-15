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

	// prepend the LinkedIn api url if $query_url is not absolute
	public function validate_query_url( $query_url, $parameters )
	{
		if (
			0 !== strpos( $query_url, 'http://' ) &&
			0 !== strpos( $query_url, 'https://' )
		)
		{
			$query_url = 'http://api.linkedin.com/v1/' . $query_url;
		}//END if

		return $query_url;
	}//END validate_query_url

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
				$url = 'people/url=' . urlencode( $id );
				break;

			case 'token':
				$url = 'people/id=' . $id;
				break;

			case 'self':
				$url = 'people/~';
				break;

			default:
				return FALSE;
		}//END switch

		if ( $fields )
		{
			$url .= ':' . $fields;
		}

		// check the cache for the user info
		$url = $this->validate_query_url( $url );

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
			case 'url':
				$url = 'people/url=' . urlencode( $id );
				break;

			case 'token':
				$url = 'people/id=' . $id;
				break;

			case 'self':
				$url = 'people/~';
				break;

			default:
				return FALSE;
		}//END switch

		$url .= '/network/updates';

		$params = array(
			'count' => $count,
			'scope' => 'self',
		);

		return $this->oauth()->get_http( $this->validate_query_url( $url ), $params );
	}//END get_updates

	/**
	 * Share something to a user's linkedin feed. this can take a minute to show up after a successful call.
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

		$url = 'people/~/shares';

		// Convert contents of $parameters into an object that can be converted to JSON later
		$json = new stdClass;

		if ( isset( $parameters['comment'] ) )
		{
			$json->comment = $parameters['comment'];
		} // END if

		$json->content = new stdClass;

		$content_fields = array(
			'title',
			'description',
			'submitted-url',
			'submitted-image-url',
		);

		foreach ( $content_fields as $field )
		{
			if ( isset( $parameters[ $field ] ) )
			{
				$json->content->$field = $parameters[ $field ];
			} // END if
		} // END foreach

		if ( isset( $parameters['visibility'] ) )
		{
			$json->visibility = new stdClass;
			$json->visibility->code = $parameters['visibility'];
		} // END if

		return $this->oauth()->post_http(
			$this->validate_query_url( $url ),
			array(
				'body' => json_encode( $json ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'x-li-format' => 'json',
				),
				'sign_parameters' => FALSE,
			)
		);
	} // END share

	/**
	 * Share something to a company's linkedin feed. this can take a minute to show up after a successful call.
	 *
	 * @param $parameters array of possible parameters from linkedin's
	 *  share api:
	 *  'comment': the status update text
	 *  'title': title of the shared content (image, url, etc.)
	 *  'description': description of the shared content
	 *  'submitted-url': url of shared content
	 *  'submitted-image-url': an image to go with the shared content url
	 *  'visibility': who can see this comment ('connections-only' or 'anyone'
	 * @param $company_id LinkedIn company id or universal-name of the company you want to share as
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function company_share( $parameters, $company_id, $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id, $user_id );
		} // END if

		// If company_id isn't numeric then it's a universal-name
		if ( ! is_numeric( $company_id ) )
		{
			if ( ! $company_id = $this->get_company_id( $company_id ) )
			{
				return FALSE;
			} // END if
		} // END if

		$url = 'companies/' . absint( $company_id ) . '/shares';

		// Convert contents of $parameters into an object that can be converted to JSON later
		$json = new stdClass;

		if ( isset( $parameters['comment'] ) )
		{
			$json->comment = $parameters['comment'];
		} // END if

		$json->content = new stdClass;

		$content_fields = array(
			'title',
			'description',
			'submitted-url',
			'submitted-image-url',
		);

		foreach ( $content_fields as $field )
		{
			if ( isset( $parameters[ $field ] ) )
			{
				$json->content->$field = $parameters[ $field ];
			} // END if
		} // END foreach

		if ( isset( $parameters['visibility'] ) )
		{
			$json->visibility = new stdClass;
			$json->visibility->code = $parameters['visibility'];
		} // END if

		return $this->oauth()->post_http(
			$this->validate_query_url( $url ),
			array(
				'body' => json_encode( $json ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'x-li-format' => 'json',
				),
				'sign_parameters' => FALSE,
			)
		);
	} // END company_share

	/**
	 * Get the ID of a company using it's universal name
	 *
	 * @param $universal_name LinkedIn universal-name of a company you want the ID of (i.e. practical-pickles)
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function get_company_id( $universal_name, $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		$url = 'companies/universal-name=' . $universal_name;

		$response = (array) $this->oauth()->get_http( $this->validate_query_url( $url ) );

		if ( Keyring_Util::is_error( $response ) || ! isset( $response['id'] ) )
		{
			return FALSE;
		} // END if

		return $response['id'];
	} // END get_company_id
}//END class