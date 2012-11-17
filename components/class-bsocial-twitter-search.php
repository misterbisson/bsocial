<?php
/*
 * Twitter rest API glue
 * 
 * Don't include this file or directly call it's methods.
 * See new_twitter_search() and new_twitter_user_stream() instead.
 *
 */

/*
 * Twitter_Search class
 * 
 * Search Twitter with a given term or phrase
 * Example: $twitter_search->search ( array( 'q' => 'search phrase' )) 
 * 
 * Available query args: https://dev.twitter.com/docs/api/1/get/search
 *
 * @author Casey Bisson
 */
class bSocial_Twitter_Search
{
	var $get_user_info = TRUE;

	function tweets()
	{
		if( ! empty( $this->api_response->results ))
			return $this->api_response->results;
		else
			return FALSE;
	}

	function next()
	{
		if( ! empty( $this->api_response->next_page ))
			return $this->search( $this->args , 'next' );
		else
			return FALSE;
	}

	function refresh()
	{
		if( ! empty( $this->api_response->refresh_url ))
			return $this->search( $this->args , 'refresh' );
		else
			return FALSE;
	}

	function search( $args , $method = 'search' )
	{
		// parse the method
		switch( $method )
		{
			case 'next':
			case 'next_page':
				if( ! empty( $this->api_response->next_page ))
				{
					$query_url = 'http://search.twitter.com/search.json' . $this->api_response->next_page;
					unset( $this->api_response );
					break;
				}
			
			case 'refresh':
				if( ! empty( $this->api_response->refresh_url ))
				{
					$query_url = 'http://search.twitter.com/search.json' . $this->api_response->refresh_url;
					unset( $this->api_response );
					break;
				}

			case 'search':
			default:
				$defaults = array(
					'q' => urlencode( site_url() ),
					'rpp' => 10,
					'result_type' => 'recent',
					'page' => 1,
					'since_id' => FALSE,
					'lang' => FALSE,
					'locale' => FALSE,
					'until' => FALSE,
					'geocode' => FALSE,
					'show_user' => FALSE,
					'include_entities' => TRUE,
					'with_twitter_user_id' => TRUE,
				);
				$args = wp_parse_args( $args, $defaults );

				// save the args
				$this->args = $args;

				$query_url = add_query_arg( $args , 'http://search.twitter.com/search.json' );
		}

		$temp_results = wp_remote_get( $query_url );
		if ( is_wp_error( $temp_results ))
		{
			$this->error = $temp_results; 
			return FALSE;
		}

		$this->api_response = json_decode( wp_remote_retrieve_body( $temp_results ));
		$this->api_response_headers = wp_remote_retrieve_headers( $temp_results );
		unset( $temp_results );

		if( ! empty( $this->api_response->error ))
		{
			$this->error = $this->api_response; 
			unset( $this->api_response );
			return FALSE;
		}

		foreach( $this->api_response->results as $result )
		{
			// we can't rely on the user_ids in the result, so we do a name lookup and unset the unreliable data.
			// http://code.google.com/p/twitter-api/issues/detail?id=214
			if( $this->get_user_info )
				$result->from_user = twitter_user_info( $result->from_user );

			$this->api_response->min_id = $result->id;
			$this->api_response->min_id_str = $result->id_str;
		}

		return $this->api_response->results;
	}
}
