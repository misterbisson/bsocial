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
	} // END post_http

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

		if ( ! $this->oauth() )
		{
			return FALSE;
		} // END if

		if ( ! isset( $this->oauth()->service->token->meta['user_id'] ) )
		{
			return FALSE;
		} // END if

		return $this->oauth()->service->token->meta['user_id'];
	} // END get_fb_user_id

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
	} // END meta

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
	} // END comments

	/**
	 * re
	 *
	 * @param $user_id WP user_id of the user you want to act as
	 * @param $page_id FB id/name of a page you wish to get the stream for
	 */
	public function user_stream( $user_id = FALSE, $page_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		if ( $page_id )
		{
			if ( ! $page = $this->set_page_token( $page_id ) )
			{
				// User doesn't have access to the page
				return FALSE;
			} // END if

			// In case the $page_id is actually a page name we'll just make sure it is set correctly to an id value here
			$page_id = $page->id;
		} // END if

		if ( ! $this->user_stream )
		{
			if ( ! class_exists( 'bSocial_Facebook_User_Stream' ) )
			{
				require __DIR__ . '/class-bsocial-facebook-user-stream.php';
			}
			$this->user_stream = new bSocial_Facebook_User_Stream( $this );
		}//END if

		$this->user_stream->profile_id = $page_id ? $page_id : $this->get_fb_user_id();

		return $this->user_stream;
	} // END user_stream

	/**
	 * wrapper for user_stream
	 *
	 * @param $user_id WP user_id of the user you want to act as
	 * @param $page_id FB id/name of a page you wish to get the stream of
	 */
	public function page_stream( $user_id = FALSE, $page_id )
	{
		return $this->user_stream( $user_id, $page_id );
	} // END get_page

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
	 * returns profile object for a FB user or page
	 *
	 * @param $user_id WP user_id of the user you want to act as
	 * @param $page_id FB id/name of a page you wish to get the profile of
	 */
	public function get_profile( $user_id = FALSE, $page_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		if ( $page_id )
		{
			if ( ! $page = $this->set_page_token( $page_id ) )
			{
				// User doesn't have access to the page
				return FALSE;
			} // END if

			// In case the $page_id is actually a page name we'll just make sure it is set correctly to an id value here
			$page_id = $page->id;
		} // END if

		$profile_id = $page_id ? $page_id : $this->get_fb_user_id();

		return $this->get_http( $profile_id );
	} // END get_profile

	/**
	 * wrapper for get_profile
	 *
	 * @param $user_id WP user_id of the user you want to act as
	 * @param $page_id FB id/name of a page you wish to get the profile of
	 */
	public function get_page( $user_id = FALSE, $page_id )
	{
		return $this->get_profile( $user_id, $page_id );
	} // END get_page

	/**
	 * Sets the access token according to the page being edited if the current user has access to the page, returns FB get_pages object for the page
	 *
	 * @param $page_id Facebook id / or FB Name of a page you wish to get the profile of
	 */
	public function set_page_token( $page_id, $post_as_user = FALSE )
	{
		$user_pages = $this->get_pages();

		if ( ! isset( $user_pages->data ) )
		{
			return FALSE;
		} // END if

		$page = FALSE;

		foreach ( $user_pages->data as $user_page )
		{
			if ( is_numeric( $page_id ) && $page_id != $user_page->id )
			{
				continue;
			} // END if
			elseif ( ! is_numeric( $page_id ) && $page_id != $user_page->name )
			{
				continue;
			} // END elseif

			$page = $user_page;
		} // END foreach

		if ( $page && ! $post_as_user )
		{
			$this->oauth()->service->token = $page->access_token;
		} // END if

		return $page;
	} // END set_page_token

	/**
	 * returns an array of Facebook pages the user has admin access to including access_tokens that can be used to post to those pages
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
	 * publishes content to a user profile or FB page
	 *
	 * @param $method FB publishing method you wish to use (feed, comments, likes, events). See full list: https://developers.facebook.com/docs/reference/api/publishing/
	 * @param $data array of data you wish to publish to FB (acceptable values depends on publishing method)
	 * @param $user_id WP user_id of the user you want to act as
	 * @param $id FB id of the object you want to interact with (page, event, album, object). Leave blank for user profiles. See: https://developers.facebook.com/docs/reference/api/publishing/
	 */
	public function publish( $method, array $data, $user_id = FALSE, $id = FALSE )
	{
		$profile_methods = array(
			'feed',
			'notes',
			'links',
			'events',
			'albums',
		);

		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		// If this is a profile method and an $id value was provided this is a page
		if ( $id && in_array( $method, $profile_methods ) )
		{
			// Setting the second argument to TRUE will cause the post attempt to continue using the current user's token instead of a page specific one
			if ( ! $page = $this->set_page_token( $id, TRUE ) )
			{
				// User doesn't have access to the page
				return FALSE;
			} // END if

			// In case the $page_id is actually a page name we'll just make sure it is set correctly to an id value here
			$id = $page->id;
		} // END if

		$id = $id ? $id : $this->get_fb_user_id();

		return $this->post_http( $id . '/' . $method, $data );
	} // END publish

	/**
	 * post a status update to a user profile or FB page
	 *
	 * @param $message_info array of message info (message, picture, link, name, caption, description, source, place, tags). See full list: https://developers.facebook.com/docs/reference/api/post
	 * @param $user_id WP user_id of the user you want to act as
	 * @param $page_id FB id/name of a page you wish to post to
	 * @retval string id of the newly created post
	 */
	public function post_status( array $message_info, $user_id = FALSE, $page_id = FALSE )
	{
		return $this->publish( 'feed', $message_info, $user_id, $page_id );
	} // END post_status

	/**
	 * post an event to a user profile or FB page
	 *
	 * @param $event_info array of event info (name, start_time, end_time). See full list: https://developers.facebook.com/docs/graph-api/reference/event
	 * @param $user_id WP user_id of the user you want to act as
	 * @param $page_id FB id/name of a page you wish to post to
	 * @retval string id of the newly created event
	 */
	public function post_event( array $event_info, $user_id = FALSE, $page_id = FALSE )
	{
		return $this->publish( 'events', $event_info, $user_id, $page_id );
	} // END post_event
} // END bSocial_Facebook