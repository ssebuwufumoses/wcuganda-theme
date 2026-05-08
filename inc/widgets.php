<?php
/**
 * Register widget areas (sidebars).
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the theme's widget areas.
 *
 * @return void
 */
function wcu_register_widget_areas() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Primary Sidebar', 'wcuganda' ),
			'id'            => 'sidebar-primary',
			'description'   => esc_html__( 'Widgets here appear in the main sidebar on archive and single pages.', 'wcuganda' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	$footer_columns = array(
		'footer-1' => esc_html__( 'Footer Column 1', 'wcuganda' ),
		'footer-2' => esc_html__( 'Footer Column 2', 'wcuganda' ),
		'footer-3' => esc_html__( 'Footer Column 3', 'wcuganda' ),
		'footer-4' => esc_html__( 'Footer Column 4', 'wcuganda' ),
	);

	foreach ( $footer_columns as $id => $label ) {
		register_sidebar(
			array(
				'name'          => $label,
				'id'            => $id,
				'description'   => esc_html__( 'Widgets in this area appear in the corresponding footer column.', 'wcuganda' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="site-footer__heading">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'wcu_register_widget_areas' );
