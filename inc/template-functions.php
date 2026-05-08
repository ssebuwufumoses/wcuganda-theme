<?php
/**
 * Theme presentation filters: body classes, archive titles, excerpt formatting.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add helpful classes to the body element.
 *
 * @param string[] $classes Existing body classes.
 * @return string[] Filtered body classes.
 */
function wcu_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	if ( is_active_sidebar( 'sidebar-primary' ) ) {
		$classes[] = 'has-sidebar';
	} else {
		$classes[] = 'no-sidebar';
	}

	if ( is_front_page() ) {
		$classes[] = 'is-home';
	}

	return $classes;
}
add_filter( 'body_class', 'wcu_body_classes' );

/**
 * Output a pingback URL header on single posts/pages where pings are open.
 *
 * @return void
 */
function wcu_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'wcu_pingback_header' );

/**
 * Override the default excerpt length on the front-end.
 *
 * @param int $length Default excerpt length in words.
 * @return int Filtered excerpt length.
 */
function wcu_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 28;
}
add_filter( 'excerpt_length', 'wcu_excerpt_length', 999 );

/**
 * Replace the default `[...]` more string with an ellipsis on the front-end.
 *
 * @param string $more Default more string.
 * @return string Filtered more string.
 */
function wcu_excerpt_more( $more ) {
	if ( is_admin() ) {
		return $more;
	}
	return '&hellip;';
}
add_filter( 'excerpt_more', 'wcu_excerpt_more' );

/**
 * Strip the "Category:" / "Tag:" / "Archives:" prefix from archive titles.
 *
 * @param string $title Archive title.
 * @return string Filtered archive title.
 */
function wcu_archive_title( $title ) {
	if ( is_category() ) {
		$title = single_cat_title( '', false );
	} elseif ( is_tag() ) {
		$title = single_tag_title( '', false );
	} elseif ( is_author() ) {
		$title = '<span class="archive-title__eyebrow">' . esc_html__( 'Author', 'wcuganda' ) . '</span> ' . get_the_author();
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	} elseif ( is_tax() ) {
		$title = single_term_title( '', false );
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'wcu_archive_title' );
