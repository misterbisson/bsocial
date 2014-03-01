<?php
/*
 * base class for bSocial's facebook-related functions.
 */
class bSocial_Facebook
{
	public $oauth    = NULL;
	public $comments = NULL;
	public $meta = NULL;
	public $user_stream = NULL;
	public $errors = array();

	/**
	 * Returns a generic facebook oauth instance
	 */
	public function oauth()
	{
		if ( $this->oauth )
		{
			return $this->oauth;
		} // END if

		// We don't care to pass any credentials because we're going to require Keyring for Facebook to work
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
	public function validate_query_url( $query_url )
	{
		if (
			0 == strpos( $query_url, 'http://' ) &&
			0 == strpos( $query_url, 'https://' )
		)
		{
			$query_url = 'https://graph.facebook.com/' . $query_url;
		} // END if

		return $query_url;
	}//END validate_query_url
	
	public function get_http( $query_url, $parameters = array() )
	{
		return $this->oauth()->get_http(
			$this->validate_query_url( $query_url ),
			$parameters
		);
	}//END get_http

	public function post_http( $query_url, $parameters = array() )
	{
		return $this->oauth()->post_http(
			$this->validate_query_url( $query_url ),
			$parameters
		);
	}//END post_http

	/**
	 * get the Facebook user_id of the current authenticated user
	 *
	 * @param $user_id WP user_id which Keyring will use to retrieve the FB user_id if left blank we'll use the current user
	 */
	public function get_fb_user_id( $user_id = FALSE )
	{
		// If a WP user_id value was given lets try to retrieve a token for that user instead
		if ( $user_id )
		{
			$parameters = array(
				'service' => 'facebook',
				'user_id' => $user_id,
			);

			$token = bsocial()->keyring()->get_token_store()->get_token( $parameters );
			
			if ( ! isset( $token->meta['user_id'] ) )
			{
				return FALSE;
			} // END if
			
			return $token->meta['user_id'];
		} // END if
		
		if ( ! isset( $this->oauth()->service->token->meta['user_id'] ) )
		{
			return FALSE;
		} // END if

		return $this->oauth()->service->token->meta['user_id'];
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
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function user_stream( $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if
		
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
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function get_permissions( $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		return $this->get_http( $this->get_fb_user_id() . '/permissions' );
	}//END get_profile

	/**
	 * @param $user_id WP user_id of the user you want to act as
	 * @param $page_id Facebook id of a page you wish to get the profile of
	 */
	public function get_profile( $user_id = FALSE, $page_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		if ( $page_id )
		{
			
		} // END if
		
		$profile_id = $page_id ? $page_id : $this->get_fb_user_id();

		return $this->get_http( $id );
	}//END get_profile
	
	public function get_page( $user_id = FALSE, $page_id )
	{
		return $this->get_profile( $user_id, $page_id );
	} // END get_page

	/**
	 * returns a array of Facebook pages the user has admin access to including access_tokens that can be used to post to those pages
	 *
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function get_pages( $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if
		
		return $this->get_http( $this->get_fb_user_id() . '/accounts' );
	} // END get_pages

	/**
	 * post a status update to the user's feed/wall
	 *
	 * @param $message the message to post
	 * @param $user_id WP user_id of the user you want to act as
	 * @retval string id of the newly created post
	 */
	public function post_status( $message, $user_id = FALSE )
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