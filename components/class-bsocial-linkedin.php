<?php

if ( ! class_exists( 'bSocial_OAuth' ) )
{
	require __DIR__ . '/class-bsocial-oauth.php';
}

class bSocial_LinkedIn extends bSocial_OAuth
{
	public function __construct()
	{
		// check if we can pass in the user token and secret or not
		if ( defined( 'GOAUTH_LINKEDIN_USER_TOKEN' ) && defined( 'GOAUTH_LINKEDIN_USER_SECRET' ) )
		{
			parent::__construct( GOAUTH_LINKEDIN_CONSUMER_KEY, GOAUTH_LINKEDIN_CONSUMER_SECRET, GOAUTH_LINKEDIN_USER_TOKEN, GOAUTH_LINKEDIN_USER_SECRET );
		}
		else
		{
			parent::__construct( GOAUTH_LINKEDIN_CONSUMER_KEY, GOAUTH_LINKEDIN_CONSUMER_SECRET );
		}
	}//END __construct
}//END class