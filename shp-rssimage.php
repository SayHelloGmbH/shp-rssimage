<?php
/**
 * Plugin Name:     Add post thumbnail image to RSS feed
 * Plugin URI:      https://github.com/SayHelloGmbH/shp-rssimage
 * Description:     Adds the post thumbnail to the RSS feed using the XML tag specified in the Media RSS Specification.
 * Author:          Say Hello GmbH
 * Author URI:      https://sayhello.ch/?utm_source=wp-plugin&utm_medium=wp-plugin&utm_campaign=shp-rssimage
 * Text Domain:     shp-rssimage
 * Domain Path:     /languages
 * Version:         0.2.1
 */

add_action( 'init', 'shp_rssimage_init' );

function shp_rssimage_init() {
	// Add the media namespace to the rss namespace declaration block
	add_action( 'rss2_ns', 'shp_rssimage_namespace' );

	// Add a media element to the feed item if the post has a featured image
	add_action( 'rss2_item', 'shp_rssimage_extend' );
}

function shp_rssimage_namespace() {
	echo 'xmlns:media="http://search.yahoo.com/mrss/"';
}

function shp_rssimage_extend() {
	if ( ! has_post_thumbnail( get_the_ID() ) ) {
		return;
	}

	$image_id         = get_post_thumbnail_id( get_the_ID() );
	$image_attributes = wp_get_attachment_image_src(
		$image_id,
		apply_filters(
			'shp_rssimage__imagesize',
			'medium'
		)
	);
	$image_url        = $image_attributes[0];
	$image_dimensions = apply_filters(
		'shp_rssimage__imagedimensions',
		[
			'width'  => $image_attributes[1],
			'height' => $image_attributes[2],
		]
	);

	$image_mime_type = get_post_mime_type( $image_id );

	$out = sprintf(
		'%1$s<media:content url="%2$s" type="%3$s" medium="image" width="%4$s" height="%5$s" />',
		PHP_EOL,
		$image_url,
		$image_mime_type,
		$image_dimensions['width'],
		$image_dimensions['height']
	);

	echo apply_filters( 'shp_rssimage__out', $out );
}

function shp_rssimage_featured_to_rss( $content ) {
	if ( has_post_thumbnail( get_the_ID() ) ) {
		$content = get_the_post_thumbnail( get_the_ID(), 'medium', [ 'class' => 'webfeedsFeaturedVisual' ] ) . $content;
	}
	return $content;
}

add_filter( 'the_excerpt_rss', 'shp_rssimage_featured_to_rss' );
