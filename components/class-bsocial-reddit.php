<?php
/**
 * wrapper for our connection to reddit's services
 */
class bSocial_Reddit
{
	public $base_url = 'http://www.reddit.com';
	public $headers = NULL;
	public $response_code = NULL;
	public $errors = array();

	public function get_links_by_domain( $domain, $limit = 10 )
	{
		$args = array();
		$args['limit'] = $limit;

		$url = $this->base_url . '/domain/' . $domain . '/.json?' . build_query( $args );

		$result = wp_remote_get( $url );

		if ( is_wp_error( $result ) )
		{
			$this->errors[] = $result;
			return FALSE;
		}

		$this->headers = $result['headers'];
		$this->response_code = $result['response'];

		return $result['body'];
	}//END get_links_by_domain
}//END class