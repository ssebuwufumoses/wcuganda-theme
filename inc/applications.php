<?php
/**
 * Applications backend.
 *
 * - Registers the private `wcu_application` CPT for storing form
 *   submissions (volunteer + speaker).
 * - Handles form POSTs via admin-post.php with nonce + honeypot +
 *   minimum-fill-time anti-spam checks.
 * - Sends an email notification to the site admin on each successful
 *   submission.
 * - Customizes the admin list table for triage.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------------
// Application CPT (private — only visible in admin to users who can edit posts)
// -----------------------------------------------------------------------------
function wcu_register_application_cpt() {
	register_post_type(
		'wcu_application',
		array(
			'labels'              => array(
				'name'          => __( 'Applications', 'wcuganda' ),
				'singular_name' => __( 'Application', 'wcuganda' ),
				'menu_name'     => __( 'Applications', 'wcuganda' ),
				'all_items'     => __( 'All Applications', 'wcuganda' ),
				'edit_item'     => __( 'Edit Application', 'wcuganda' ),
				'view_item'     => __( 'View Application', 'wcuganda' ),
				'search_items'  => __( 'Search Applications', 'wcuganda' ),
				'not_found'     => __( 'No applications yet.', 'wcuganda' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-email-alt',
			'supports'            => array( 'title', 'editor' ),
			'capability_type'     => 'post',
		)
	);
}
add_action( 'init', 'wcu_register_application_cpt' );

// -----------------------------------------------------------------------------
// Admin columns
// -----------------------------------------------------------------------------
add_filter(
	'manage_wcu_application_posts_columns',
	function ( $columns ) {
		return array(
			'cb'                => isset( $columns['cb'] ) ? $columns['cb'] : '',
			'title'             => __( 'Applicant', 'wcuganda' ),
			'wcu_app_type'      => __( 'Type', 'wcuganda' ),
			'wcu_app_email'     => __( 'Email', 'wcuganda' ),
			'wcu_app_chapter'   => __( 'Chapter', 'wcuganda' ),
			'date'              => __( 'Submitted', 'wcuganda' ),
		);
	}
);

add_action(
	'manage_wcu_application_posts_custom_column',
	function ( $column, $post_id ) {
		switch ( $column ) {
			case 'wcu_app_type':
				$type  = get_post_meta( $post_id, '_wcu_app_type', true );
				$label = ( 'speaker' === $type ) ? __( 'Speaker', 'wcuganda' ) : __( 'Volunteer', 'wcuganda' );
				echo esc_html( $label );
				break;
			case 'wcu_app_email':
				$email = get_post_meta( $post_id, '_wcu_app_email', true );
				if ( $email ) {
					printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
				} else {
					echo '—';
				}
				break;
			case 'wcu_app_chapter':
				$chapter = get_post_meta( $post_id, '_wcu_app_chapter', true );
				echo esc_html( $chapter ? $chapter : '—' );
				break;
		}
	},
	10,
	2
);

// -----------------------------------------------------------------------------
// Form handlers — admin-post.php endpoints
// -----------------------------------------------------------------------------
add_action( 'admin_post_wcu_application_submit', 'wcu_handle_application_submit' );
add_action( 'admin_post_nopriv_wcu_application_submit', 'wcu_handle_application_submit' );

/**
 * Handle a submission from either the volunteer or speaker form.
 *
 * @return void
 */
function wcu_handle_application_submit() {
	$type = isset( $_POST['wcu_app_type'] ) ? sanitize_key( wp_unslash( $_POST['wcu_app_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked below.
	if ( ! in_array( $type, array( 'volunteer', 'speaker' ), true ) ) {
		wcu_application_redirect( 'invalid', '' );
	}

	$nonce_action = 'wcu_app_' . $type;
	$nonce_field  = 'wcu_app_nonce';

	if ( ! isset( $_POST[ $nonce_field ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) ), $nonce_action ) ) {
		wcu_application_redirect( 'invalid', $type );
	}

	// Honeypot — must be empty.
	if ( ! empty( $_POST['wcu_app_website'] ) ) {
		wcu_application_redirect( 'success', $type );
	}

	// Time check — form must be at least 3 seconds old.
	$ts = isset( $_POST['wcu_app_ts'] ) ? (int) $_POST['wcu_app_ts'] : 0;
	if ( $ts && ( time() - $ts ) < 3 ) {
		wcu_application_redirect( 'invalid', $type );
	}

	// Required fields.
	$name  = isset( $_POST['wcu_app_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wcu_app_name'] ) ) : '';
	$email = isset( $_POST['wcu_app_email'] ) ? sanitize_email( wp_unslash( $_POST['wcu_app_email'] ) ) : '';

	if ( '' === $name || '' === $email || ! is_email( $email ) ) {
		wcu_application_redirect( 'invalid', $type );
	}

	// Optional fields.
	$chapter   = isset( $_POST['wcu_app_chapter'] ) ? sanitize_text_field( wp_unslash( $_POST['wcu_app_chapter'] ) ) : '';
	$bio       = isset( $_POST['wcu_app_bio'] ) ? wp_kses_post( wp_unslash( $_POST['wcu_app_bio'] ) ) : '';
	$message   = isset( $_POST['wcu_app_message'] ) ? wp_kses_post( wp_unslash( $_POST['wcu_app_message'] ) ) : '';
	$skills    = isset( $_POST['wcu_app_skills'] ) ? sanitize_text_field( wp_unslash( $_POST['wcu_app_skills'] ) ) : '';
	$twitter   = isset( $_POST['wcu_app_twitter'] ) ? esc_url_raw( wp_unslash( $_POST['wcu_app_twitter'] ) ) : '';
	$github    = isset( $_POST['wcu_app_github'] ) ? esc_url_raw( wp_unslash( $_POST['wcu_app_github'] ) ) : '';

	// Speaker-specific.
	$talk_title    = isset( $_POST['wcu_app_talk_title'] ) ? sanitize_text_field( wp_unslash( $_POST['wcu_app_talk_title'] ) ) : '';
	$talk_abstract = isset( $_POST['wcu_app_talk_abstract'] ) ? wp_kses_post( wp_unslash( $_POST['wcu_app_talk_abstract'] ) ) : '';

	// Build post.
	$title = sprintf( '%s — %s', $name, ( 'speaker' === $type ) ? __( 'Speaker', 'wcuganda' ) : __( 'Volunteer', 'wcuganda' ) );

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'wcu_application',
			'post_status' => 'publish',
			'post_title'  => $title,
			'post_content' => $message,
		),
		true
	);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		wcu_application_redirect( 'invalid', $type );
	}

	update_post_meta( $post_id, '_wcu_app_type', $type );
	update_post_meta( $post_id, '_wcu_app_name', $name );
	update_post_meta( $post_id, '_wcu_app_email', $email );
	update_post_meta( $post_id, '_wcu_app_chapter', $chapter );
	update_post_meta( $post_id, '_wcu_app_bio', $bio );
	update_post_meta( $post_id, '_wcu_app_skills', $skills );
	update_post_meta( $post_id, '_wcu_app_twitter', $twitter );
	update_post_meta( $post_id, '_wcu_app_github', $github );

	if ( 'speaker' === $type ) {
		update_post_meta( $post_id, '_wcu_app_talk_title', $talk_title );
		update_post_meta( $post_id, '_wcu_app_talk_abstract', $talk_abstract );
	}

	// Notify admin.
	wcu_send_application_notification( $post_id, $type );

	wcu_application_redirect( 'success', $type );
}

/**
 * Send an email notification when an application is submitted.
 *
 * @param int    $post_id Post ID.
 * @param string $type    'volunteer' or 'speaker'.
 * @return void
 */
function wcu_send_application_notification( $post_id, $type ) {
	$to      = get_option( 'admin_email' );
	$subject = sprintf(
		/* translators: 1: type, 2: site name. */
		__( '[%1$s] New %2$s application', 'wcuganda' ),
		get_bloginfo( 'name' ),
		( 'speaker' === $type ) ? __( 'speaker', 'wcuganda' ) : __( 'volunteer', 'wcuganda' )
	);

	$lines   = array();
	$lines[] = sprintf( __( 'Name: %s', 'wcuganda' ), get_post_meta( $post_id, '_wcu_app_name', true ) );
	$lines[] = sprintf( __( 'Email: %s', 'wcuganda' ), get_post_meta( $post_id, '_wcu_app_email', true ) );
	$lines[] = sprintf( __( 'Chapter: %s', 'wcuganda' ), get_post_meta( $post_id, '_wcu_app_chapter', true ) );

	if ( 'speaker' === $type ) {
		$lines[] = '';
		$lines[] = sprintf( __( 'Talk title: %s', 'wcuganda' ), get_post_meta( $post_id, '_wcu_app_talk_title', true ) );
		$lines[] = sprintf( __( 'Abstract: %s', 'wcuganda' ), wp_strip_all_tags( get_post_meta( $post_id, '_wcu_app_talk_abstract', true ) ) );
	}

	$skills = get_post_meta( $post_id, '_wcu_app_skills', true );
	if ( $skills ) {
		$lines[] = sprintf( __( 'Skills: %s', 'wcuganda' ), $skills );
	}

	$bio = get_post_meta( $post_id, '_wcu_app_bio', true );
	if ( $bio ) {
		$lines[] = '';
		$lines[] = __( 'Bio:', 'wcuganda' );
		$lines[] = wp_strip_all_tags( $bio );
	}

	$lines[] = '';
	$lines[] = __( 'Message:', 'wcuganda' );
	$lines[] = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );

	$lines[] = '';
	$lines[] = sprintf(
		/* translators: %s: admin URL to the application post. */
		__( 'Review in admin: %s', 'wcuganda' ),
		get_edit_post_link( $post_id, 'raw' )
	);

	$body = implode( "\n", $lines );

	wp_mail( $to, $subject, $body );
}

/**
 * Redirect back to the originating page with status.
 *
 * @param string $status 'success' | 'invalid'.
 * @param string $type   Form type for context.
 * @return void
 */
function wcu_application_redirect( $status, $type ) {
	$referer = wp_get_referer();
	if ( ! $referer ) {
		$referer = home_url( '/' );
	}
	$url = add_query_arg(
		array(
			'wcu_app'      => $status,
			'wcu_app_type' => $type,
		),
		$referer
	);
	wp_safe_redirect( $url . ( '' !== $type ? '#wcu-app-form' : '' ) );
	exit;
}
