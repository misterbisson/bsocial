<?php
/*
 * Twitter rest API glue
 * 
 * Don't include this file or directly call it's methods.
 * See bsocial()->new_twitter_search() instead.
 *
 */

/*
 * Twitter_Search class
 * 
 * Search Twitter with a given term or phrase
 * Example: $twitter_search->search ( array( 'q' => 'search phrase' ))
 * 
 * Available query args:
 *   https://dev.twitter.com/docs/api/1.1/get/search/tweets
 *
 * @author Casey Bisson
 */
class bSocial_Twitter_Search
{
	var $connection = NULL;
	var $get_user_info = NULL;

	function tweets()
	{
		if ( empty( $this->api_response->statuses ) )
		{
			return FALSE;
		}

		return $this->api_response->statuses;
	}//END tweets

	function next()
	{
		if ( empty( $this->api_response->search_metadata->next_results ) )
		{
			return FALSE;
		}

		return $this->search( $this->args , 'next' );
	}//END next

	function refresh()
	{
		if( empty( $this->api_response->search_metadata->refresh_url ) )
		{
			return FALSE;
		}

		return $this->search( $this->args , 'refresh' );
	}//END refresh

	function search( $connection, $args , $method = 'search' )
	{
		$this->connection = $connection;

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

		$this->api_response = $this->connection->get( $query_url, $this->args );

		if( ! empty( $this->api_response->errors ) )
		{
			$this->error = $this->api_response;
			unset( $this->api_response );
			return FALSE;
		}

		return $this->api_response->statuses;
	}//END search
}//END bSocial_Twitter_Search