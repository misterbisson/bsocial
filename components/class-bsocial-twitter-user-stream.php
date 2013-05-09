<?php
/*
 * Twitter rest API glue
 * 
 * Don't include this file or directly call it's methods.
 * See bsocial()->new_twitter_user_stream() instead.
 *
 */

/*
 * bSocial_Twitter_User_Stream class
 * 
 * Get the public Twitter history for a given user
 * Example: $twitter_search->search ( array( 'q' => 'search phrase' )) 
 * 
 * Available query args: https://dev.twitter.com/docs/api/1/get/statuses/user_timeline
 *
 * @author Casey Bisson
 */
class bSocial_Twitter_User_Stream
{
	function tweets()
	{
		if( ! empty( $this->api_response ))
			return $this->api_response;
		else
			return FALSE;
	}

	function next()
	{
		if( ! empty( $this->api_response ))
			return $this->stream( $this->args , 'next' );
		else
			return FALSE;
	}

	function refresh()
	{
		if( ! empty( $this->api_response ))
			return $this->stream( $this->args , 'refresh' );
		else
			return FALSE;
	}

	function stream( $args , $method = 'stream' )
	{

		switch( $method )
		{
			case 'next':
			case 'next_page':
				$args['max_id'] = $this->api_response[ count( $this->api_response ) -1 ]->id_str;
				unset( $this->api_response );
				break;
			
			case 'refresh':
				$args['since_id'] = $this->api_response[0]->id_str;
				unset( $this->api_response );
				break;
		}

		$defaults = array(
			'user_id' => FALSE,
			'screen_name' => FALSE,
			'since_id' => FALSE,
			'max_id' => FALSE,
			'count' => 10,
			'page' => FALSE,
			'trim_user' => 'true',
			'contributor_details' => 'false',
			'include_entities' => 'true',
			'exclude_replies' => 'false',
			'include_rts' => 'true',
		);
		$args = wp_parse_args( $args, $defaults );

		// save the args
		$this->args = $args;

		$query_url = add_query_arg( $args , 'http://api.twitter.com/1/statuses/user_timeline.json' );

		$temp_results = wp_remote_get( $query_url );
		if ( is_wp_error( $temp_results ))
		{
			$this->error = $temp_results; 
			return FALSE;
		}

		// fetch that stuff
		$this->api_response = json_decode( wp_remote_retrieve_body( $temp_results ));
		$this->api_response_headers = wp_remote_retrieve_headers( $temp_results );
		unset( $temp_results );

		if( ! empty( $this->api_response->error ))
		{
			$this->error = $this->api_response; 
			unset( $this->api_response );
			return FALSE;
		}

		// set the max and min ids
		$this->max_id = $this->api_response[0]->id;
		$this->max_id_str = $this->api_response[0]->id_str;
		$this->min_id = $this->api_response[ count( $this->api_response ) -1 ]->id;
		$this->min_id_str = $this->api_response[ count( $this->api_response ) -1 ]->id_str;

		// return that stuff
		return $this->api_response;
	}
}
