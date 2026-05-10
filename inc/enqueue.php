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

	// Compiled theme stylesheet (declares @font-face rules for self-hosted Inter
	// and Source Serif 4 variable fonts).
	wp_enqueue_style(
		'wcu-style',
		$theme_uri . '/assets/css/style.css',
		array(),
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

	// Homepage stats counter (only loaded when the homepage is rendered).
	if ( is_front_page() ) {
		wp_enqueue_script(
			'wcu-stats-counter',
			$theme_uri . '/assets/js/src/stats-counter.js',
			array(),
			$version,
			true
		);
	}

	// Member profile Share button (Web Share API + clipboard fallback).
	if ( is_singular( 'wcu_member' ) ) {
		wp_enqueue_script(
			'wcu-member-share',
			$theme_uri . '/assets/js/src/member-share.js',
			array(),
			$version,
			true
		);
	}

	// Threaded comments support.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'wcu_enqueue_assets' );

/**
 * Preload the Latin variable font files used above the fold so the browser
 * can fetch them in parallel with the stylesheet instead of waiting for the
 * @font-face rules to be parsed.
 *
 * @return void
 */
function wcu_preload_fonts() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( WCU_THEME_URI . '/assets/fonts/inter-latin-wght-normal.woff2' )
	);
}
add_action( 'wp_head', 'wcu_preload_fonts', 2 );

/**
 * Inline the theme-toggle bootstrap inside <head> so the data-theme attribute
 * is set on <html> BEFORE paint. Prevents a flash of dark theme when the user
 * has chosen light (or vice versa).
 *
 * @return void
 */
function wcu_inline_theme_toggle() {
	$path = get_template_directory() . '/assets/js/src/theme-toggle.js';
	if ( ! file_exists( $path ) ) {
		return;
	}
	$js = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	if ( false === $js ) {
		return;
	}
	echo "<script>\n" . $js . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- file is theme-controlled.
}
add_action( 'wp_head', 'wcu_inline_theme_toggle', 1 );

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
