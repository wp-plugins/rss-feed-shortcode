<?php
/**
 * Plugin Name: RSS Feed Shortcode
 * Description: A fast ajax shortcode that outputs the latest items out of an RSS feed of your choice.
 * Author: Carlo Manf
 * Author URI: http://carlomanf.id.au
 * Version: 1.0.0
 */

// Render the table for the feed output
function rss_feed_shortcode_render_the_table( $items ) {
	$content = '';

	$content .= '<table><thead><tr><th scope="col">Date</th><th scope="col">Item</th></tr></thead><tbody>';

	foreach ( $items as $item ) {
		$content .= '<tr>';
		$content .= '<td>' . $item[ 'date' ] . '</td>';
		$content .= '<td><a href="' . $item[ 'permalink' ] . '">' . $item[ 'title' ] . '</a></td>';
		$content .= '</tr>';
	}

	$content .= '</tbody></table>';

	return $content;
}

// Fetch the feed
add_action( 'wp_ajax_rss_feed_shortcode_get_feed', 'rss_feed_shortcode_get_feed' );
add_action( 'wp_ajax_nopriv_rss_feed_shortcode_get_feed', 'rss_feed_shortcode_get_feed' );
function rss_feed_shortcode_get_feed() {
	include_once( ABSPATH . WPINC . '/feed.php' );
	$feed = fetch_feed( $_POST[ 'src' ] );

	if ( !is_wp_error( $feed ) ) {
		$max_items = $feed->get_item_quantity( 5 );
		$feed_items = $feed->get_items( 0, $max_items );

		if ( $max_items == 0 ) {
			echo '<p>No items in this feed!</p>';
			exit;
		}
		else {
			$feed_output = array();

			foreach ( $feed_items as $item ) {
				$the_item = array();
				$the_item[ 'permalink' ] = esc_url( $item->get_permalink() );
				$the_item[ 'date' ] = $item->get_date( 'j M' );
				$the_item[ 'title' ] = esc_html( $item->get_title() );

				array_push( $feed_output, $the_item );
			}

			echo rss_feed_shortcode_render_the_table( $feed_output );
			exit;
		}
	}
	else {
		echo '<p>The feed could not be fetched.</p>';
		exit;
	}
}

// Handle the feed shortcode
add_shortcode( 'rss', function( $atts ) {
	$atts = shortcode_atts( array( 'feed' => null ), $atts, 'rss' );

	$GLOBALS[ 'rss_feed_shortcode' ] = true;

	return '<div class="rss-feed"><p><a href="' . esc_attr( $atts[ 'feed' ] ) . '">Loading feed &hellip;</a></p></div>';
} );

// Register the feed ajax script just in case
add_action( 'init', function() {
	wp_register_script( 'rss-feed-shortcode', plugins_url( 'rss-feed-shortcode.js', __FILE__ ), array( 'jquery' ), '', true );
} );

// Only print it if the feed shortcode is present
add_action( 'wp_footer', function() {
	if ( isset( $GLOBALS[ 'rss_feed_shortcode' ] ) && true === $GLOBALS[ 'rss_feed_shortcode' ] ) {
		printf( '<script>var ajaxurl="%s";</script>', admin_url( 'admin-ajax.php' ) );
		wp_print_scripts( 'rss-feed-shortcode' );
	}
} );
