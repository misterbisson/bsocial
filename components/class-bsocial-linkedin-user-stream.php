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
 * interact with the public LinkedIn stream for a given user
 *
 * Available query args:
 *   http://developer.linkedin.com/documents/get-network-updates-and-statistics-api
 *   http://developer.linkedin.com/documents/share-api
 */
class bSocial_LinkedIn_User_Stream extends bSocial_LinkedIn
{
	public $base_url = 'http://api.linkedin.com/v1/people/';

	/**
	 * @param $user_id a user token/id or url
	 * @param $by 'token' or 'url' 
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

		return $this->post_http( $url, array( 'custom_post_type' => 'json', 'post_data' => json_encode( $json ) ) );
	}//END share
}//END class