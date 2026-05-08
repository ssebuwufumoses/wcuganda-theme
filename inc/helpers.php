<?php
/**
 * Reusable helper functions used throughout the theme.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the active theme version (for cache-busting and meta).
 *
 * @return string Theme version.
 */
function wcu_get_theme_version() {
	if ( defined( 'WCU_THEME_VERSION' ) ) {
		return WCU_THEME_VERSION;
	}
	$theme = wp_get_theme();
	return $theme->get( 'Version' );
}

/**
 * Inline SVG icon library. Add new icons by extending the lookup map.
 *
 * @param string $name Icon key.
 * @param array  $args {
 *     Optional rendering arguments.
 *
 *     @type int|string $width       Pixel width. Default 24.
 *     @type int|string $height      Pixel height. Default 24.
 *     @type string     $class       Extra CSS classes appended to the SVG.
 *     @type string     $title       Accessible title; if empty, aria-hidden is set.
 *     @type bool       $focusable   Whether the SVG is keyboard-focusable.
 * }
 * @return string SVG markup, or empty string if the icon is unknown.
 */
function wcu_get_svg_icon( $name, $args = array() ) {
	$icons = array(
		'menu' => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
		'close' => '<line x1="6" y1="6" x2="18" y2="18"/><line x1="6" y1="18" x2="18" y2="6"/>',
		'search' => '<circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
		'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
		'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
		'chevron-down' => '<polyline points="6 9 12 15 18 9"/>',
		'chevron-right' => '<polyline points="9 6 15 12 9 18"/>',
		'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
		'map-pin' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
		'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22 6 12 13 2 6"/>',
		'twitter' => '<path d="M22 4.01c-1 .49-1.98.689-3 .99-1.121-1.265-2.783-1.335-4.38-.737S11.977 6.323 12 8v1c-3.245.083-6.135-1.395-8-4 0 0-4.182 7.433 4 11-1.872 1.247-3.739 2.088-6 2 3.308 1.803 6.913 2.423 10.034 1.517C15.726 18.5 18.838 14.001 19 8c-.001-.243.029-.484.087-.72.835-.84 1.452-1.872 1.913-3.27z"/>',
		'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
		'linkedin' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
		'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>',
		'github' => '<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>',
		'youtube' => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>',
		'wordpress' => '<circle cx="12" cy="12" r="10"/><path d="M5 8.5l4.5 12L12 13l2.5 7.5 4.5-12"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	$defaults = array(
		'width'     => 24,
		'height'    => 24,
		'class'     => '',
		'title'     => '',
		'focusable' => false,
	);
	$args = wp_parse_args( $args, $defaults );

	$class      = trim( 'wcu-icon wcu-icon--' . sanitize_html_class( $name ) . ' ' . $args['class'] );
	$title_id   = '';
	$has_title  = '' !== trim( $args['title'] );
	$aria       = $has_title ? '' : 'aria-hidden="true"';
	$labelledby = '';

	if ( $has_title ) {
		static $wcu_icon_counter = 0;
		++$wcu_icon_counter;
		$title_id   = 'wcu-icon-' . (int) $wcu_icon_counter;
		$labelledby = sprintf( ' aria-labelledby="%s" role="img"', esc_attr( $title_id ) );
	}

	$svg = sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg" class="%1$s" width="%2$d" height="%3$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="%4$s"%5$s%6$s>',
		esc_attr( $class ),
		(int) $args['width'],
		(int) $args['height'],
		$args['focusable'] ? 'true' : 'false',
		$aria ? ' ' . $aria : '',
		$labelledby
	);

	if ( $has_title ) {
		$svg .= sprintf( '<title id="%1$s">%2$s</title>', esc_attr( $title_id ), esc_html( $args['title'] ) );
	}

	$svg .= $icons[ $name ];
	$svg .= '</svg>';

	return $svg;
}

/**
 * Echo an inline SVG icon.
 *
 * @param string $name Icon key.
 * @param array  $args Optional rendering arguments. See wcu_get_svg_icon().
 * @return void
 */
function wcu_svg_icon( $name, $args = array() ) {
	echo wcu_get_svg_icon( $name, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe SVG built from a fixed lookup table.
}

/**
 * Get a list of social profile URLs from theme mods.
 *
 * Customizer keys: wcu_social_twitter, wcu_social_facebook, wcu_social_linkedin,
 * wcu_social_instagram, wcu_social_github, wcu_social_youtube, wcu_social_wordpress.
 *
 * @return array<string,string> Map of network key to URL, in display order.
 */
function wcu_get_social_links() {
	$networks = array( 'twitter', 'facebook', 'linkedin', 'instagram', 'youtube', 'github', 'wordpress' );
	$links    = array();

	foreach ( $networks as $network ) {
		$url = get_theme_mod( 'wcu_social_' . $network, '' );
		if ( ! empty( $url ) ) {
			$links[ $network ] = esc_url( $url );
		}
	}

	return $links;
}

/**
 * Posted-on meta string for the current post (date + author).
 *
 * @return string Escaped meta HTML.
 */
function wcu_get_posted_meta() {
	$time_string = sprintf(
		'<time class="entry-date published updated" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf(
		/* translators: %s: post author display name. */
		esc_html__( 'By %s', 'wcuganda' ),
		'<a class="entry-author" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a>'
	);

	return '<span class="entry-date-wrap">' . $time_string . '</span> <span class="entry-author-wrap">' . $author . '</span>';
}

/**
 * Render the wp.org "Powered by WordPress" attribution used in the footer.
 *
 * @return string HTML string.
 */
function wcu_get_powered_by() {
	return sprintf(
		/* translators: 1: opening anchor tag, 2: closing anchor tag. */
		esc_html__( 'Proudly part of the global %1$sWordPress%2$s community.', 'wcuganda' ),
		'<a href="' . esc_url( 'https://wordpress.org/' ) . '" rel="nofollow noopener">',
		'</a>'
	);
}
