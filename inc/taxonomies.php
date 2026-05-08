<?php
/**
 * Register WCUganda taxonomies and seed default terms.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all WCUganda taxonomies on init.
 *
 * @return void
 */
function wcu_register_taxonomies() {
	wcu_register_chapter_taxonomy();
	wcu_register_event_type_taxonomy();
	wcu_register_sponsor_tier_taxonomy();
	wcu_register_skills_taxonomy();
	wcu_register_member_role_taxonomy();
}
add_action( 'init', 'wcu_register_taxonomies', 5 );

/**
 * Build a labels array for a taxonomy from a singular and plural noun.
 *
 * @param string $singular Singular label.
 * @param string $plural   Plural label.
 * @return array
 */
function wcu_taxonomy_labels( $singular, $plural ) {
	return array(
		'name'              => $plural,
		'singular_name'     => $singular,
		'search_items'      => sprintf( __( 'Search %s', 'wcuganda' ), $plural ),    // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'all_items'         => sprintf( __( 'All %s', 'wcuganda' ), $plural ),       // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'parent_item'       => sprintf( __( 'Parent %s', 'wcuganda' ), $singular ),  // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'parent_item_colon' => sprintf( __( 'Parent %s:', 'wcuganda' ), $singular ), // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'edit_item'         => sprintf( __( 'Edit %s', 'wcuganda' ), $singular ),    // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'update_item'       => sprintf( __( 'Update %s', 'wcuganda' ), $singular ),  // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'add_new_item'      => sprintf( __( 'Add New %s', 'wcuganda' ), $singular ), // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'new_item_name'     => sprintf( __( 'New %s name', 'wcuganda' ), $singular ),// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		'menu_name'         => $plural,
		'not_found'         => sprintf( __( 'No %s found.', 'wcuganda' ), strtolower( $plural ) ), // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
	);
}

/**
 * Chapter taxonomy — labels chapters across event/member CPTs (Kampala, Entebbe, Jinja).
 *
 * @return void
 */
function wcu_register_chapter_taxonomy() {
	register_taxonomy(
		'wcu_chapter_tax',
		array( 'wcu_event', 'wcu_member' ),
		array(
			'labels'            => wcu_taxonomy_labels( __( 'Chapter', 'wcuganda' ), __( 'Chapters', 'wcuganda' ) ),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'chapter',
				'with_front' => false,
			),
		)
	);
}

/**
 * Event Type taxonomy — Meetup, WordCamp, Workshop, Hackathon.
 *
 * @return void
 */
function wcu_register_event_type_taxonomy() {
	register_taxonomy(
		'wcu_event_type',
		array( 'wcu_event' ),
		array(
			'labels'            => wcu_taxonomy_labels( __( 'Event type', 'wcuganda' ), __( 'Event types', 'wcuganda' ) ),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'event-type',
				'with_front' => false,
			),
		)
	);
}

/**
 * Sponsor Tier taxonomy — Bronze, Silver, Gold, Platinum.
 *
 * @return void
 */
function wcu_register_sponsor_tier_taxonomy() {
	register_taxonomy(
		'wcu_sponsor_tier',
		array( 'wcu_sponsor' ),
		array(
			'labels'            => wcu_taxonomy_labels( __( 'Tier', 'wcuganda' ), __( 'Tiers', 'wcuganda' ) ),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'tier',
				'with_front' => false,
			),
		)
	);
}

/**
 * Skills taxonomy — Frontend, Backend, Design, Content, DevOps.
 *
 * @return void
 */
function wcu_register_skills_taxonomy() {
	register_taxonomy(
		'wcu_skills',
		array( 'wcu_member' ),
		array(
			'labels'            => wcu_taxonomy_labels( __( 'Skill', 'wcuganda' ), __( 'Skills', 'wcuganda' ) ),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'skill',
				'with_front' => false,
			),
		)
	);
}

/**
 * Member Role taxonomy — Speaker, Organizer, Contributor, Attendee.
 *
 * @return void
 */
function wcu_register_member_role_taxonomy() {
	register_taxonomy(
		'wcu_member_role',
		array( 'wcu_member' ),
		array(
			'labels'            => wcu_taxonomy_labels( __( 'Role', 'wcuganda' ), __( 'Roles', 'wcuganda' ) ),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'role',
				'with_front' => false,
			),
		)
	);
}

/**
 * Seed default terms for each taxonomy on first registration.
 *
 * Runs once after taxonomies are registered. Idempotent — safe to run repeatedly.
 *
 * @return void
 */
function wcu_seed_default_terms() {
	if ( get_option( 'wcu_default_terms_seeded' ) ) {
		return;
	}

	$defaults = array(
		'wcu_chapter_tax'  => array( 'Kampala', 'Entebbe', 'Jinja' ),
		'wcu_event_type'   => array( 'Meetup', 'WordCamp', 'Workshop', 'Hackathon' ),
		'wcu_sponsor_tier' => array( 'Bronze', 'Silver', 'Gold', 'Platinum' ),
		'wcu_skills'       => array( 'Frontend', 'Backend', 'Design', 'Content', 'DevOps' ),
		'wcu_member_role'  => array( 'Speaker', 'Organizer', 'Contributor', 'Attendee' ),
	);

	foreach ( $defaults as $taxonomy => $terms ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		foreach ( $terms as $term ) {
			if ( ! term_exists( $term, $taxonomy ) ) {
				wp_insert_term( $term, $taxonomy );
			}
		}
	}

	update_option( 'wcu_default_terms_seeded', 1 );
}
add_action( 'init', 'wcu_seed_default_terms', 20 );
