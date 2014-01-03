<?php
/*
 * Facebook user stream
 *
 * Don't include this file or directly call its methods.
 * See bsocial()->facebook_user_stream() instead.
 */

if ( ! class_exists( 'bSocial_Facebook' ) )
{
	require __DIR__ . '/class-bsocial-facebook.php';
}

/*
 * bSocial_Facebook_User_Stream class
 *
 * Get the public posts on a user's wall
 */
class bSocial_Facebook_User_Stream extends bSocial_Facebook
{
	// urls to get to the next and previous pages
	public $previous_url = NULL;
	public $next_url = NULL;

	public function get_posts( $limit = 2 )
	{
		if ( ! $this->facebook )
		{
			return new WP_Error( 'facebook auth error', 'error instantiating a Facebook instance.');
		}

		$user_id = $this->get_user_id();

		if ( is_wp_error( $user_id ) )
		{
			return $user_id;
		}

		$posts = $this->facebook->api(
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

	/**
	 * post a status update to the user's feed/wall
	 *
	 * @param $message the message to post
	 * @retval string id of the newly created post
	 */
	public function post( $message )
	{
		if ( ! $this->facebook )
		{
			return new WP_Error( 'facebook auth error', 'error instantiating a Facebook instance.');
		}

		// publish_actions is the permission needed to post to a user's wall
		$user_id = $this->get_user_id( array( 'publish_actions' ) );

		if ( is_wp_error( $user_id ) )
		{
			return $user_id;
		}

		try
		{
			$post_id = $this->facebook->api(
				'/' . $user_id . '/feed',
				'POST',
				array(
					'message' => $message,
				)
			);
		}
		catch ( Exception $e )
		{
			return new WP_Error( '/feed post error', $e->getMessage() );
		}

		return $post_id;
	}//END post
}//END class