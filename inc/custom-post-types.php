<?php
/**
 * Register WCUganda custom post types.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all WCUganda CPTs on init.
 *
 * @return void
 */
function wcu_register_post_types() {
	wcu_register_event_cpt();
	wcu_register_member_cpt();
	wcu_register_sponsor_cpt();
	wcu_register_wordcamp_cpt();
	wcu_register_chapter_cpt();
}
add_action( 'init', 'wcu_register_post_types' );

/**
 * Build a labels array for a CPT from a singular and plural noun.
 *
 * @param string $singular Singular label, e.g. "Event".
 * @param string $plural   Plural label, e.g. "Events".
 * @return array
 */
function wcu_cpt_labels( $singular, $plural ) {
	return array(
		'name'                  => $plural,
		'singular_name'         => $singular,
		'menu_name'             => $plural,
		'name_admin_bar'        => $singular,
		'add_new'               => sprintf(
			/* translators: %s: singular post type label. */
			_x( 'Add New %s', 'post type', 'wcuganda' ),
			$singular
		),
		'add_new_item'          => sprintf( __( 'Add New %s', 'wcuganda' ), $singular ), // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'new_item'              => sprintf( __( 'New %s', 'wcuganda' ), $singular ),     // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'edit_item'             => sprintf( __( 'Edit %s', 'wcuganda' ), $singular ),    // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'view_item'             => sprintf( __( 'View %s', 'wcuganda' ), $singular ),    // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'all_items'             => sprintf( __( 'All %s', 'wcuganda' ), $plural ),       // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'search_items'          => sprintf( __( 'Search %s', 'wcuganda' ), $plural ),    // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'not_found'             => sprintf( __( 'No %s found.', 'wcuganda' ), strtolower( $plural ) ),                   // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'not_found_in_trash'    => sprintf( __( 'No %s found in Trash.', 'wcuganda' ), strtolower( $plural ) ),          // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'archives'              => sprintf( __( '%s archives', 'wcuganda' ), $singular ),                                // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'attributes'            => sprintf( __( '%s attributes', 'wcuganda' ), $singular ),                              // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'featured_image'        => __( 'Featured image', 'wcuganda' ),
		'set_featured_image'    => __( 'Set featured image', 'wcuganda' ),
		'remove_featured_image' => __( 'Remove featured image', 'wcuganda' ),
		'filter_items_list'     => sprintf( __( 'Filter %s list', 'wcuganda' ), strtolower( $plural ) ),                 // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'items_list_navigation' => sprintf( __( '%s list navigation', 'wcuganda' ), $plural ),                            // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'items_list'            => sprintf( __( '%s list', 'wcuganda' ), $plural ),                                       // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
	);
}

/**
 * Register the Event CPT.
 *
 * @return void
 */
function wcu_register_event_cpt() {
	register_post_type(
		'wcu_event',
		array(
			'labels'             => wcu_cpt_labels( __( 'Event', 'wcuganda' ), __( 'Events', 'wcuganda' ) ),
			'description'        => __( 'Community events: meetups, workshops, hackathons, and more.', 'wcuganda' ),
			'public'             => true,
			'has_archive'        => 'events',
			'rewrite'            => array(
				'slug'       => 'events',
				'with_front' => false,
			),
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-calendar-alt',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'show_in_rest'       => true,
			'show_in_nav_menus'  => true,
			'capability_type'    => 'post',
			'taxonomies'         => array( 'wcu_chapter_tax', 'wcu_event_type' ),
		)
	);
}

/**
 * Register the Member CPT.
 *
 * @return void
 */
function wcu_register_member_cpt() {
	register_post_type(
		'wcu_member',
		array(
			'labels'            => wcu_cpt_labels( __( 'Member', 'wcuganda' ), __( 'Members', 'wcuganda' ) ),
			'description'       => __( 'Community members: organizers, contributors, speakers, attendees.', 'wcuganda' ),
			'public'            => true,
			'has_archive'       => 'members',
			'rewrite'           => array(
				'slug'       => 'members',
				'with_front' => false,
			),
			'menu_position'     => 6,
			'menu_icon'         => 'dashicons-groups',
			'supports'          => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'show_in_rest'      => true,
			'show_in_nav_menus' => true,
			'capability_type'   => 'post',
			'taxonomies'        => array( 'wcu_chapter_tax', 'wcu_skills', 'wcu_member_role' ),
		)
	);
}

/**
 * Register the Sponsor CPT.
 *
 * @return void
 */
function wcu_register_sponsor_cpt() {
	register_post_type(
		'wcu_sponsor',
		array(
			'labels'            => wcu_cpt_labels( __( 'Sponsor', 'wcuganda' ), __( 'Sponsors', 'wcuganda' ) ),
			'description'       => __( 'Companies and organizations that sponsor the community.', 'wcuganda' ),
			'public'            => true,
			'has_archive'       => 'sponsors',
			'rewrite'           => array(
				'slug'       => 'sponsors',
				'with_front' => false,
			),
			'menu_position'     => 7,
			'menu_icon'         => 'dashicons-heart',
			'supports'          => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'show_in_rest'      => true,
			'show_in_nav_menus' => false,
			'capability_type'   => 'post',
			'taxonomies'        => array( 'wcu_sponsor_tier' ),
		)
	);
}

/**
 * Register the WordCamp CPT.
 *
 * @return void
 */
function wcu_register_wordcamp_cpt() {
	register_post_type(
		'wcu_wordcamp',
		array(
			'labels'            => wcu_cpt_labels( __( 'WordCamp', 'wcuganda' ), __( 'WordCamps', 'wcuganda' ) ),
			'description'       => __( 'Editions of WordCamp Uganda.', 'wcuganda' ),
			'public'            => true,
			'has_archive'       => 'wordcamps',
			'rewrite'           => array(
				'slug'       => 'wordcamps',
				'with_front' => false,
			),
			'menu_position'     => 8,
			'menu_icon'         => 'dashicons-megaphone',
			'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'show_in_rest'      => true,
			'show_in_nav_menus' => true,
			'capability_type'   => 'post',
		)
	);
}

/**
 * Register the Chapter CPT.
 *
 * @return void
 */
function wcu_register_chapter_cpt() {
	register_post_type(
		'wcu_chapter',
		array(
			'labels'            => wcu_cpt_labels( __( 'Chapter', 'wcuganda' ), __( 'Chapters', 'wcuganda' ) ),
			'description'       => __( 'Local chapters of WordPress Community Uganda (Kampala, Entebbe, Jinja, etc.).', 'wcuganda' ),
			'public'            => true,
			'has_archive'       => 'chapters',
			'rewrite'           => array(
				'slug'       => 'chapters',
				'with_front' => false,
			),
			'menu_position'     => 9,
			'menu_icon'         => 'dashicons-location-alt',
			'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'show_in_rest'      => true,
			'show_in_nav_menus' => true,
			'capability_type'   => 'post',
		)
	);
}
