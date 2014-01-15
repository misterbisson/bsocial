<?php
/*
 * base class for bSocial's facebook-related functions.
 */
class bSocial_Facebook
{
	public $facebook = NULL;
	public $comments = NULL;
	public $meta = NULL;
	public $user_stream = NULL;
	public $errors = array();

	/**
	 * return an instance of the facebook oauth client
	 */
	public function facebook()
	{
		if ( $this->facebook )
		{
			return $this->facebook;
		}

		$this->facebook = bsocial()->new_facebook(
			bsocial()->options()->facebook->app_id,
			bsocial()->options()->facebook->secret
		);

		return $this->facebook;
	}//END __construct

	public function meta()
	{
		if ( ! $this->meta )
		{
			if ( ! class_exists( 'bSocial_Facebook_Meta' ) )
			{
				require __DIR__ .'/class-bsocial-facebook-meta.php';
			}

			$this->meta = new bSocial_Facebook_Meta;
		}//END if

		return $this->meta;
	}//END meta

	public function comments()
	{
		if ( ! $this->comments )
		{
			if ( ! class_exists( 'bSocial_Facebook_Comments' ) )
			{
				require __DIR__ .'/class-bsocial-facebook-comments.php';
			}

			$this->comments = new bSocial_Facebook_Comments;
		}//END if

		return $this->comments;
	}//END comments

	/**
	 * get an instance of bSocial_Facebook_User_Stream
	 */
	public function user_stream()
	{
		if ( ! $this->user_stream )
		{
			if ( ! class_exists( 'bSocial_Facebook_User_Stream' ) )
			{
				require __DIR__ . '/class-bsocial-facebook-user-stream.php';
			}
			$this->user_stream = new bSocial_Facebook_User_Stream( $this );
		}//END if
		return $this->user_stream;
	}//END user_stream

	/**
	 * get the id of the current authenticated user
	 *
	 * @param $scope an array of permissions to request. the list of
	 *        permissions can be found here:
	 *        https://developers.facebook.com/docs/reference/login/
	 */
	public function get_user_id( $scope = NULL )
	{
		$user_id = $this->facebook()->getUser();

		if ( ! $user_id )
		{
			$login_url = $this->facebook()->getLoginUrl();
			if ( $scope )
			{
				$login_url .= '&scope=' . implode( ',', $scope );
			}
			$this->errors[] = new WP_Error( 'facebook auth error', 'user not logged in. Please <a href="' . $login_url . '">login</a> and try again.' );
			return FALSE;
		}//END if

		return $user_id;
	}//END get_user_id

	/**
	 * @param $user_or_page_id id of a user or a page. if it's left blank
	 *  then we'll use the authenticated user's id.
	 */
	public function get_profile( $user_or_page_id = NULL )
	{
		if ( empty( $user_or_page_id ) )
		{
			$user_or_page_id = $this->get_user_id();
		}
		if ( ! $user_or_page_id )
		{
			return FALSE;
		}

		if ( 0 !== strpos( $user_or_page_id, '/' ) )
		{
			$user_or_page_id = '/' . $user_or_page_id;
		}

		try
		{
			return $this->facebook()->api( $user_or_page_id, 'GET' );
		}
		catch ( FacebookApiException $e )
		{
			$this->erros[] = new WP_Error( $e->getType(), $e->getMessage() );
		}

		return FALSE;
	}//END get_profile

	/**
	 * post a status update to the user's feed/wall
	 *
	 * @param $message the message to post
	 * @retval string id of the newly created post
	 */
	public function post_status( $message )
	{
		// publish_actions is the permission needed to post to a user's wall
		$user_id = $this->get_user_id( array( 'publish_actions' ) );

		if ( ! $user_id )
		{
			return FALSE;
		}

		try
		{
			$post_id = $this->facebook()->api(
				'/' . $user_id . '/feed',
				'POST',
				array(
					'message' => $message,
				)
			);
		}
		catch ( Exception $e )
		{
			$post_id = FALSE;
			$this->errors[] = new WP_Error( '/feed post error', $e->getMessage() );
		}

		return $post_id;
	}//END post_status
}//END class