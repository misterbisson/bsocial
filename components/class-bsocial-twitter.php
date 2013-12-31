<?php

if ( ! class_exists( 'bSocial_OAuth' ) )
{
	require __DIR__ . '/class-bsocial-oauth.php';
}

class bSocial_Twitter extends bSocial_OAuth
{
	public function __construct()
	{
		// check if we can pass in the user token and secret or not
		if ( defined( 'GOAUTH_TWITTER_ACCESS_TOKEN' ) && defined( 'GOAUTH_TWITTER_ACCESS_TOKEN_SECRET' ) )
		{
			parent::__construct( GOAUTH_TWITTER_CONSUMER_KEY, GOAUTH_TWITTER_CONSUMER_SECRET, GOAUTH_TWITTER_ACCESS_TOKEN, GOAUTH_TWITTER_ACCESS_TOKEN_SECRET );
		}
		else
		{
			parent::__construct( GOAUTH_TWITTER_CONSUMER_KEY, GOAUTH_TWITTER_CONSUMER_SECRET);
		}
	}//END __construct

	// prepend the twitter api url if $query_url is not absolute
	public function validate_query_url( $query_url, $parameters )
	{
		if (
			0 !== strpos( $query_url, 'http://' ) &&
			0 !== strpos( $query_url, 'https://' )
		)
		{
			$query_url = 'https://api.twitter.com/1.1/' . $query_url;

			if ( ! isset( $parameters['format'] ) )
			{
				$query_url .= '.json';
			}
			else
			{
				$query_url .= '.' . $parameters['format'];
			}
		}//END if

		return $query_url;
	}//END validate_query_url

	public function get_http( $query_url, $parameters = array() )
	{
		return parent::get_http( $this->validate_query_url( $query_url, $parameters ), $parameters );
	}//END get_http

	public function post_http( $query_url, $parameters = array() )
	{
		return parent::post_http( $this->validate_query_url( $query_url, $parameters ), $parameters );
	}//END post_http
}//END class