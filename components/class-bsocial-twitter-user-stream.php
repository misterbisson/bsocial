<?php
/*
 * bSocial_Twitter_User_Stream class
 *
 * Don't include this file or directly call it's methods.
 * Use bsocial()->twitter()->user_stream() instead.
 *
 * Get the public Twitter history for a given user
 * Example: bsocial()->twitter()->user_stream( array( 'screen_name' => 'gigaom', 'count' => 2 ) )
 *
 * Available query args:
 *   https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
 *
 * @author Casey Bisson
 * @author will luo
 */
class bSocial_Twitter_User_Stream
{
	public $twitter = NULL;      // handle to a bSocial_Twitter instance
	public $api_response = NULL;

	public function __construct( $twitter )
	{
		$this->twitter = $twitter;
	}//END __construct

	public function tweets()
	{
		if ( empty( $this->api_response ) )
		{
			return FALSE;
		}

		return $this->api_response;
	}//END tweets

	public function next()
	{
		if ( empty( $this->api_response ) )
		{
			return FALSE;
		}

		return $this->stream( $this->args, 'next' );
	}//END next

	public function refresh()
	{
		if( empty( $this->api_response ) )
		{
			return FALSE;
		}

		return $this->stream( $this->args, 'refresh' );
	}//END refresh

	/**
	 * @param $args stream search args
	 * @param $method which stream search results to get
	 */
	public function get_stream( $args, $method = 'stream' )
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
		}//END switch

		$defaults = array(
			'user_id' => NULL,
			'screen_name' => NULL,
			'since_id' => NULL,
			'count' => 10,
			'max_id' => NULL,
			'trim_user' => 'true',
			'exclude_replies' => 'false',
			'contributor_details' => 'false',
			'include_rts' => 'true',
		);

		$this->args = array_filter( wp_parse_args( $args, $defaults ) );

		$this->api_response = $this->twitter->get_http( 'statuses/user_timeline', $this->args );

		if( ! empty( $this->api_response->errors ) )
		{
			$this->error = $this->api_response;
			unset( $this->api_response );
			return FALSE;
		}

		// set the max and min ids. note that the tweets are sorted in
		// desc ID/time order
		$this->max_id = $this->api_response[0]->id;
		$this->max_id_str = $this->api_response[0]->id_str;

		$min_id_idx = 1 < count( $this->api_response ) ? count( $this->api_response ) - 1 : 0;
		$this->min_id = $this->api_response[ $min_id_idx ]->id;
		$this->min_id_str = $this->api_response[ $min_id_idx ]->id_str;

		return $this->api_response;
	}//END get_stream
}//END class