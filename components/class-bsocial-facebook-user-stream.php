<?php
/*
 * Facebook user stream (wall)
 *
 * Don't include this file or directly call its methods.
 * Use bsocial()->facebook()->user_stream() instead.
 */
class bSocial_Facebook_User_Stream
{
	public $bsocial_facebook = NULL;

	// urls to get to the next and previous pages
	public $previous_url = NULL;
	public $next_url = NULL;

	public function __construct( $bsocial_facebook )
	{
		$this->bsocial_facebook = $bsocial_facebook;
	}//END __construct

	/**
	 * get some number of posts from the authenticated user's wall
	 */
	public function get_posts( $limit = 2 )
	{
		if ( bsocial()->keyring() )
		{
			// Do something here?
		} // END if

		$user_id = $this->bsocial_facebook->get_user_id();

		if ( ! $user_id )
		{
			return FALSE;
		}

		$posts = $this->bsocial_facebook->facebook->api(
			'/' . $user_id . '/feed',
			'GET',
			array(
				'limit' => $limit,
			)
		);

		if ( isset( $posts->paging ) )
		{
			$this->previous_url = $posts->paging['previous'];
			$this->next_url = $posts->paging['next'];
		}

		return $posts;
	}//END get_posts
}//END class