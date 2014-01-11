<?php
/*
 * Open Graph integration
 * Adds Open Graph metadata to your pages
 *
 * Author: originally by Will Norris (http://willnorris.com/), extended and maintained by Casey Bisson
 * License: Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0.html)
 */

class bSocial_Opengraph
{
	public $ns_uri = 'http://ogp.me/ns#';
	public $ns_set = FALSE;

	public function __construct()
	{
		add_action( 'init', array( $this, 'init' ) );
	} // END __construct

	public function init()
	{
		// actions and filters that interact with WP
		add_filter( 'language_attributes', array( $this, 'language_attributes' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ) );

		// filters for default opengraph metadata
		add_filter( 'opengraph_title', array( $this, 'default_title' ), 5 );
		add_filter( 'opengraph_type', array( $this, 'default_type' ), 5 );
		add_filter( 'opengraph_image', array( $this, 'default_image' ), 5 );
		add_filter( 'opengraph_url', array( $this, 'default_url' ), 5 );
		add_filter( 'opengraph_site_name', array( $this, 'default_sitename' ), 5 );
		add_filter( 'opengraph_description', array( $this, 'default_description' ), 5 );
	} // END init

	/**
	 * Add Open Graph XML namespace to <html> element.
	 */
	public function language_attributes( $output )
	{
		$this->ns_set = TRUE;

		$output .= ' prefix="og: ' . esc_attr( $this->ns_uri ) . '"';
		return $output;
	}

	/**
	 * Get the Open Graph metadata for the current page.
	 *
	 * @uses apply_filters() Calls 'opengraph_{$name}' for each property name
	 * @uses apply_filters() Calls 'opengraph_metadata' before returning metadata array
	 */
	public function metadata()
	{
		$metadata = array();

	 	// defualt properties defined at http://opengraphprotocol.org/
		$properties = array(
			// required
			'title', 'type', 'image', 'url',

			// optional
			'site_name', 'description',

			// location
			'longitude', 'latitude', 'street-address', 'locality', 'region',
			'postal-code', 'country-name',

			// contact
			'email', 'phone_number', 'fax_number',
		);

		foreach ( $properties as $property )
		{
			$filter = 'opengraph_' . $property;
			$metadata[ "og:$property" ] = apply_filters( $filter, '' );
		}

		return apply_filters( 'opengraph_metadata', $metadata );
	}

	/**
	 * Default title property, using the page title.
	 */
	public function default_title( $title = '' )
	{
		if ( is_singular() && empty( $title ) )
		{
			$title = get_queried_object()->post_title;
		}

		return $title;
	}

	/**
	 * Default type property.
	 */
	public function default_type( $type = '' )
	{
		if ( empty( $type ) )
		{
			$type = 'blog';
		}

		return $type;
	}

	/**
	 * Default image property, using the post-thumbnail.
	 */
	public function default_image( $image = '' )
	{
		if (
			is_singular() && // only operate on single posts or pages, not the front page of the site
			empty( $image ) && // don't replace the image if one is already set
			current_theme_supports( 'post-thumbnails' ) && // only attempt to get the post thumbnail if the theme supports them
			has_post_thumbnail( get_queried_object_id() ) // only set the meta if we have a thumbnail
		)
		{
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( get_queried_object_id() ), 'large');
			if ( $thumbnai )
			{
				$image = $thumbnail[0];
			}
		} // END if

		return $image;
	}

	/**
	 * Default url property, using the permalink for the page.
	 */
	public function default_url( $url = '' )
	{
	    if ( ! empty( $url )) return $url;

		if ( is_singular() )
		{
			$url = get_permalink( get_queried_object_id() );
		}
		else
		{
			$url = trailingslashit( get_bloginfo( 'url' ) );
		}

		return $url;
	}

	/**
	 * Default site_name property, using the bloginfo name.
	 */
	public function default_sitename( $name = '' )
	{
		if ( empty( $name ) )
		{
			$name = get_bloginfo( 'name' );
		}
		return $name;
	}

	/**
	 * Default description property, using the bloginfo description.
	 */
	public function default_description( $description = '' )
	{
	    if ( ! empty( $description ) )
	    {
		    return $description;
	    }

		// get blog description as default
	    $description = get_bloginfo( 'description' );

		// replace the description with a more specific one if available
	    if ( is_singular() )
	    {
			$description = wp_kses( apply_filters( 'the_excerpt', empty( get_queried_object()->post_excerpt ) ? wp_trim_words( strip_shortcodes( get_queried_object()->post_content ) ) : get_queried_object()->post_excerpt ), array() );
	    }

	    return $description;
	}

	/**
	 * Output Open Graph <meta> tags in the page header.
	 */
	public function wp_head()
	{
		$xml_ns = '';
		if ( ! $this->ns_set )
		{
			$xml_ns = 'xmlns:og="' . esc_attr( $this->ns_uri ) . '" ';
		}

		$metadata = metadata();
		foreach ( $metadata as $key => $value )
		{
			if ( empty( $key ) || empty( $value ) )
			{
				continue;
			}

			echo '<meta ' . $xml_ns . 'property="' . esc_attr( $key ) . '" content="' . esc_attr( $value ) . '" />' . "\n";
		} // END foreach
	}
}