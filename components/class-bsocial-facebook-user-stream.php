<?php
/*
 * Facebook user stream (wall)
 *
 * Don't include this file or directly call its methods.
 * Use bsocial()->facebook()->user_stream() instead.
 */
class bSocial_Facebook_User_Stream
{
	public $facebook = NULL;

	// urls to get to the next and previous pages
	public $previous_url = NULL;
	public $next_url = NULL;

	public $profile_id;

	public function __construct( $facebook )
	{
		$this->facebook = $facebook;
	} // END __construct

	/**
	 * get some number of posts from the authenticated user's wall
	 * @param $limit number of posts to return
	 */
	public function get_posts( $limit = 10 )
	{
		$posts = $this->facebook->get_http( $this->profile_id . '/feed', array( 'limit' => $limit ) );

		if ( isset( $posts->paging ) )
		{
			$this->previous_url = $posts->paging->previous;
			$this->next_url = $posts->paging->next;
		}

		return $posts;
	} // END get_posts
} // END bSocial_Facebook_User_Stream