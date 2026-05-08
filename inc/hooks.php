<?php
/**
 * Theme-wide actions and filters.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme setup: features, supports, image sizes, menus, etc.
 *
 * @return void
 */
function wcu_theme_setup() {
	load_theme_textdomain( 'wcuganda', WCU_THEME_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	// Custom image sizes used across the site.
	add_image_size( 'wcu-card', 800, 450, true );
	add_image_size( 'wcu-hero', 1920, 800, true );
	add_image_size( 'wcu-avatar', 240, 240, true );
}
add_action( 'after_setup_theme', 'wcu_theme_setup' );

/**
 * Set the content width based on the theme's design.
 *
 * @return void
 */
function wcu_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'wcu_content_width', 800 );
}
add_action( 'after_setup_theme', 'wcu_content_width', 0 );

/**
 * Slim down `wp_head` output for performance and tidiness.
 *
 * @return void
 */
function wcu_clean_head() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
}
add_action( 'init', 'wcu_clean_head' );

/**
 * Disable WordPress emoji scripts and styles.
 *
 * @return void
 */
function wcu_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'wcu_disable_emojis' );

