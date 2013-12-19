<?php

class bSocial_Twitter
{
	public $connection = NULL;

	public function __construct()
	{
		if ( ! class_exists( 'TwitterOAuth' ) )
		{
			require __DIR__ . '/external/twitteroauth/twitteroauth/twitteroauth.php';
		}
		$this->connection = new TwitterOAuth(
			GOAUTH_TWITTER_CONSUMER_KEY,
			GOAUTH_TWITTER_CONSUMER_SECRET,
			GOAUTH_TWITTER_ACCESS_TOKEN,
			GOAUTH_TWITTER_ACCESS_TOKEN_SECRET
		);
	}//END __construct

	public function get_http( $query_url, $parameters )
	{
		return $this->connection->get( $query_url, $parameters );
	}
}//END class