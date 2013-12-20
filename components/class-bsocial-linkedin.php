<?php

class bSocial_LinkedIn
{
	// curl options
	public $timeout = 30;        // default timeout
	public $connecttimeout = 30; // default connect timeout.
	public $ssl_verifypeer = FALSE; // Verify peer SSL Cert.?
	public $useragent = 'bSocial_LinkedIn v0.1';
	public $http_header = array();

	public $consumer = NULL;
	public $token = NULL;
	public $sha1_method = NULL;

	public function __construct()
	{
		if ( ! class_exists( 'OAuthRequest' ) )
		{
			require __DIR__ . '/external/OAuth.php';
		}

		$this->consumer = new OAuthConsumer( GOAUTH_LINKEDIN_CONSUMER_KEY, GOAUTH_LINKEDIN_CONSUMER_SECRET );

		if ( defined( 'GOAUTH_LINKEDIN_USER_TOKEN' ) && defined( 'GOAUTH_LINKEDIN_USER_SECRET' ) )
		{
			$this->token = new OAuthConsumer( GOAUTH_LINKEDIN_USER_TOKEN, GOAUTH_LINKEDIN_USER_SECRET );
		}
		$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
	}//END __construct

	public function get_http( $query_url, $parameters = array() )
	{
		if ( ! isset( $parameters['format'] ) )
		{
			$parameters['format'] = 'json';
		}

		$request = OAuthRequest::from_consumer_and_token( $this->consumer, $this->token, 'GET', $query_url, $parameters );

		$request->sign_request( $this->sha1_method, $this->consumer, $this->token );

		return json_decode( $this->http( $request->to_url(), 'GET' ) );
	}

	// from twitteroauth
	public function http( $url, $method, $postfields = NULL )
	{
		$this->http_info = array();
		$ci = curl_init();

		/* Curl settings */
		curl_setopt( $ci, CURLOPT_USERAGENT, $this->useragent );
		curl_setopt( $ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout );
		curl_setopt( $ci, CURLOPT_TIMEOUT, $this->timeout );
		curl_setopt( $ci, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ci, CURLOPT_HTTPHEADER, array('Expect:' ) );
		curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer );
		curl_setopt( $ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader' ) );
		curl_setopt( $ci, CURLOPT_HEADER, FALSE );

		switch ( $method )
		{
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if ( ! empty( $postfields ) )
				{
					curl_setopt( $ci, CURLOPT_POSTFIELDS, $postfields );
				}
				break;

			case 'DELETE':
				curl_setopt( $ci, CURLOPT_CUSTOMREQUEST, 'DELETE' );
				if ( ! empty( $postfields ) )
				{
					$url = "{$url}?{$postfields}";
				}
		}//END switch

		curl_setopt( $ci, CURLOPT_URL, $url );
		$response = curl_exec( $ci );

		$this->http_code = curl_getinfo( $ci, CURLINFO_HTTP_CODE );
		$this->http_info = array_merge( $this->http_info, curl_getinfo( $ci ) );
		$this->url = $url;
		curl_close( $ci );
		return $response;
	}//END http

	/**
	 * store header info
	 */
	public function getHeader( $ch, $header )
	{
		$i = strpos($header, ':');
		if ( ! empty( $i ) )
		{
			$key = str_replace( '-', '_', strtolower( substr( $header, 0, $i ) ) );
			$value = trim( substr( $header, $i + 2 ) );
			$this->http_header[ $key ] = $value;
		}
		return strlen( $header );
	}//END getHeader

}//END class