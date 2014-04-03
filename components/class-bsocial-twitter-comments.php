<?php

class bSocial_Twitter_Comments
{

	public $option_name = 'bsocial_twitter_comments';

	public function __construct()
	{
		add_action( 'init', array( $this, 'init' ) );
	} // END __construct

	public function init()
	{
		// add tweets as filterable comment types
		add_filter( 'admin_comment_types_dropdown', array( $this, 'admin_comment_types_dropdown' ) );

		// cron
		add_action( 'wp_ajax_bsocial_twitter_comments', array( $this, 'cron_register' ) );
		add_action( 'ingest_twitter_comments', array( $this, 'ingest_twitter_comments' ) );
	} // END init

	public function ingest_twitter_comments()
	{
		global $wpdb;

		// get the ID of the last ingested tweet
		if ( strlen( get_option( $this->option_name ) ) )
		{
			$most_recent_tweet = get_option( $this->option_name );
		}
		else
		{
			add_option( $this->option_name, '', '', 'no' ); // add an empty option with the autoload disabled
		} // END else

		// prime HyperDB with a small write so we can make subsequent reads from the mast and avoid problems resulting from replication lag
		update_comment_meta( 1, $this->option_name, time() );

		$tz_offset = get_option( 'gmt_offset' ); // get the timezone offset

		// start the twitter search
		$home_url = preg_replace( '|https?://|', '', home_url() );
		bsocial()->twitter()->search()->search(
			array(
				'q' => urlencode( $home_url ),
				'count' => 100,
				'result_type' => 'recent',
				'since_id' => $most_recent_tweet,
			)
		);

		// run with it
		foreach ( (array) bsocial()->twitter()->search()->tweets() as $tweet )
		{

			if ( current_user_can( 'manage_options' ) )
			{
				print_r( $tweet );
			}

			if ( ! isset( $tweet->from_user->name ) )
			{
				continue; // give up if the username lookup failed
			}

			if ( bsocial()->comment_id_by_meta( $tweet->id_str, 'tweet_id' ) )
			{
				continue; // skip the tweet if we've already imported it
			}

			// map the URLs in the tweet to local posts
			// a tweet with links to multiple posts will only be added as a comment to the post with the highest post_id
			$found_post_ids = array();
			foreach ( (array) bsocial()->find_urls( $tweet->text ) as $url )
			{
				// resolve the URL to its final destination
				$url = bsocial()->follow_url( $url );

				// try to resolve the URL to a post id
				$post_id = url_to_postid( bsocial()->follow_url( $url ) ); // returns 0 if no match

				// some links to the blog don't resolve to post IDs, think of the home or tag pages.
				// hackish: those tweets get inserted against post id 0

				// make a list of the matching post IDs
				// check if the URL is part of this blog
				if ( (int) $wpdb->blogid == (int) bsocial()->url_to_blogid( $url ) ) // if we have the function to map links to blog IDs _and_ the link is for this blog
				{
					$found_post_ids[] = (int) $post_id;
				}
			} // END foreach

			// do any of the links point to this blog?
			if ( ! count( $found_post_ids ) )
			{
				continue; // no matching links
			}

			// get the highest found post id
			sort( $found_post_ids );
			$post_id = array_pop( $found_post_ids );

			// create the comment array
			$comment = array(
				'comment_post_ID' => $post_id,
				'comment_author' => $tweet->from_user->name,
				'comment_author_email' => $tweet->from_user->id_str . '@twitter.id',
//				'comment_author_url' => 'http://twitter.com/'. $tweet->from_user->screen_name .'/status/'. $tweet->id_str,
				'comment_author_url' => 'http://twitter.com/'. $tweet->from_user->screen_name,
				'comment_content' => $tweet->text,
				'comment_type' => 'tweet',
				'comment_date_gmt' => date( 'Y-m-d H:i:s', strtotime( $tweet->created_at ) ),
				'comment_date' => date( 'Y-m-d H:i:s', strtotime( $tweet->created_at ) + ( 3600 * $tz_offset ) ),
			);

			// insert the comment
			$comment_id = wp_insert_comment( $comment );
			add_comment_meta( $comment_id, 'tweet_id', $tweet->id_str ); // record the ID of the tweet
			add_comment_meta( $comment_id, 'tweet_rank', $tweet->from_user->followers_count ); // get the follower count of the twitter user as a means to sort tweets by rank of the user
			bsocial()->comment_id_by_meta_update_cache( $comment_id, $tweet->id_str, 'tweet_id' );

			// update the comment count
			if ( 0 < $post_id )
			{
				wp_update_comment_count( $post_id );
			}

			if ( get_option( 'comments_notify' ) )
			{
				wp_notify_postauthor( $comment_id, 'comment' ); // only works for comments, so we fudge
			}

			if ( current_user_can( 'manage_options' ) )
			{
				echo '<p>Comment inserted, <a href="' . get_edit_comment_link( $tweet ) . '">view/edit</a>.';
			}

			// possibly useful for determining rank of a tweet:
			// $tweet->metadata->recent_retweets & $tweet->from_user->followers_count
		} // END foreach

		// update the option with the last ingested tweet
		update_option( $this->option_name, bsocial()->twitter()->search()->api_response->max_id_str );

		// delete the dummy comment meta we used to prime HyperDB earlier
		delete_comment_meta( 1, $this->option_name );

	} // END ingest_twitter_comments

	public function cron_register()
	{
		if ( ! current_user_can( 'manage_options' ) )
		{
			wp_die( 'Trying something funny, are you?', 'Tisk tisk' );
		}

		echo '<pre>';

		if ( wp_next_scheduled( 'ingest_twitter_comments' ) )
		{
			wp_clear_scheduled_hook( 'ingest_twitter_comments' );
			echo '<p>Deleted previously schedule hook</p>';
		}

		wp_schedule_event( time() + 3600, 'hourly', 'ingest_twitter_comments' );
		echo '<p>Registered cron hook</p>';

		echo '<p>Ingesting comments now</p>';
		$this->ingest_twitter_comments();
		echo '<p>Done ingestion</p>';

		die;

	} // END cron_register

	public function admin_comment_types_dropdown( $types )
	{
		$types['tweet'] = 'Tweets';
		return $types;
	} // END admin_comment_types_dropdown
}//END class