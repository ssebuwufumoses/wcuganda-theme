<?php
/**
 * Register meta boxes for all WCUganda CPTs.
 *
 * Each box is a `WCU_Meta_Box` instance. Hook into `init` so the class is
 * available even though the CPT registers earlier on the same hook.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Boot all meta box registrations.
 *
 * @return void
 */
function wcu_register_meta_boxes() {
	if ( ! is_admin() ) {
		return;
	}

	wcu_register_event_meta_box();
	wcu_register_member_meta_box();
	wcu_register_sponsor_meta_box();
	wcu_register_wordcamp_meta_box();
	wcu_register_chapter_meta_box();
}
add_action( 'init', 'wcu_register_meta_boxes', 30 );

/**
 * Event details meta box.
 *
 * @return void
 */
function wcu_register_event_meta_box() {
	new WCU_Meta_Box(
		'wcu_event_details',
		__( 'Event Details', 'wcuganda' ),
		'wcu_event',
		array(
			'_wcu_event_date'       => array(
				'label'    => __( 'Event date', 'wcuganda' ),
				'type'     => 'date',
				'required' => true,
			),
			'_wcu_event_time'       => array(
				'label' => __( 'Start time', 'wcuganda' ),
				'type'  => 'time',
			),
			'_wcu_event_end_date'   => array(
				'label'       => __( 'End date', 'wcuganda' ),
				'type'        => 'date',
				'description' => __( 'Only set for multi-day events.', 'wcuganda' ),
			),
			'_wcu_event_venue'      => array(
				'label'       => __( 'Venue', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. Innovation Village, Kampala', 'wcuganda' ),
			),
			'_wcu_event_address'    => array(
				'label' => __( 'Address', 'wcuganda' ),
				'type'  => 'textarea',
			),
			'_wcu_event_rsvp_url'   => array(
				'label'       => __( 'RSVP URL', 'wcuganda' ),
				'type'        => 'url',
				'placeholder' => 'https://',
			),
			'_wcu_event_meetup_url' => array(
				'label'       => __( 'Meetup.com URL', 'wcuganda' ),
				'type'        => 'url',
				'placeholder' => 'https://www.meetup.com/...',
			),
			'_wcu_event_capacity'   => array(
				'label' => __( 'Capacity', 'wcuganda' ),
				'type'  => 'number',
			),
		)
	);
}

/**
 * Member profile meta box.
 *
 * @return void
 */
function wcu_register_member_meta_box() {
	new WCU_Meta_Box(
		'wcu_member_profile',
		__( 'Member Profile', 'wcuganda' ),
		'wcu_member',
		array(
			'_wcu_member_role_title'      => array(
				'label'       => __( 'Role / title', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. Frontend Developer', 'wcuganda' ),
			),
			'_wcu_member_pronouns'        => array(
				'label'       => __( 'Pronouns', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => __( 'he/him · she/her · they/them', 'wcuganda' ),
			),
			'_wcu_member_short_bio'       => array(
				'label'       => __( 'Short bio', 'wcuganda' ),
				'type'        => 'textarea',
				'description' => __( 'A 1-2 sentence summary shown in the profile hero card.', 'wcuganda' ),
			),
			'_wcu_member_company'         => array(
				'label'       => __( 'Company', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. Freelance, Automattic, etc.', 'wcuganda' ),
			),
			'_wcu_member_preferred_hosting' => array(
				'label'       => __( 'Preferred hosting', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. Pressable, self-hosted', 'wcuganda' ),
			),
			'_wcu_member_first_wp_version' => array(
				'label'       => __( 'First WordPress version used', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. 4.x, 5.5, 6.0', 'wcuganda' ),
			),
			'_wcu_member_birthday'        => array(
				'label'       => __( 'Birthday (no year)', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. August 6', 'wcuganda' ),
			),
			'_wcu_member_specialties'     => array(
				'label'       => __( 'Specialties', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. Project Manager, Designer, Site Builder', 'wcuganda' ),
				'description' => __( 'Comma-separated list of specialty pills shown on the profile.', 'wcuganda' ),
			),
			'_wcu_member_wporg_username'  => array(
				'label'       => __( 'WordPress.org username', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => 'your-wp-username',
				'description' => __( 'Username only (no @ or full URL). Powers the WordPress.org link and future badge / photo integrations.', 'wcuganda' ),
			),
			'_wcu_member_twitter'         => array(
				'label'       => __( 'Twitter / X URL', 'wcuganda' ),
				'type'        => 'url',
				'placeholder' => 'https://x.com/...',
			),
			'_wcu_member_github'          => array(
				'label'       => __( 'GitHub URL', 'wcuganda' ),
				'type'        => 'url',
				'placeholder' => 'https://github.com/...',
			),
			'_wcu_member_linkedin'        => array(
				'label'       => __( 'LinkedIn URL', 'wcuganda' ),
				'type'        => 'url',
				'placeholder' => 'https://www.linkedin.com/in/...',
			),
			'_wcu_member_website'         => array(
				'label'       => __( 'Personal website', 'wcuganda' ),
				'type'        => 'url',
				'placeholder' => 'https://',
			),
			'_wcu_member_speaker'         => array(
				'label'          => __( 'Speaker', 'wcuganda' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'This member has spoken at WordCamps or meetups.', 'wcuganda' ),
			),
			'_wcu_member_organizer'       => array(
				'label'          => __( 'Organizer', 'wcuganda' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'This member is part of the organizing team.', 'wcuganda' ),
			),
			'_wcu_member_featured'        => array(
				'label'          => __( 'Featured', 'wcuganda' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Show a verified-style star next to the name.', 'wcuganda' ),
			),
			'_wcu_member_available_for_hire' => array(
				'label'          => __( 'Available for hire', 'wcuganda' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Show "Available for hire" pill on the profile.', 'wcuganda' ),
			),
			'_wcu_member_open_sponsorship' => array(
				'label'          => __( 'Open to sponsorship', 'wcuganda' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Show "Open to sponsorship" pill on the profile.', 'wcuganda' ),
			),
			'_wcu_member_open_volunteering' => array(
				'label'          => __( 'Open to volunteering', 'wcuganda' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Show "Open to volunteering" pill on the profile.', 'wcuganda' ),
			),
		)
	);
}

/**
 * Sponsor details meta box.
 *
 * @return void
 */
function wcu_register_sponsor_meta_box() {
	new WCU_Meta_Box(
		'wcu_sponsor_details',
		__( 'Sponsor Details', 'wcuganda' ),
		'wcu_sponsor',
		array(
			'_wcu_sponsor_url'    => array(
				'label'       => __( 'Sponsor website', 'wcuganda' ),
				'type'        => 'url',
				'placeholder' => 'https://',
				'required'    => true,
			),
			'_wcu_sponsor_year'   => array(
				'label'       => __( 'Year sponsored', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => '2026',
			),
			'_wcu_sponsor_active' => array(
				'label'          => __( 'Currently active', 'wcuganda' ),
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Show this sponsor on the homepage and current sponsors lists.', 'wcuganda' ),
			),
		)
	);
}

/**
 * WordCamp details meta box.
 *
 * @return void
 */
function wcu_register_wordcamp_meta_box() {
	new WCU_Meta_Box(
		'wcu_wordcamp_details',
		__( 'WordCamp Details', 'wcuganda' ),
		'wcu_wordcamp',
		array(
			'_wcu_wc_year'           => array(
				'label'       => __( 'Year', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => '2026',
				'required'    => true,
			),
			'_wcu_wc_date'           => array(
				'label' => __( 'Start date', 'wcuganda' ),
				'type'  => 'date',
			),
			'_wcu_wc_end_date'       => array(
				'label' => __( 'End date', 'wcuganda' ),
				'type'  => 'date',
			),
			'_wcu_wc_venue'          => array(
				'label' => __( 'Venue', 'wcuganda' ),
				'type'  => 'text',
			),
			'_wcu_wc_address'        => array(
				'label' => __( 'Address', 'wcuganda' ),
				'type'  => 'textarea',
			),
			'_wcu_wc_ticket_url'     => array(
				'label'       => __( 'Ticket URL', 'wcuganda' ),
				'type'        => 'url',
				'placeholder' => 'https://',
			),
			'_wcu_wc_attendee_count' => array(
				'label' => __( 'Attendee count', 'wcuganda' ),
				'type'  => 'number',
			),
		)
	);
}

/**
 * Chapter details meta box.
 *
 * @return void
 */
function wcu_register_chapter_meta_box() {
	new WCU_Meta_Box(
		'wcu_chapter_details',
		__( 'Chapter Details', 'wcuganda' ),
		'wcu_chapter',
		array(
			'_wcu_chapter_city'           => array(
				'label'       => __( 'City', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. Kampala', 'wcuganda' ),
				'required'    => true,
			),
			'_wcu_chapter_meetup_url'     => array(
				'label'       => __( 'Meetup.com URL', 'wcuganda' ),
				'type'        => 'url',
				'placeholder' => 'https://www.meetup.com/...',
			),
			'_wcu_chapter_meetup_embed'   => array(
				'label'       => __( 'Meetup.com embed code', 'wcuganda' ),
				'type'        => 'textarea',
				'description' => __( 'Optional: paste the iframe embed code from Meetup.com.', 'wcuganda' ),
			),
			'_wcu_chapter_email'          => array(
				'label'       => __( 'Contact email', 'wcuganda' ),
				'type'        => 'email',
				'placeholder' => 'kampala@wcuganda.org',
			),
			'_wcu_chapter_organizers'     => array(
				'label'       => __( 'Organizers', 'wcuganda' ),
				'type'        => 'textarea',
				'description' => __( 'Comma-separated names of chapter organizers.', 'wcuganda' ),
			),
			'_wcu_chapter_founded'        => array(
				'label'       => __( 'Year founded', 'wcuganda' ),
				'type'        => 'text',
				'placeholder' => '2018',
			),
		)
	);
}
