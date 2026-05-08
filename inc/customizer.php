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

	// -------------------------------------------------------------------------
	// Homepage panel: hero, stats, WordCamp banner, map, newsletter, footer
	// -------------------------------------------------------------------------
	$wp_customize->add_panel(
		'wcu_homepage',
		array(
			'title'       => esc_html__( 'Homepage', 'wcuganda' ),
			'description' => esc_html__( 'Content shown on the homepage sections built by the theme.', 'wcuganda' ),
			'priority'    => 30,
		)
	);

	wcu_register_hero_section( $wp_customize );
	wcu_register_stats_section( $wp_customize );
	wcu_register_wordcamp_section( $wp_customize );
	wcu_register_newsletter_section( $wp_customize );
	wcu_register_map_section( $wp_customize );
	wcu_register_footer_section( $wp_customize );
}
add_action( 'customize_register', 'wcu_customize_register' );

/**
 * Hero section: heading, subheading, two CTAs, optional background image.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function wcu_register_hero_section( $wp_customize ) {
	$wp_customize->add_section(
		'wcu_hero',
		array(
			'title' => esc_html__( 'Hero', 'wcuganda' ),
			'panel' => 'wcu_homepage',
		)
	);

	$controls = array(
		'wcu_hero_eyebrow'      => array(
			'label'   => esc_html__( 'Eyebrow text', 'wcuganda' ),
			'default' => esc_html__( 'WordPress Community Uganda', 'wcuganda' ),
			'type'    => 'text',
			'sanitize' => 'sanitize_text_field',
		),
		'wcu_hero_heading'      => array(
			'label'   => esc_html__( 'Heading', 'wcuganda' ),
			'default' => esc_html__( 'Build the open web together.', 'wcuganda' ),
			'type'    => 'text',
			'sanitize' => 'sanitize_text_field',
		),
		'wcu_hero_subheading'   => array(
			'label'   => esc_html__( 'Subheading', 'wcuganda' ),
			'default' => esc_html__( 'Meetups, WordCamps, and contributor days across Uganda — powered by makers like you.', 'wcuganda' ),
			'type'    => 'textarea',
			'sanitize' => 'wp_kses_post',
		),
		'wcu_hero_cta_primary_text' => array(
			'label'   => esc_html__( 'Primary CTA label', 'wcuganda' ),
			'default' => esc_html__( 'Join the community', 'wcuganda' ),
			'type'    => 'text',
			'sanitize' => 'sanitize_text_field',
		),
		'wcu_hero_cta_primary_url'  => array(
			'label'   => esc_html__( 'Primary CTA URL', 'wcuganda' ),
			'default' => '/get-involved/',
			'type'    => 'url',
			'sanitize' => 'esc_url_raw',
		),
		'wcu_hero_cta_secondary_text' => array(
			'label'   => esc_html__( 'Secondary CTA label', 'wcuganda' ),
			'default' => esc_html__( 'Upcoming events', 'wcuganda' ),
			'type'    => 'text',
			'sanitize' => 'sanitize_text_field',
		),
		'wcu_hero_cta_secondary_url'  => array(
			'label'   => esc_html__( 'Secondary CTA URL', 'wcuganda' ),
			'default' => '/events/',
			'type'    => 'url',
			'sanitize' => 'esc_url_raw',
		),
	);

	wcu_add_customizer_controls( $wp_customize, 'wcu_hero', $controls );

	$wp_customize->add_setting(
		'wcu_hero_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'wcu_hero_image',
			array(
				'label'     => esc_html__( 'Background image', 'wcuganda' ),
				'section'   => 'wcu_hero',
				'mime_type' => 'image',
			)
		)
	);
}

/**
 * Stats section: 4 numeric stat blocks with labels.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function wcu_register_stats_section( $wp_customize ) {
	$wp_customize->add_section(
		'wcu_stats',
		array(
			'title' => esc_html__( 'Stats', 'wcuganda' ),
			'panel' => 'wcu_homepage',
		)
	);

	$defaults = array(
		1 => array( '500',  __( 'Community members', 'wcuganda' ) ),
		2 => array( '60',   __( 'Events hosted', 'wcuganda' ) ),
		3 => array( '3',    __( 'Active chapters', 'wcuganda' ) ),
		4 => array( '8',    __( 'Years building', 'wcuganda' ) ),
	);

	foreach ( $defaults as $i => $pair ) {
		wcu_add_customizer_controls(
			$wp_customize,
			'wcu_stats',
			array(
				"wcu_stat_{$i}_value" => array(
					'label'    => sprintf( esc_html__( 'Stat %d — number', 'wcuganda' ), $i ),
					'default'  => $pair[0],
					'type'     => 'text',
					'sanitize' => 'sanitize_text_field',
				),
				"wcu_stat_{$i}_label" => array(
					'label'    => sprintf( esc_html__( 'Stat %d — label', 'wcuganda' ), $i ),
					'default'  => $pair[1],
					'type'     => 'text',
					'sanitize' => 'sanitize_text_field',
				),
			)
		);
	}
}

/**
 * WordCamp banner section: heading, date, location, ticket URL.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function wcu_register_wordcamp_section( $wp_customize ) {
	$wp_customize->add_section(
		'wcu_wordcamp_banner',
		array(
			'title'       => esc_html__( 'WordCamp Banner', 'wcuganda' ),
			'description' => esc_html__( 'Promote the next WordCamp. Leave the title blank to hide the banner.', 'wcuganda' ),
			'panel'       => 'wcu_homepage',
		)
	);

	wcu_add_customizer_controls(
		$wp_customize,
		'wcu_wordcamp_banner',
		array(
			'wcu_wc_eyebrow'  => array(
				'label'   => esc_html__( 'Eyebrow', 'wcuganda' ),
				'default' => esc_html__( 'Save the date', 'wcuganda' ),
				'type'    => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'wcu_wc_title'    => array(
				'label'   => esc_html__( 'WordCamp title', 'wcuganda' ),
				'default' => esc_html__( 'WordCamp Kampala 2026', 'wcuganda' ),
				'type'    => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'wcu_wc_date'     => array(
				'label'   => esc_html__( 'Date (display string)', 'wcuganda' ),
				'default' => esc_html__( 'Saturday, 14 November 2026', 'wcuganda' ),
				'type'    => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'wcu_wc_location' => array(
				'label'   => esc_html__( 'Location', 'wcuganda' ),
				'default' => esc_html__( 'Kampala, Uganda', 'wcuganda' ),
				'type'    => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'wcu_wc_cta_text' => array(
				'label'   => esc_html__( 'Button label', 'wcuganda' ),
				'default' => esc_html__( 'Get your ticket', 'wcuganda' ),
				'type'    => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'wcu_wc_cta_url'  => array(
				'label'   => esc_html__( 'Button URL', 'wcuganda' ),
				'default' => '#',
				'type'    => 'url',
				'sanitize' => 'esc_url_raw',
			),
		)
	);
}

/**
 * Newsletter section: heading + button label. Form action handled by MailPoet
 * shortcode if active, otherwise a basic form posting to a configurable URL.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function wcu_register_newsletter_section( $wp_customize ) {
	$wp_customize->add_section(
		'wcu_newsletter',
		array(
			'title' => esc_html__( 'Newsletter', 'wcuganda' ),
			'panel' => 'wcu_homepage',
		)
	);

	wcu_add_customizer_controls(
		$wp_customize,
		'wcu_newsletter',
		array(
			'wcu_newsletter_heading'    => array(
				'label'    => esc_html__( 'Heading', 'wcuganda' ),
				'default'  => esc_html__( 'Stay in the loop', 'wcuganda' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'wcu_newsletter_subheading' => array(
				'label'    => esc_html__( 'Subheading', 'wcuganda' ),
				'default'  => esc_html__( 'Get monthly highlights, upcoming events, and contributor opportunities in your inbox.', 'wcuganda' ),
				'type'     => 'textarea',
				'sanitize' => 'wp_kses_post',
			),
			'wcu_newsletter_button'     => array(
				'label'    => esc_html__( 'Button label', 'wcuganda' ),
				'default'  => esc_html__( 'Subscribe', 'wcuganda' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'wcu_newsletter_shortcode'  => array(
				'label'       => esc_html__( 'MailPoet shortcode (optional)', 'wcuganda' ),
				'description' => esc_html__( 'Paste the MailPoet form shortcode here, e.g. [mailpoet_form id="1"]. If left blank a fallback form is shown.', 'wcuganda' ),
				'default'     => '',
				'type'        => 'text',
				'sanitize'    => 'sanitize_text_field',
			),
		)
	);
}

/**
 * Map section: Google Maps embed URL.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function wcu_register_map_section( $wp_customize ) {
	$wp_customize->add_section(
		'wcu_map',
		array(
			'title'       => esc_html__( 'Map', 'wcuganda' ),
			'description' => esc_html__( 'Paste a Google Maps embed URL (the src attribute of the iframe). Leave blank to hide.', 'wcuganda' ),
			'panel'       => 'wcu_homepage',
		)
	);

	wcu_add_customizer_controls(
		$wp_customize,
		'wcu_map',
		array(
			'wcu_map_heading' => array(
				'label'    => esc_html__( 'Heading', 'wcuganda' ),
				'default'  => esc_html__( 'Find a chapter near you', 'wcuganda' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'wcu_map_embed'   => array(
				'label'    => esc_html__( 'Google Maps embed URL', 'wcuganda' ),
				'default'  => '',
				'type'     => 'url',
				'sanitize' => 'esc_url_raw',
			),
		)
	);
}

/**
 * Footer section: about text + Join CTA URL used by the header button.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function wcu_register_footer_section( $wp_customize ) {
	$wp_customize->add_section(
		'wcu_footer',
		array(
			'title' => esc_html__( 'Footer / Header', 'wcuganda' ),
			'panel' => 'wcu_homepage',
		)
	);

	wcu_add_customizer_controls(
		$wp_customize,
		'wcu_footer',
		array(
			'wcu_join_url'    => array(
				'label'       => esc_html__( 'Header "Join Us" button URL', 'wcuganda' ),
				'description' => esc_html__( 'Leave blank to hide the header CTA button.', 'wcuganda' ),
				'default'     => '',
				'type'        => 'url',
				'sanitize'    => 'esc_url_raw',
			),
			'wcu_footer_about' => array(
				'label'    => esc_html__( 'Footer about text', 'wcuganda' ),
				'default'  => esc_html__( 'WordPress Community Uganda is the local hub for organizers, contributors, and learners building the open web together.', 'wcuganda' ),
				'type'     => 'textarea',
				'sanitize' => 'wp_kses_post',
			),
		)
	);
}

/**
 * Add a batch of simple text/textarea/url Customizer controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @param string               $section_id   Section ID.
 * @param array                $controls     Map of setting key => control config.
 * @return void
 */
function wcu_add_customizer_controls( $wp_customize, $section_id, $controls ) {
	foreach ( $controls as $setting_id => $config ) {
		$type        = isset( $config['type'] ) ? $config['type'] : 'text';
		$sanitize_cb = isset( $config['sanitize'] ) ? $config['sanitize'] : 'sanitize_text_field';

		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => isset( $config['default'] ) ? $config['default'] : '',
				'sanitize_callback' => $sanitize_cb,
				'transport'         => 'refresh',
			)
		);

		$control_args = array(
			'label'   => isset( $config['label'] ) ? $config['label'] : $setting_id,
			'section' => $section_id,
			'type'    => $type,
		);

		if ( ! empty( $config['description'] ) ) {
			$control_args['description'] = $config['description'];
		}

		$wp_customize->add_control( $setting_id, $control_args );
	}
}

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
