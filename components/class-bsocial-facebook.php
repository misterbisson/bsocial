<?php
/*
 * base class for bSocial's facebook-related functions.
 */
class bSocial_Facebook
{
	public $facebook = NULL;
	public $oauth    = NULL;
	public $comments = NULL;
	public $meta = NULL;
	public $user_stream = NULL;
	public $errors = array();

	/**
	 * return an instance of the facebook oauth client
	 */
	public function facebook()
	{
		if ( bsocial()->keyring() )
		{
			return $this->oauth();
		} // END if

		if ( $this->facebook )
		{
			return $this->facebook;
		}

		// Start a facebook instance
		if ( ! empty( bsocial()->options()->facebook->access_token ) )
		{
			$this->facebook = bsocial()->new_facebook(
				bsocial()->options()->facebook->app_id,
				bsocial()->options()->facebook->secret,
				bsocial()->options()->facebook->access_token
			);
		} // END if
		else
		{
			$this->facebook = bsocial()->new_facebook(
				bsocial()->options()->facebook->app_id,
				bsocial()->options()->facebook->secret,
				NULL
			);
		} // END else

		return $this->facebook;
	}//END __construct

	/**
	 * Returns a generic facebook oauth instance
	 */
	public function oauth()
	{
		if ( $this->oauth )
		{
			return $this->oauth;
		} // END if

		$this->oauth = bsocial()->new_oauth(
			NULL,
			NULL,
			NULL,
			NULL,
			'facebook'
		);

		return $this->oauth;
	} // END oauth

	// prepend the facebook api url if $query_url is not absolute
	public function validate_query_url( $query_url, $parameters )
	{
		if (
			0 == strpos( $query_url, 'http://' ) &&
			0 == strpos( $query_url, 'https://' )
		)
		{
			return $query_url;
		} // END if

		$query_url = 'https://graph.facebook.com/' . $query_url;

		return $query_url;
	}//END validate_query_url

	/**
	 * get the Facebook id of the current authenticated user
	 *
	 * @param $scope an array of permissions to request. the list of
	 *        permissions can be found here:
	 *        https://developers.facebook.com/docs/reference/login/
	 */
	public function get_fb_user_id( $scope = NULL )
	{
		// Try getting it from the the Keyring service first
		if ( isset( $this->oauth()->service->token->meta['user_id'] ) )
		{
			return $this->oauth()->service->token->meta['user_id'];
		} // END if

		$fb_user_id = $this->facebook()->getUser();

		if ( ! $fb_user_id )
		{
			$login_url = $this->facebook()->getLoginUrl();
			if ( $scope )
			{
				$login_url .= '&scope=' . implode( ',', $scope );
			}
			$this->errors[] = new WP_Error( 'facebook auth error', 'user not logged in. Please <a href="' . $login_url . '">login</a> and try again.' );
			return FALSE;
		}//END if

		return $fb_user_id;
	}//END get_fb_user_id

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
	 * @param $fb_user_or_page_id Facebook id of a user or a page. if it's left blank
	 *  then we'll use the authenticated user's id.
	 */
	public function get_profile( $fb_user_or_page_id = NULL )
	{
		if ( empty( $fb_user_or_page_id ) )
		{
			$fb_user_or_page_id = $this->get_fb_user_id();
		}
		if ( ! $fb_user_or_page_id )
		{
			return FALSE;
		}

		if ( 0 !== strpos( $fb_user_or_page_id, '/' ) )
		{
			$fb_user_or_page_id = '/' . $fb_user_or_page_id;
		}

		try
		{
			return $this->facebook()->api( $fb_user_or_page_id, 'GET' );
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
		$fb_user_id = $this->get_fb_user_id( array( 'publish_actions' ) );

		if ( ! $fb_user_id )
		{
			return FALSE;
		}

		try
		{
			$post_id = $this->facebook()->api(
				'/' . $fb_user_id . '/feed',
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