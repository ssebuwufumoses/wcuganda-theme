<?php
/**
 * Register navigation menus.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the theme's navigation menu locations.
 *
 * @return void
 */
function wcu_register_menus() {
	register_nav_menus(
		array(
			'primary'  => esc_html__( 'Primary Menu', 'wcuganda' ),
			'footer-1' => esc_html__( 'Footer Menu — About', 'wcuganda' ),
			'footer-2' => esc_html__( 'Footer Menu — Get Involved', 'wcuganda' ),
			'footer-3' => esc_html__( 'Footer Menu — Resources', 'wcuganda' ),
			'social'   => esc_html__( 'Social Links', 'wcuganda' ),
			'legal'    => esc_html__( 'Legal Menu', 'wcuganda' ),
		)
	);
}
add_action( 'init', 'wcu_register_menus' );

/**
 * Render the primary navigation menu, with a fallback when no menu is assigned.
 *
 * @return void
 */
function wcu_render_primary_menu() {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'menu_id'        => 'wcu-primary-menu',
				'menu_class'     => 'wcu-nav__menu',
				'container'      => false,
				'fallback_cb'    => '__return_empty_string',
				'depth'          => 2,
			)
		);
		return;
	}

	echo '<ul id="wcu-primary-menu" class="wcu-nav__menu">';
	printf(
		'<li class="wcu-nav__item"><a class="wcu-nav__link" href="%1$s">%2$s</a></li>',
		esc_url( home_url( '/' ) ),
		esc_html__( 'Home', 'wcuganda' )
	);
	echo '</ul>';
}

/**
 * Render a footer menu with sensible defaults.
 *
 * @param string $location Menu location key.
 * @return void
 */
function wcu_render_footer_menu( $location ) {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}

	wp_nav_menu(
		array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => 'site-footer__menu',
			'depth'          => 1,
			'fallback_cb'    => '__return_empty_string',
		)
	);
}
