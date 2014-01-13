<?php
class bSocial_FeaturedComments
{
	// don't mess with these
	var $id_base = 'bsuite-fcomment';
	var $post_type_name = 'bsuite-fcomment';
	var $meta_key = 'bsuite-fcomment';
	var $tag_regex = '/\[\/?featured_?comment\]/i'; // just match the single tag to make it easy to remove
	var $wrapper_regex = '/\[featured_?comment\](.*?)\[\/?featured_?comment\]/i'; // match the content inside the tags
	var $enqueued_admin_js = FALSE;

	public function __construct()
	{

		add_action( 'init', array( $this, 'register_post_type' ), 11 );
		add_action( 'edit_comment', array( $this, 'edit_comment' ), 5 );
		add_action( 'delete_comment', array( $this, 'unfeature_comment' ) );
		add_action( 'wp_ajax_bsocial_feature_comment', array( $this, 'ajax' ) );

		add_filter( 'quicktags_settings', array( $this, 'quicktags_settings' ) );
		add_filter( 'comment_row_actions', array( $this, 'comment_row_actions' ), 10, 2 );

		add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_filter( 'post_class', array( $this, 'filter_post_class' ) );
		add_filter( 'get_comment_text', array( $this, 'filter_get_comment_text' ) );
		add_filter( 'the_author', array( $this, 'filter_the_author' ) );
		add_filter( 'the_author_posts_link', array( $this, 'filter_the_author_posts_link' ) );
		add_filter( 'post_type_link', array( $this, 'post_type_link' ), 11, 2 );

/*

@TODO:

+ add metaboxes to the custom post type that show the comment text and connect to both the post and comment

+ delete the comment meta when the post is deleted

+ add a metabox to the comment that connects to the featured comment post

+ add a metabox to the regular post that shows featured comments on the post

*/

	}//END __construct

	public function pre_get_posts( $query )
	{

		if ( bsocial()->options()->featuredcomments->add_to_waterfall && ! is_admin() && $query->is_main_query() )
		{
			$post_types = array_merge(
				(array) $query->query_vars['post_type'],
				array( is_singular() && isset( $query->queried_object->post_type ) ? $query->queried_object->post_type : 'post' ),
				array( $this->post_type_name )
			);

			$query->set( 'post_type', $post_types );
		}

		return $query;
	}//END pre_get_posts

	public function quicktags_settings( $settings )
	{
		switch( $settings['id'] )
		{
			case 'content':
				if ( get_current_screen()->id !== 'comment' )
				{
					return $settings;
				}
				// no break, so it continues to the case below
			case 'replycontent':
				$settings['buttons'] .= ',featuredcomment';
				// enqueue some JS to handle these buttons, yes?
				// similar to AddQuicktagsAndFunctions() in http://plugins.trac.wordpress.org/browser/vipers-video-quicktags/trunk/vipers-video-quicktags.php#L556
				// and now that I've read further, I don't think messing with the button list does anything.
				break;
		}//END switch

		return $settings;
	}//END quicktags_settings

	public function post_type_link( $permalink, $post )
	{
		if ( $post->post_type == $this->post_type_name && ( $comment_id = get_post_meta( $post->ID, $this->meta_key .'-comment_id', TRUE ) ) )
		{
			return get_comment_link( $comment_id );
		}

		return $permalink;
	}//END post_type_link

	public function filter_get_comment_text( $content )
	{
		if ( is_admin() )
		{
			return $content;
		}
		else
		{
			return preg_replace( $this->tag_regex, '', $content );
		}
	}//END filter_get_comment_text

	public function filter_post_class( $classes )
	{
		if ( get_post( get_the_ID() )->post_type == $this->post_type_name )
		{
			$classes[] = 'post';
		}

		return $classes;
	}//END filter_post_class

	public function filter_the_author( $author_name )
	{
		if ( get_the_ID() && get_post( get_the_ID() )->post_type == $this->post_type_name )
		{
			return get_comment_author( get_post_meta( get_the_ID(), $this->meta_key .'-comment_id', TRUE ) );
		}
		else
		{
			return $author_name;
		}
	}//END filter_the_author

	public function filter_the_author_posts_link( $url )
	{
		if ( get_the_ID() && get_post( get_the_ID() )->post_type == $this->post_type_name )
		{
			return '';
		}
		else
		{
			return $url;
		}
	}//END filter_the_author_posts_link

	public function get_featured_comment_text( $comment_id = FALSE )
	{
		remove_filter( 'get_comment_text', array( $this, 'filter_get_comment_text' ) );
		$text = $this->_get_featured_comment_text( get_comment_text( $comment_id ) );
		add_filter( 'get_comment_text', array( $this, 'filter_get_comment_text' ) );

		return $text[1];
	}//END get_featured_comment_text

	public function _get_featured_comment_text( $input )
	{
		preg_match( $this->wrapper_regex, $input, $text );

		return empty( $text[1] ) ? $input : $text[1];
	}//END _get_featured_comment_text

	public function edit_comment( $comment_id )
	{
		$comment = get_comment( $comment_id );

		// check if the featured tags exist in the comment content, permissions will be checked in the next function
		if (
			$featured = $this->_get_featured_comment_text( $comment->comment_content ) ||
			get_comment_meta( $comment->comment_ID, $this->meta_key .'-post_id', TRUE )
		)
		{
			$this->feature_comment( $comment_id );
		}

	}//END edit_comment

	public function unfeature_comment( $comment_id )
	{
		$comment = get_comment( $comment_id );

		// check user permissions
		// @todo: map a meta cap for this rather than extend the edit_post here
		if ( current_user_can( 'edit_post', $comment->comment_post_ID ) )
		{
			if ( $post_id = get_comment_meta( $comment->comment_ID, $this->meta_key .'-post_id', TRUE ) )
			{
				wp_delete_post( $post_id );
				delete_comment_meta( $comment->comment_ID, $this->meta_key .'-post_id' );
			}
		}
	}//END unfeature_comment

	public function feature_comment( $comment_id )
	{
		$comment = get_comment( $comment_id );

		// check user permissions
		// @todo: map a meta cap for this rather than extend the edit_post here
		if ( current_user_can( 'edit_post', $comment->comment_post_ID ) )
		{
			if ( $post_id = get_comment_meta( $comment->comment_ID, $this->meta_key .'-post_id', TRUE ) )
			{
				wp_update_post( (object) array( 'ID' => $post_id, 'post_content' => $featured ) ); // we have a post for this comment
			}
			else
			{
				$this->create_post( $comment_id ); // create a new post for this comment
			}
		}//END if
	}//END feature_comment

	public function create_post( $comment_id )
	{

		$comment = get_comment( $comment_id );
		$parent = get_post( $comment->comment_post_ID );
		$featured = $this->_get_featured_comment_text( $comment->comment_content );
		// @todo = wrap the content in a blockquote tag with the cite URL set to the comment permalink

		$post = array(
			'post_title' => $featured,
			'post_content' => $featured,
			'post_name' => sanitize_title( $featured ),
			'post_date' => bsocial()->options()->featuredcomments->use_commentdate ? $comment->comment_date : FALSE, // comment_date vs. the date the comment was featured
			'post_date_gmt' => bsocial()->options()->featuredcomments->use_commentdate ? $comment->comment_date_gmt : FALSE,
			'post_author' => $parent->post_author, // so permissions map the same as for the parent post
			'post_parent' => $parent->ID,
			'post_status' => $parent->post_status,
			'post_password' => $parent->post_password,
			'post_type' => $this->post_type_name,
		);
		$post_id = wp_insert_post( $post );

		// simple sanity check
		if ( ! is_numeric( $post_id ) )
		{
			return $post_id;
		}

		// save the meta
		update_post_meta( $post_id, $this->meta_key .'-comment_id', $comment->comment_ID );
		update_comment_meta( $comment->comment_ID, $this->meta_key .'-post_id', $post_id );

		// get all the terms on the parent post
		foreach ( (array) wp_get_object_terms( $parent->ID, get_object_taxonomies( $parent->post_type ) ) as $term )
		{
			$parent_terms[ $term->taxonomy ][] = $term->name;
		}

		// set those terms on the comment
		foreach ( (array) $parent_terms as $tax => $terms )
		{
			wp_set_object_terms( $post_id, $terms, $tax, FALSE );
		}

		return $post_id;
	}//END create_post

	public function comment_row_actions( $actions, $comment )
	{

		// check permissions against the parent post
		if ( ! current_user_can( 'edit_post', $comment->comment_post_ID ) )
		{
			return $actions;
		}

		// is this comment featured or not, what actions are available?
		if ( get_comment_meta( $comment->comment_ID, $this->meta_key .'-post_id', TRUE ) )
		{
			$actions['feature-comment hide-if-no-js'] = '<a class="feature-comment feature-comment-needs-refresh featured-comment" id="feature-comment-'. $comment->comment_ID .'" title="Unfeature" href="#">Unfeature</a>';
		}
		else
		{
			$actions['feature-comment hide-if-no-js'] = '<a class="feature-comment feature-comment-needs-refresh unfeatured-comment" id="feature-comment-'. $comment->comment_ID .'" title="Feature" href="#">Feature</a>';
		}

		// enqueue some JS once
		if ( ! $this->enqueued_admin_js )
		{
			add_action( 'admin_print_footer_scripts', array( $this, 'footer_js' ) );
			$this->enqueued_admin_js = TRUE;
		}

		return $actions;
	}//END comment_row_actions

	public function footer_js()
	{
		// this JS code originated by Mark Jaquith, http://coveredwebservices.com/ , for Gigaom, http://gigaom.com/

		?>
		<script type="text/javascript">
			var bsocial_featuredcomment_nonce = '<?php echo wp_create_nonce( 'bsocial-featuredcomment-save' ); ?>';
			function cwsFeatComLoad() {
				jQuery('#replyrow a.save').click(function() { cwsFeatComLoadLoop( jQuery('#comment_ID').val() ); });
				jQuery('a.feature-comment').removeClass('feature-comment-needs-refresh');
				jQuery('a.feature-comment').click( function(){
					var cmt = jQuery(this);
					var comment_id = cmt.attr('id').replace( /feature-comment-/, '' );
					var ajaxURL = '<?php echo js_escape( admin_url( 'admin-ajax.php' ) ); ?>';
					if ( cmt.hasClass('featured-comment') ) {
						cmt.fadeOut();
						jQuery.post(ajaxURL, {
							action:"bsocial_feature_comment",
							direction:"unfeature",
							comment_id: comment_id,
							cookie: encodeURIComponent(document.cookie),
							_bsocial_featuredcomment_nonce: bsocial_featuredcomment_nonce
						}, function(str){
							cmt.text("Feature").addClass('unfeatured-comment').removeClass('featured-comment').fadeIn();
						});
					} else {
						cmt.fadeOut();
						jQuery.post(ajaxURL, {
							action:"bsocial_feature_comment",
							direction:"feature",
							comment_id: comment_id,
							cookie: encodeURIComponent(document.cookie),
							_bsocial_featuredcomment_nonce: bsocial_featuredcomment_nonce
						}, function(str){
							cmt.text("Unfeature").addClass('featured-comment').removeClass('unfeatured-comment').fadeIn();
						});
					}
					return false;
				});
			}
			function cwsFeatComLoadLoop(comment_id) {
				if ( jQuery( '#comment-' + comment_id + ' a.feature-comment-needs-refresh').text() ) {
					cwsFeatComLoad();
				} else {
					setTimeout("cwsFeatComLoadLoop(" + comment_id + ")", 100);
				}
			}

		jQuery( document ).ready( function(){
			cwsFeatComLoad();
		});
		</script>
		<?php
	}//END footer_js


	public function register_post_type()
	{

		// @TODO: should get list of post types that support comments, then get the list of taxonomies they support
		$taxonomies = get_taxonomies( array( 'public' => TRUE ) );

		register_post_type( $this->post_type_name,
			array(
				'labels' => array(
					'name' => 'Featured Comments',
					'singular_name' => 'Featured Comment',
				),
				'supports' => array(
					'title',
					'author',
				),
				'register_meta_box_cb' => array( $this, 'register_metaboxes' ),
				'public' => TRUE,
				'show_in_menu' => 'edit-comments.php',
				'has_archive' => 'talkbox',
				'rewrite' => array(
					'slug' => 'talkbox',
					'with_front' => FALSE,
				),
				'taxonomies' => $taxonomies,
			)
		);
	}//END register_post_type

	public function metabox( $post )
	{
	}//END metabox

	public function register_metaboxes()
	{
		// add metaboxes
		add_meta_box( $id_base, 'Featured Comment', array( $this, 'metabox' ), $this->post_type_name, 'normal', 'high' );
	}//END register_metaboxes

	public function ajax()
	{
		$comment_id = intval( $_REQUEST['comment_id'] );

		if ( ! current_user_can( 'moderate_comments' ) )
		{
			return FALSE;
		}

		if ( ! check_ajax_referer( 'bsocial-featuredcomment-save', '_bsocial_featuredcomment_nonce' ) )
		{
			return;
		}//end if

		if ( get_comment( $comment_id ) )
		{
			if ( 'feature' == $_POST['direction'] )
			{
				$this->feature_comment( $comment_id );
			}
			else
			{
				$this->unfeature_comment( $comment_id );
			}
		}//END if

		die;
	}//END ajax
}//end bSuite_FeaturedComments class