<?php
/*
 * Open Graph integration
 * Adds Open Graph metadata to your pages
 *
 * Author: originally by Will Norris (http://willnorris.com/), extended and maintained by Casey Bisson
 * License: Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0.html)
 *
 * OG docs at http://ogp.me and https://developers.facebook.com/docs/reference/opengraph
 */

class bSocial_Opengraph
{
	public $ns_uri = 'http://ogp.me/ns#';
	public $ns_set = FALSE;

	public function __construct()
	{
		add_action( 'init', array( $this, 'init' ) );
	}// END __construct

	public function init()
	{
		add_filter( 'language_attributes', array( $this, 'language_attributes' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ) );

		// disable jetpack's og features, because they conflict
		add_filter( 'jetpack_enable_opengraph', '__return_false' );
	}// END init

	/**
	 * Add Open Graph XML namespace to <html> element.
	 */
	public function language_attributes( $output )
	{
		$this->ns_set = TRUE;

		$output .= ' prefix="og: ' . esc_attr( $this->ns_uri ) . '"';
		return $output;
	}// END language_attributes

	/**
	 * Output <meta> tags in the page header.
	 */
	public function wp_head()
	{
		$xml_ns = '';
		if ( ! $this->ns_set )
		{
			$xml_ns = 'xmlns:og="' . esc_attr( $this->ns_uri ) . '" ';
		}

		$metadata = $this->metadata();
		foreach ( $metadata as $key => $value )
		{
			if ( empty( $key ) || empty( $value ) )
			{
				continue;
			}

			// support arrays
			if ( is_array( $value ) )
			{
				foreach ( $value as $v )
				{
					if ( empty( $v ) )
					{
						continue;
					}

					echo '<meta ' . $xml_ns . 'property="' . esc_attr( $key ) . '" content="' . esc_attr( $v ) . '" />' . "\n";
				}
			}// END if
			else
			{
				// default to single instances
				echo '<meta ' . $xml_ns . 'property="' . esc_attr( $key ) . '" content="' . esc_attr( $value ) . '" />' . "\n";
			}//end else
		} // END foreach
	}// END wp_head

	/**
	 * Get the Open Graph metadata for the current page.
	 *
	 * @uses apply_filters() Calls 'opengraph_{$name}' for each property name
	 * @uses apply_filters() Calls 'opengraph_metadata' before returning metadata array
	 */
	public function metadata()
	{
		$metadata = array();

		// default properties defined at http://opengraphprotocol.org/
		$properties = array(
			// required
			'og:title', 'og:type', 'og:image', 'og:url',

			// optional
			'og:site_name', 'og:description',

			// optional, article-specific
			'article:author', 'article:modified_time', 'article:published_time', 'article:publisher', 'article:section', 'article:tag',

			// optional, book-specific
			'books:author', 'books:canonical_name', 'books:isbn',

			// location
			'og:longitude', 'og:latitude', 'place:location:longitude', 'place:location:latitude', 'og:street-address', 'og:locality', 'og:region',
			'og:postal-code', 'og:country-name',

			// contact
			'og:email', 'og:phone_number', 'og:fax_number',
		);

		$defaults = $this->defaults();

		foreach ( $properties as $property )
		{
			$filter = 'opengraph_' . str_replace( 'og:', '', $property ) ;
			$metadata[ $property ] = apply_filters( $filter, isset( $defaults[ $property ] ) ? $defaults[ $property ] : '' );
		}

		return apply_filters( 'opengraph_metadata', $metadata );
	}// END metadata

	/**
	 * Default values for common opengraph properties
	 */
	public function defaults()
	{

		// set the timezone to UTC for the later strtotime() call,
		// preserve the old timezone so we can set it back when done
		$old_tz = date_default_timezone_get();
		date_default_timezone_set( 'UTC' );

		if ( is_author() )
		{
			$author = apply_filters( 'opengraph_author_object', get_queried_object() );

			$return['og:description']     = get_the_author_meta( 'description', $author->ID );
			$return['og:image']           = $this->get_avatar( $author->user_email, 512 );
			$return['og:title']           = get_the_author_meta( 'display_name', $author->ID );
			$return['og:type']            = 'profile';
			$return['og:url']             = get_author_posts_url( $author->ID );
			$return['profile:first_name'] = get_the_author_meta( 'first_name', $author->ID );
			$return['profile:last_name']  = get_the_author_meta( 'last_name', $author->ID );
			$return['profile:username']   = get_the_author_meta( 'display_name', $author->ID );
		}// END if
		elseif ( is_singular() )
		{
			$post = get_queried_object();

			// get article-specific data
			$return['article:author']         = get_author_posts_url( $post->post_author );
			$return['article:modified_time']  = date( 'c', strtotime( $post->post_modified_gmt ) );
			$return['article:published_time'] = date( 'c', strtotime( $post->post_date_gmt ) );
			$return['article:publisher']      = bsocial()->options()->facebook->page;
			$return['article:tag']            = (array) wp_get_object_terms(
				$post->ID,
				(array) get_object_taxonomies( $post->post_type ),
				array(
					'orderby' => 'count',
					'order' => 'DESC',
					'fields' => 'names',
				)
			);

			// description and image not shown for passworded posts
			if ( post_password_required() )
			{
				$return['og:description'] = '';
			}
			else
			{
				$return['og:description'] = wp_kses(
					apply_filters(
						'the_excerpt',
						empty( $post->post_excerpt ) ? wp_trim_words( strip_shortcodes( $post->post_content ) ) : $post->post_excerpt
					),
					array()
				);

				$return['og:image'] = $this->get_thumbnail( $post->ID, 'large' );
			}// END else
			$return['og:title'] = empty( $post->post_title ) ? ' ' : wp_kses( get_the_title( $post->ID ), array() ) ;
			$return['og:type']  = 'article';
			$return['og:url']   = get_permalink( $post->ID );
		}// END elseif
		else
		{
			$return['og:description'] = wp_kses( get_bloginfo( 'description' ), array() );
			$return['og:title']       = wp_kses( get_bloginfo( 'name' ), array() );
			$return['og:type']        = empty( bsocial()->options()->opengraph->type ) ? 'blog' : bsocial()->options()->opengraph->type;

			// only attempt to expose a URL if on the front or home page
			// (this default might be used for search or tag archive pages with less predictable URLs)
			if ( is_home() || is_front_page() )
			{
				$return['og:url'] = home_url( '/' );
			}
		}// END else

		// reset the timezone
		date_default_timezone_set( $old_tz );

		return $return;
	}//END defaults

	/**
	 * get the post thumbnail, wrapper for wp_get_attachment_image_src()
	 */
	public function get_thumbnail( $post_id, $size = 'large' )
	{
		if (
			! current_theme_supports( 'post-thumbnails' ) || // only attempt to get the post thumbnail if the theme supports them
			! has_post_thumbnail( $post_id ) // only set the meta if we have a thumbnail
		)
		{
			return;
		}//END if

		$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
		if ( ! $thumbnail )
		{
			return;
		}//END if

		return $thumbnail[0];
	}// END get_thumbnail

	/**
	 * get author avatar
	 *
	 * this is derived from WP's core get_avatar() method, https://core.trac.wordpress.org/browser/tags/3.8/src/wp-includes/pluggable.php#L1669
	 * unfortunately, that method is too messy and there's no other clean way to get a gravatar
	 */
	public function get_avatar( $email, $size = 512, $ssl = FALSE )
	{

		if ( ! empty( $email ) )
		{
			$email_hash = md5( strtolower( trim( $email ) ) );
		}

		// set the host based on ssl
		if ( $ssl )
		{
			$host = 'https://secure.gravatar.com';
		}
		else
		{
			if ( ! empty( $email ) )
			{
				$host = sprintf( 'http://%d.gravatar.com', ( hexdec( $email_hash[0] ) % 2 ) );
			}
			else
			{
				$host = 'http://0.gravatar.com';
			}
		}// END else

		// work out the default avatar type
		$default = get_option( 'avatar_default' );
		if ( empty( $default ) )
		{
			$default = 'identicon';
		}

		if ( 'mystery' == $default )
		{
			$default = "$host/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}"; // ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
		}
		elseif ( 'blank' == $default )
		{
			$default = $email ? 'blank' : includes_url( 'images/blank.gif' );
		}
		elseif ( ! empty( $email ) && 'gravatar_default' == $default )
		{
			$default = '';
		}
		elseif ( 'gravatar_default' == $default )
		{
			$default = "$host/avatar/?s={$size}";
		}
		elseif ( empty( $email ) )
		{
			$default = "$host/avatar/?d=$default&amp;s={$size}";
		}
		elseif ( 0 === strpos( $default, 'http://' ) )
		{
			$default = add_query_arg( 's', $size, $default );
		}// END a bunch of elseifs

		// return an avatar uri
		if ( ! empty( $email ) )
		{
			$out = "$host/avatar/";
			$out .= $email_hash;
			$out .= '?s='.$size;
			$out .= '&amp;d=' . urlencode( $default );

			$rating = get_option( 'avatar_rating' );
			if ( ! empty( $rating ) )
			{
				$out .= "&amp;r={$rating}";
			}

			$out = str_replace( '&#038;', '&amp;', esc_url( $out ) );
			return $out;
		}// END if
		else
		{
			return $default;
		}// END else
	}// END get_avatar
}//END class