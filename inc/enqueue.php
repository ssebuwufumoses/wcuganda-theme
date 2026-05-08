<?php
/**
 * Enqueue scripts and styles.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue front-end assets.
 *
 * @return void
 */
function wcu_enqueue_assets() {
	$theme_uri = WCU_THEME_URI;
	$version   = WCU_THEME_VERSION;

	// Google Fonts: Inter (headings) + Source Serif 4 (body).
	wp_enqueue_style(
		'wcu-google-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap',
		array(),
		null
	);

	// Compiled theme stylesheet.
	wp_enqueue_style(
		'wcu-style',
		$theme_uri . '/assets/css/style.css',
		array( 'wcu-google-fonts' ),
		$version
	);

	// Front-end navigation behavior.
	wp_enqueue_script(
		'wcu-navigation',
		$theme_uri . '/assets/js/src/navigation.js',
		array(),
		$version,
		true
	);

	// Threaded comments support.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'wcu_enqueue_assets' );

/**
 * Add preconnect resource hints for Google Fonts.
 *
 * @param array  $urls          Resource URLs already queued for the relation.
 * @param string $relation_type Relation type being requested.
 * @return array Filtered URLs.
 */
function wcu_resource_hints( $urls, $relation_type ) {
	if ( wp_style_is( 'wcu-google-fonts', 'enqueued' ) && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'wcu_resource_hints', 10, 2 );

/**
 * Enqueue editor (Gutenberg) styles.
 *
 * @return void
 */
function wcu_enqueue_editor_assets() {
	wp_enqueue_style(
		'wcu-editor-style',
		WCU_THEME_URI . '/assets/css/style.css',
		array(),
		WCU_THEME_VERSION
	);
}
add_action( 'enqueue_block_editor_assets', 'wcu_enqueue_editor_assets' );
