<?php

class bSocial_Keyring_Twitter extends Keyring_Service_Twitter {
	const NAME  = 'bsocial-twitter';
	const LABEL = 'bSocial Twitter';
	
	public function __construct()
	{
		parent::__construct();
	}//END __construct

	public function get_http( $query_url, $parameters = array() )
	{
		return $this->http( $query_url, 'GET', $parameters );
	} // END get_http
	
	public function post_http( $query_url, $parameters = array() )
	{
		return $this->http( $query_url, 'POST', $parameters );
	}//END post_http
	
	public function http( $query_url, $method, $postfields = NULL )
	{		
		$parameters['method'] = $method;
		
		if ( $postfields )
		{
			$parameters['body'] = $postfields;
		} // END if
				
		return $this->request( $query_url, $parameters );
	} // END http
}//END class

add_action( 'keyring_load_services', array( 'bSocial_Keyring_Twitter', 'init' ) );