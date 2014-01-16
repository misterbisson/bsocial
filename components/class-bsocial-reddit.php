<?php
/**
 * wrapper for our connection to reddit's services. currently we do not
 * use any service that requires authentication, so this class is actually
 * immune from OAuth.
 */
class bSocial_Reddit
{
	public $base_url = 'http://www.reddit.com';

	// request params
	public $args = NULL;

	// results
	public $http_response = NULL;
	public $api_response = NULL; // raw api results
	public $reddit_links = NULL; // reddit link submission objects
	public $after = NULL;
	public $before = NULL;
	public $errors = array();


	public function links()
	{
		if ( empty( $this->api_response ) )
		{
			return FALSE;
		}

		return $this->api_response;
	}//END links

	/**
	 * get a list of reddit link submissions by link domains.
	 *
	 * @param $args array parameters to fetch link submissions with.
	 *  it may contain:
	 *
	 *  'domain': domain of link submissions to retrieve
	 *  'type': what type of submissions to get. valid choices are:
	 *    'hot' (the default which's the same as ''), 'new', 'rising'
	 *    'controversial', and 'top'
	 *  'limit': how many link submissions to retreieve
	 *  'after': retrieve submissions after this reddit link id
	 */
	public function get_links_by_domain( $args )
	{
		$defaults = array(
			'domain' => NULL,    // required
			'type' => '',        // empty = 'hot'
			'limit' => 10,
			'after' => NULL,
		);
		$this->args = array_filter( wp_parse_args( $args, $defaults ) );

		$query_vars = array();
		$query_vars['limit'] = $this->args['limit'];

		if ( ! empty( $this->args['after'] ) )
		{
			$query_vars['after'] = $this->args['after'];
		}

		switch ( $this->args['type'] )
		{
			case '':
			case 'hot':
			case 'new':
			case 'rising':
			case 'controversial':
			case 'top':
				break;

			default:
				$this->errors[] = new WP_Error( 'invalid type param', $this->args['type'] . ' is not a valid link submission type' );
				return FALSE;
		}//END switch

		$url = sprintf(
			'%1$s/domain/%2$s/%3$s/.json?%4$s',
			$this->base_url,
			$this->args['domain'],
			$this->args['type'],
			build_query( $query_vars )
		);

		$this->http_response = wp_remote_get( $url );

		if ( is_wp_error( $this->http_response ) )
		{
			$this->errors[] = $this->http_response;
			return FALSE;
		}

		if ( isset( $this->http_response['body'] ) )
		{
			$this->api_response = json_decode( $this->http_response['body'] );

			if ( isset( $this->api_response->data->children ) )
			{
				$this->reddit_links = $this->api_response->data->children;
			}

			if ( isset( $this->api_response->data->after ) )
			{
				$this->after = $this->api_response->data->after;
			}

			if ( isset( $this->api_response->data->before ) )
			{
				$this->before = $this->api_response->data->before;
			}
		}

		return $this->reddit_links;
	}//END get_links_by_domain

	/**
	 * get the next page of link submissions. this should only be called
	 * after calling get_links_by_domain().
	 */
	public function next( $page_size = NULL )
	{
		if ( empty( $this->args['domain'] ) )
		{
			$this->errors[] = new WP_Error( 'next() error', 'missing request domain. next() should only be called after a get_links_* call' );
			return FALSE;
		}

		if ( ! empty( $page_size ) )
		{
			$this->args['limit'] = $page_size;
		}

		$this->args['after'] = $this->after;

		return $this->get_links_by_domain( $this->args );
	}//END next

}//END class