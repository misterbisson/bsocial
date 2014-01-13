<?php
/*
 * Twitter_Search class
 *
 * Don't include this file or directly call it's methods. Use
 * bsocial()->twitter()->search() to get an insstance of this class instead.
 *
 * Search Twitter with a given term or phrase
 * Example: bsocial()->twitter->search()->search( array( 'q' => 'search phrase' ))
 *
 * Available query args:
 *   https://dev.twitter.com/docs/api/1.1/get/search/tweets
 *
 * @author Casey Bisson
 * @author Will Luo
 */
class bSocial_Twitter_Search
{
	public $twitter = NULL;      // handle to a bSocial_Twitter instance
	public $api_response = NULL;

	public function __construct( $twitter )
	{
		$this->twitter = $twitter;
	}//END __construct

	/**
	 * get the latest search results
	 */
	public function tweets()
	{
		if ( empty( $this->api_response->statuses ) )
		{
			return FALSE;
		}

		return $this->api_response->statuses;
	}//END tweets

	public function next()
	{
		if ( empty( $this->api_response->search_metadata->next_results ) )
		{
			return FALSE;
		}

		return $this->search( $this->args, 'next' );
	}//END next

	public function refresh()
	{
		if( empty( $this->api_response->search_metadata->refresh_url ) )
		{
			return FALSE;
		}

		return $this->search( $this->args, 'refresh' );
	}//END refresh

	public function search( $args, $method = 'search' )
	{
		// parse the method
		switch( $method )
		{
			case 'next':
			case 'next_page':
				if ( ! empty( $this->api_response->search_metadata->next_results ) )
				{
					$args = wp_parse_args( $this->api_response->search_metadata->next_results );
					unset( $this->api_response );
					break;
				}

			case 'refresh':
				if( ! empty( $this->api_response->search_metadata->refresh_url ) )
				{
					$args = wp_parse_args( $this->api_response->search_metadata->refresh_url );
					unset( $this->api_response );
					break;
				}

			case 'search':
			default:
				$defaults = array(
					'q' => NULL,
					'geocode' => NULL,
					'lang' => NULL,
					'locale' => NULL,
					'result_type' => 'recent',
					'count' => 10,
					'until' => NULL,
					'since_id' => NULL,
					'max_id' => NULL,
					'include_entities' => TRUE,
					'callback' => NULL,
				);

				$this->args = array_filter( wp_parse_args( $args, $defaults ) );

				$query_url = 'search/tweets';
		}//END switch

		$this->api_response = $this->twitter->get_http( $query_url, $this->args );

		if( ! empty( $this->api_response->errors ) )
		{
			$this->error = $this->api_response;
			unset( $this->api_response );
			return FALSE;
		}

		return $this->api_response->statuses;
	}//END search
}//END bSocial_Twitter_Search