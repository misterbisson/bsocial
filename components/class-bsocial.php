<?php

class bSocial
{
	public $id_base = 'bsocial';

	public $facebook = NULL;
	public $featuredcomments = NULL;
	public $linkedin = NULL;
	public $opengraph = NULL;
	public $twitter = NULL;
	public $reddit = NULL;

	private $options = NULL;

	public function __construct()
	{
		// activate components
		add_action( 'init', array( $this, 'init' ), 1 );

		// hooks for methods in this class
		add_action( 'wp_ajax_show_cron', array( $this, 'show_cron' ) );
		add_action( 'delete_comment', array( $this, 'comment_id_by_meta_delete_cache' ) );
	}

	/**
	 * Singleton for the keyring plugin
	 */
	public function keyring()
	{
		global $keyring;

		if ( class_exists( 'Keyring' ) && ! is_object( $keyring ) )
		{
			$keyring = Keyring::init();
		} // END if

		return $keyring;
	} // END keyring

	public function init()
	{
		$options = $this->options();

		if ( $options->facebook->enable )
		{
			if (
				$options->facebook->meta ||
				$options->facebook->js
			)
			{
				$this->opengraph();
				$this->facebook()->meta();
			}

			if ( $options->facebook->comments )
			{
				$this->facebook()->comments();
			}

			// if facebook is enabled, the widgets are enabled
			require_once __DIR__ .'/widgets-facebook.php';
		}//END if

		if ( $options->linkedin->enable )
		{
			if (
				$options->linkedin->meta ||
				$options->linkedin->js
			)
			{
				$this->opengraph();
//				$this->linkedin()->meta();
			}
		}//END if

		if ( $options->twitter->enable )
		{
			if (
				$options->twitter->meta ||
				$options->twitter->js
			)
			{
				$this->opengraph();
				$this->twitter()->meta();
			}

			if ( $options->twitter->comments )
			{
				$this->twitter()->comments();
			}
		}//END if

		// featured comments
		if ( $options->featuredcomments->enable )
		{
			$this->featuredcomments();
		}

		// opengraph (though it was probably loaded above)
		if ( ! $this->opengraph && $options->opengraph->enable )
		{
			$this->opengraph();
		}

		// the admin settings page
		if ( is_admin() )
		{
			$this->admin();
		}
	}//END init


	/**
	 * object accessors
	 */
	public function admin()
	{
		if ( ! isset( $this->admin ) )
		{
			require_once __DIR__ . '/class-bsocial-admin.php';
			$this->admin = new bSocial_Admin;
		}

		return $this->admin;
	}

	public function facebook()
	{
		if ( ! $this->facebook )
		{
			if ( ! class_exists( 'bSocial_Facebook' ) )
			{
				require __DIR__ .'/class-bsocial-facebook.php';
			}
			$this->facebook = new bSocial_Facebook();
		}
		return $this->facebook;
	}//END facebook

	public function featuredcomments()
	{
		if ( ! $this->featuredcomments )
		{
			if ( ! class_exists( 'bSocial_Featuredcomments' ) )
			{
				require __DIR__ .'/class-bsocial-featuredcomments.php';
			}
			$this->featuredcomments = new bSocial_Featuredcomments();
		}
		return $this->featuredcomments;
	}//END featuredcomments

	public function linkedin()
	{
		if ( ! $this->linkedin )
		{
			if ( ! class_exists( 'bSocial_LinkedIn' ) )
			{
				require __DIR__ .'/class-bsocial-linkedin.php';
			}
			$this->linkedin = new bSocial_LinkedIn();
		}
		return $this->linkedin;
	}//END linkedin

	public function new_oauth( $consumer_key, $consumer_secret, $access_token = NULL, $access_secret = NULL, $service = NULL )
	{
		if ( ! class_exists( 'bSocial_OAuth' ) )
		{
			require __DIR__ . '/class-bsocial-oauth.php';
		}

		return new bSocial_OAuth( $consumer_key, $consumer_secret, $access_token, $access_secret, $service );
	}//END new_oauth

	public function opengraph()
	{
		if ( ! $this->opengraph )
		{
			if ( ! class_exists( 'bSocial_Opengraph' ) )
			{
				require __DIR__ .'/class-bsocial-opengraph.php';
			}
			$this->opengraph = new bSocial_Opengraph();
		}
		return $this->opengraph;
	}//END opengraph

	public function twitter()
	{
		if ( ! $this->twitter )
		{
			if ( ! class_exists( 'bSocial_Twitter' ) )
			{
				require __DIR__ .'/class-bsocial-twitter.php';
			}
			$this->twitter = new bSocial_Twitter();
		}
		return $this->twitter;
	}//END twitter

	public function reddit()
	{
		if ( ! $this->reddit )
		{
			if ( ! class_exists( 'bSocial_Reddit' ) )
			{
				require __DIR__ .'/class-bsocial-reddit.php';
			}
			$this->reddit = new bSocial_Reddit();
		}
		return $this->reddit;
	}//END reddit

	/**
	 * plugin options getter
	 */
	public function options()
	{
		if ( ! $this->options )
		{
			$this->options = (object) apply_filters(
				'go_config',
				wp_parse_args( (array) get_option( $this->id_base ), (array) $this->options_default() ),
				$this->id_base
			);
		}

		return $this->options;
	} // END options

	public function options_default()
	{
		// please note that most arrays are coerced to objects
		return (object) array(
			// social network integrations
			'facebook' => (object) array(
				'enable' => 1,
				'meta' => 1,
				'js' => 1,

				'admins' => '',
				'page' => '',

				'add_button' => 1,
				'comments' => 0,
			),
			'linkedin' => (object) array(
				'enable' => 1,
				'meta' => 1,
				'js' => 1,
			),
			'twitter' => (object) array(
				'enable' => 1,
				'meta' => 1,
				'js' => 1,

				'consumer_key' => '',
				'consumer_secret' => '',
				'access_token' => '',
				'access_secret' => '',

				'username' => '',
				'comments' => 1,
			),

			// features
			'featuredcomments' => (object) array(
				'enable' => 1,

				'use_commentdate' => 1,
				'add_to_waterfall' => 1,
			),
			'opengraph' => (object) array(
				'enable' => 1,
				'type' => 'blog',
			),

			// suppressed options (hides them from options page)
			// this is only useful if the options are being set using a go_config filter
			// it does not block somebody from setting options for these values
			// note that these are not coerced to objects
			'suppress' => array(
				// commented out because it's useful as an explanation, but not as a default
				// 'facebook' => array(
				// 	'subcomponent' => '', // only top level components are supported for now, so this is aspirational
				// )
			),
		);
	} // END options_default

	/**
	 * utility methods used by other components
	 */
	public function url_to_blogid( $url )
	{
		if ( ! is_multisite() )
		{
			return FALSE;
		}

		global $wpdb, $base;

		$url = parse_url( $url );
		if ( is_subdomain_install() )
		{
			return get_blog_id_from_url( $url['host'], '/' );
		}
		/**
		 * This else condition will only happen when is_subdomain_install() 
		 * is false. I.e.: it should never happen on WP.com
		 * 
		 * get_blog_id_from_url() in WP core can't handle URLs that include
		 * a post's permalink, so we work around it with some custom queries.
		 */
		elseif ( ! empty( $url['path'] ) )
		{
			// get the likely blog path
			$path = explode( '/', ltrim( substr( $url['path'], strlen( $base ) ), '/' ) );
			$path = empty( $path[0] ) ? '/' : '/'. $path[0] .'/';
			// get all blog paths for this domain
			if ( ! $paths = wp_cache_get( $url['host'], 'paths-for-domain' ) )
			{
				$paths = $wpdb->get_col( "SELECT path FROM $wpdb->blogs WHERE domain = '". $wpdb->escape( $url['host'] ) ."' /* url_to_blogid */" );
				wp_cache_set( $url['host'], $paths, 'paths-for-domain', 3607 ); // cache it for an hour
			}
			// chech if the given path is among the known paths
			// allows us to differentiate between paths of the main blog and those of sub-blogs
			$path = in_array( $path, $paths ) ? $path : '/';
			return get_blog_id_from_url( $url['host'], $path );
		}//END elseif

		// cry uncle, return 1
		return 1;
	}//END url_to_blogid

	public function find_urls( $text )
	{
		// nice regex thanks to John Gruber http://daringfireball.net/2010/07/improved_regex_for_matching_urls
		preg_match_all( '#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\) ))*\) )+(?:\(([^\s()<>]+|(\([^\s()<>]+\) ))*\)|[^\s`!()\[\]{};:\'".,<>?гхрсту]) )#', $text, $urls );

		return $urls[0];
	}

	public function follow_url( $location, $verbose = FALSE, $refresh = FALSE )
	{
		if ( $refresh || ( ! $trail = wp_cache_get( (string) $location, 'follow_url' ) ) )
		{
			$headers = get_headers( $location );
			$trail = array();
			$destination = $location;
			foreach( (array) $headers as $header )
			{
				if ( 0 === stripos( $header, 'HTTP' ) )
				{
					preg_match( '/ [1-5][0-9][0-9] /', $header, $matches );
					$trail[] = array( 'location' => $destination, 'response' => trim( $matches[0] ) );
				}

				if( 0 === stripos( $header, 'Location' ) )
				{
					$destination = array_pop( $this->find_urls( $header ) );
				}
			}//END foreach

			wp_cache_set( (string) $location, $trail, 'follow_url', 3607 ); // cache for an hour
		}//END if

		if( $verbose )
		{
			return $trail;
		}
		else
		{
			return $trail[ count( $trail ) - 1 ]['location'];
		}
	}//END follow_url

	/**
	 * this method uses a custom SQL query because it's way more performant
	 * than the SQL from WP's core WP_Comment_Query class.
	 * 
	 * The main problem: joins on tables with BLOB or TEXT columns _always_
	 * go to temp tables on disk. See http://dev.mysql.com/doc/refman/5.5/en/internal-temporary-tables.html
	 */
	public function comment_id_by_meta( $metavalue, $metakey )
	{
		global $wpdb;

		if ( ! $comment_id = wp_cache_get( (string) $metakey .':'. (string) $metavalue, 'comment_id_by_meta' ) )
		{
			$comment_id = $wpdb->get_var( $wpdb->prepare( 'SELECT comment_id FROM ' . $wpdb->commentmeta . ' WHERE meta_key = %s AND meta_value = %s', $metakey, $metavalue ) );
			wp_cache_set( (string) $metakey .':'. (string) $metavalue, $comment_id, 'comment_id_by_meta' );
		}

		return $comment_id;
	}//END comment_id_by_meta

	public function comment_id_by_meta_update_cache( $comment_id, $metavalue, $metakey )
	{
		if ( 0 < $comment_id )
		{
			return;
		}

		if ( ( ! $metavalue ) || ( ! $metakey ) )
		{
			return;
		}

		wp_cache_set( (string) $metakey .':'. (string) $metavalue, (int) $comment_id, 'comment_id_by_meta' );
	}//END comment_id_by_meta_update_cache

	public function comment_id_by_meta_delete_cache( $comment_id )
	{
		foreach ( (array) get_metadata( 'comment', $comment_id ) as $metakey => $metavalues )
		{
			foreach( $metavalues as $metavalue )
			{
				wp_cache_delete( (string) $metakey .':'. (string) $metavalue, 'comment_id_by_meta' );
			}
		}
	}//END comment_id_by_meta_delete_cache

	public function json_int_to_string( $string )
	{
		//32-bit PHP doesn't play nicely with the large ints FB returns, so we
		//encapsulate large ints in double-quotes to force them to be strings
		//http://stackoverflow.com/questions/2907806/handling-big-user-ids-returned-by-fql-in-php
		return preg_replace( '/:(\d+)/', ':"${1}"', $string );
	}//END json_int_to_string

	// Show cron array for debugging
	public function show_cron()
	{
		if ( current_user_can( 'manage_options' ) )
		{
			echo '<pre>' .  print_r( _get_cron_array(), TRUE ) . '</pre>';
		};
		exit;
	}//END show_cron
}//END class

function bsocial()
{
	global $bsocial;

	if( ! $bsocial )
	{
		$bsocial = new bSocial();
	}

	return $bsocial;
}//END bsocial
