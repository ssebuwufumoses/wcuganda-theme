<?php
/**
 * Theme Customizer registrations.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Customizer settings, sections, and controls.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function wcu_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'wcu_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'wcu_customize_partial_blogdescription',
			)
		);
	}

	// -------------------------------------------------------------------------
	// Social links section
	// -------------------------------------------------------------------------
	$wp_customize->add_section(
		'wcu_social',
		array(
			'title'       => esc_html__( 'Social Links', 'wcuganda' ),
			'description' => esc_html__( 'Full URLs to community profiles. Empty fields are hidden in the footer.', 'wcuganda' ),
			'priority'    => 80,
		)
	);

	$networks = array(
		'twitter'   => esc_html__( 'Twitter / X URL', 'wcuganda' ),
		'facebook'  => esc_html__( 'Facebook URL', 'wcuganda' ),
		'linkedin'  => esc_html__( 'LinkedIn URL', 'wcuganda' ),
		'instagram' => esc_html__( 'Instagram URL', 'wcuganda' ),
		'youtube'   => esc_html__( 'YouTube URL', 'wcuganda' ),
		'github'    => esc_html__( 'GitHub URL', 'wcuganda' ),
		'wordpress' => esc_html__( 'WordPress.org Profile URL', 'wcuganda' ),
	);

	foreach ( $networks as $key => $label ) {
		$setting = 'wcu_social_' . $key;

		$wp_customize->add_setting(
			$setting,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			$setting,
			array(
				'label'   => $label,
				'section' => 'wcu_social',
				'type'    => 'url',
			)
		);
	}
}
add_action( 'customize_register', 'wcu_customize_register' );

/**
 * Render the site title for the selective-refresh partial.
 *
 * @return void
 */
function wcu_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective-refresh partial.
 *
 * @return void
 */
function wcu_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Enqueue Customizer-preview live-update script.
 *
 * @return void
 */
function wcu_customize_preview_js() {
	wp_enqueue_script(
		'wcu-customizer',
		WCU_THEME_URI . '/assets/js/src/customizer.js',
		array( 'customize-preview' ),
		WCU_THEME_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'wcu_customize_preview_js' );
