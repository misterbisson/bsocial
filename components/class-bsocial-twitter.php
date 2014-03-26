<?php
/**
 * wrapper for our connection to the twitter services
 */
class bSocial_Twitter
{
	public $oauth = NULL;
	public $comments = NULL;
	public $meta = NULL;
	public $search = NULL;
	public $user_stream = NULL;
	public $user_id = NULL;
	public $user = NULL;

	/**
	 * get an oauth instance
	 */
	public function oauth()
	{
		if ( $this->oauth )
		{
			return $this->oauth;
		}

		// Start an oauth instance
		if ( ! empty( bsocial()->options()->twitter->access_token ) && ! empty( bsocial()->options()->twitter->access_secret ) )
		{
			$this->oauth = bsocial()->new_oauth(
				bsocial()->options()->twitter->consumer_key,
				bsocial()->options()->twitter->consumer_secret,
				bsocial()->options()->twitter->access_token,
				bsocial()->options()->twitter->access_secret,
				'twitter'
			);
		} // END if
		else
		{
			$this->oauth = bsocial()->new_oauth(
				bsocial()->options()->twitter->consumer_key,
				bsocial()->options()->twitter->consumer_secret,
				NULL,
				NULL,
				'twitter'
			);
		} // END else

		return $this->oauth;
	}//END oauth

	// prepend the twitter api url if $query_url is not absolute
	public function validate_query_url( $query_url, $parameters )
	{
		if (
			0 !== strpos( $query_url, 'http://' ) &&
			0 !== strpos( $query_url, 'https://' )
		)
		{
			$query_url = 'https://api.twitter.com/1.1/' . $query_url;

			if ( ! isset( $parameters['format'] ) )
			{
				$query_url .= '.json';
			}
			else
			{
				$query_url .= '.' . $parameters['format'];
			}
		}//END if

		return $query_url;
	}//END validate_query_url

	public function get_http( $query_url, $parameters = array() )
	{
		return $this->oauth()->get_http(
			$this->validate_query_url( $query_url, $parameters ),
			$parameters
		);
	}//END get_http

	public function post_http( $query_url, $parameters = array() )
	{
		return $this->oauth()->post_http(
			$this->validate_query_url( $query_url, $parameters ),
			$parameters
		);
	}//END post_http

	public function meta()
	{
		if ( ! $this->meta )
		{
			if ( ! class_exists( 'bSocial_Twitter_Meta' ) )
			{
				require __DIR__ .'/class-bsocial-twitter-meta.php';
			}

			$this->meta = new bSocial_Twitter_Meta;
		}//END if

		return $this->meta;
	}//END meta

	/**
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function comments( $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		if ( ! $this->comments )
		{
			if ( ! class_exists( 'bSocial_Twitter_Comments' ) )
			{
				require __DIR__ .'/class-bsocial-twitter-comments.php';
			}

			$this->comments = new bSocial_Twitter_Comments;
		}//END if

		return $this->comments;
	}//END comments

	/**
	 * return the twitter search object
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function search( $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		if ( ! $this->search )
		{
			if ( ! class_exists( 'bSocial_Twitter_Search' ) )
			{
				require __DIR__ .'/class-bsocial-twitter-search.php';
			}

			$this->search = new bSocial_Twitter_Search( $this );
		}//END if

		return $this->search;
	}//END search

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
			if ( ! class_exists( 'bSocial_Twitter_User_Stream' ) )
			{
				require __DIR__ .'/class-bsocial-twitter-user-stream.php';
			}

			$this->user_stream = new bSocial_Twitter_User_Stream( $this );
		}//END if

		return $this->user_stream;
	}//END user_stream

	/**
	 * Look up info about the twitter user by their screen name or ID
	 * Note: the ID here is not compatible with the user ID returned from
	 * the search API. This is a Twitter limitation.
	 *
	 * method docs: https://dev.twitter.com/docs/api/1.1/get/users/show
	 * useful: $user->name, $user->screen_name, $user->id_str,
	 *         $user->followers_count
	 *
	 * @param $screen_name user screen name or id
	 * @param $by 'screen_name' or 'id'
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function get_user_info( $screen_name, $by = 'screen_name', $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		// are we searching by screen name or ID?
		$by = in_array( $by, array( 'screen_name', 'id' )) ? $by : 'screen_name';

		// check the cache for the user info
		if ( ! $user = wp_cache_get( (string) $screen_name, 'twitter_' . $by ) )
		{
			// check Twitter for the user info
			$user = $this->get_http( 'users/show', array( $by => $screen_name ) );

			if ( empty( $user->errors ) )
			{
				wp_cache_set( (string) $screen_name, $user, 'twitter_screen_name', 604801 ); // cache for 7 days
			}
		}//END if

		return $user;
	}//END get_user_info

	/**
	 * @param $message the message to tweet
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function post_tweet( $message, $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		return $this->post_http( 'statuses/update', array( 'status' => $message ) );
	}//END post_tweet

	/**
	 * @param $tweet_id the id of the tweet you want to retweet
	 * @param $user_id WP user_id of the user you want to act as
	 */
	public function retweet( $tweet_id, $user_id = FALSE )
	{
		if ( $user_id )
		{
			$this->oauth()->set_keyring_user_token( $user_id );
		} // END if

		return $this->post_http( 'statuses/retweet/' . absint( $tweet_id ) );
	} // END retweet

	/**
	 * Get current Twitter.com configuration.
	 * Useful for getting things like the current short_url_length and short_url_length_https values.
	 * See: https://dev.twitter.com/docs/api/1.1/get/help/configuration
	 */
	public function get_twitter_help_configuration()
	{
		return $this->get_http( 'help/configuration' );
	} // END get_twitter_help_configuration
}//END class