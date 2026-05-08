<?php
/**
 * Customize the WP admin list-table columns for each WCUganda CPT, so
 * organizers see useful meta at a glance without opening each post.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------------
// Events
// -----------------------------------------------------------------------------
add_filter( 'manage_wcu_event_posts_columns', 'wcu_event_columns' );
add_action( 'manage_wcu_event_posts_custom_column', 'wcu_event_column_content', 10, 2 );
add_filter( 'manage_edit-wcu_event_sortable_columns', 'wcu_event_sortable_columns' );

/**
 * Event list columns.
 *
 * @param array $columns Default columns.
 * @return array
 */
function wcu_event_columns( $columns ) {
	return array(
		'cb'                  => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'               => __( 'Title', 'wcuganda' ),
		'wcu_event_date'      => __( 'Event date', 'wcuganda' ),
		'wcu_event_venue'     => __( 'Venue', 'wcuganda' ),
		'taxonomy-wcu_event_type'   => __( 'Type', 'wcuganda' ),
		'taxonomy-wcu_chapter_tax'  => __( 'Chapter', 'wcuganda' ),
		'wcu_event_rsvp'      => __( 'RSVP', 'wcuganda' ),
		'date'                => __( 'Published', 'wcuganda' ),
	);
}

/**
 * Event column cell renderer.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function wcu_event_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'wcu_event_date':
			$date = get_post_meta( $post_id, '_wcu_event_date', true );
			if ( ! empty( $date ) ) {
				$ts = strtotime( $date );
				echo $ts ? esc_html( wp_date( 'j M Y', $ts ) ) : esc_html( $date );
			} else {
				echo '—';
			}
			break;

		case 'wcu_event_venue':
			$venue = get_post_meta( $post_id, '_wcu_event_venue', true );
			echo esc_html( $venue ? $venue : '—' );
			break;

		case 'wcu_event_rsvp':
			$url = get_post_meta( $post_id, '_wcu_event_rsvp_url', true );
			if ( ! empty( $url ) ) {
				printf(
					'<a href="%1$s" target="_blank" rel="noopener">%2$s</a>',
					esc_url( $url ),
					esc_html__( 'Open', 'wcuganda' )
				);
			} else {
				echo '—';
			}
			break;
	}
}

/**
 * Make the event-date column sortable.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function wcu_event_sortable_columns( $columns ) {
	$columns['wcu_event_date'] = 'wcu_event_date';
	return $columns;
}

add_action( 'pre_get_posts', 'wcu_event_orderby' );

/**
 * Order events by event date when the column is clicked.
 *
 * @param WP_Query $query Current query.
 * @return void
 */
function wcu_event_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'wcu_event_date' !== $query->get( 'orderby' ) ) {
		return;
	}
	$query->set( 'meta_key', '_wcu_event_date' );
	$query->set( 'orderby', 'meta_value' );
}

// -----------------------------------------------------------------------------
// Members
// -----------------------------------------------------------------------------
add_filter( 'manage_wcu_member_posts_columns', 'wcu_member_columns' );
add_action( 'manage_wcu_member_posts_custom_column', 'wcu_member_column_content', 10, 2 );

/**
 * Member list columns.
 *
 * @param array $columns Default columns.
 * @return array
 */
function wcu_member_columns( $columns ) {
	return array(
		'cb'                            => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'wcu_member_avatar'             => __( 'Photo', 'wcuganda' ),
		'title'                         => __( 'Name', 'wcuganda' ),
		'wcu_member_role_title'         => __( 'Role', 'wcuganda' ),
		'taxonomy-wcu_chapter_tax'      => __( 'Chapter', 'wcuganda' ),
		'taxonomy-wcu_skills'           => __( 'Skills', 'wcuganda' ),
		'taxonomy-wcu_member_role'      => __( 'Member roles', 'wcuganda' ),
		'wcu_member_flags'              => __( 'Flags', 'wcuganda' ),
		'date'                          => __( 'Published', 'wcuganda' ),
	);
}

/**
 * Member column cell renderer.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function wcu_member_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'wcu_member_avatar':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 48, 48 ), array( 'style' => 'border-radius:50%;' ) );
			} else {
				echo '—';
			}
			break;

		case 'wcu_member_role_title':
			$role = get_post_meta( $post_id, '_wcu_member_role_title', true );
			echo esc_html( $role ? $role : '—' );
			break;

		case 'wcu_member_flags':
			$flags = array();
			if ( '1' === get_post_meta( $post_id, '_wcu_member_speaker', true ) ) {
				$flags[] = esc_html__( 'Speaker', 'wcuganda' );
			}
			if ( '1' === get_post_meta( $post_id, '_wcu_member_organizer', true ) ) {
				$flags[] = esc_html__( 'Organizer', 'wcuganda' );
			}
			echo $flags ? esc_html( implode( ', ', $flags ) ) : '—';
			break;
	}
}

// -----------------------------------------------------------------------------
// Sponsors
// -----------------------------------------------------------------------------
add_filter( 'manage_wcu_sponsor_posts_columns', 'wcu_sponsor_columns' );
add_action( 'manage_wcu_sponsor_posts_custom_column', 'wcu_sponsor_column_content', 10, 2 );

/**
 * Sponsor list columns.
 *
 * @param array $columns Default columns.
 * @return array
 */
function wcu_sponsor_columns( $columns ) {
	return array(
		'cb'                              => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'wcu_sponsor_logo'                => __( 'Logo', 'wcuganda' ),
		'title'                           => __( 'Sponsor', 'wcuganda' ),
		'taxonomy-wcu_sponsor_tier'       => __( 'Tier', 'wcuganda' ),
		'wcu_sponsor_active'              => __( 'Active', 'wcuganda' ),
		'wcu_sponsor_year'                => __( 'Year', 'wcuganda' ),
		'wcu_sponsor_url'                 => __( 'Website', 'wcuganda' ),
		'date'                            => __( 'Published', 'wcuganda' ),
	);
}

/**
 * Sponsor column cell renderer.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function wcu_sponsor_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'wcu_sponsor_logo':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 60, 30 ) );
			} else {
				echo '—';
			}
			break;

		case 'wcu_sponsor_active':
			$active = '1' === get_post_meta( $post_id, '_wcu_sponsor_active', true );
			echo $active
				? '<span style="color:#16A34A;font-weight:600;">' . esc_html__( 'Yes', 'wcuganda' ) . '</span>'
				: '<span style="color:#525252;">' . esc_html__( 'No', 'wcuganda' ) . '</span>';
			break;

		case 'wcu_sponsor_year':
			echo esc_html( get_post_meta( $post_id, '_wcu_sponsor_year', true ) ?: '—' );
			break;

		case 'wcu_sponsor_url':
			$url = get_post_meta( $post_id, '_wcu_sponsor_url', true );
			if ( ! empty( $url ) ) {
				$host = wp_parse_url( $url, PHP_URL_HOST );
				printf(
					'<a href="%1$s" target="_blank" rel="noopener">%2$s</a>',
					esc_url( $url ),
					esc_html( $host ? $host : $url )
				);
			} else {
				echo '—';
			}
			break;
	}
}

// -----------------------------------------------------------------------------
// WordCamps
// -----------------------------------------------------------------------------
add_filter( 'manage_wcu_wordcamp_posts_columns', 'wcu_wordcamp_columns' );
add_action( 'manage_wcu_wordcamp_posts_custom_column', 'wcu_wordcamp_column_content', 10, 2 );

/**
 * WordCamp list columns.
 *
 * @param array $columns Default columns.
 * @return array
 */
function wcu_wordcamp_columns( $columns ) {
	return array(
		'cb'                  => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'               => __( 'WordCamp', 'wcuganda' ),
		'wcu_wc_year'         => __( 'Year', 'wcuganda' ),
		'wcu_wc_date'         => __( 'Start date', 'wcuganda' ),
		'wcu_wc_venue'        => __( 'Venue', 'wcuganda' ),
		'wcu_wc_attendees'    => __( 'Attendees', 'wcuganda' ),
		'date'                => __( 'Published', 'wcuganda' ),
	);
}

/**
 * WordCamp column cell renderer.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function wcu_wordcamp_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'wcu_wc_year':
			echo esc_html( get_post_meta( $post_id, '_wcu_wc_year', true ) ?: '—' );
			break;

		case 'wcu_wc_date':
			$date = get_post_meta( $post_id, '_wcu_wc_date', true );
			if ( ! empty( $date ) ) {
				$ts = strtotime( $date );
				echo $ts ? esc_html( wp_date( 'j M Y', $ts ) ) : esc_html( $date );
			} else {
				echo '—';
			}
			break;

		case 'wcu_wc_venue':
			echo esc_html( get_post_meta( $post_id, '_wcu_wc_venue', true ) ?: '—' );
			break;

		case 'wcu_wc_attendees':
			$count = get_post_meta( $post_id, '_wcu_wc_attendee_count', true );
			echo $count ? esc_html( number_format_i18n( (int) $count ) ) : '—';
			break;
	}
}

// -----------------------------------------------------------------------------
// Chapters
// -----------------------------------------------------------------------------
add_filter( 'manage_wcu_chapter_posts_columns', 'wcu_chapter_columns' );
add_action( 'manage_wcu_chapter_posts_custom_column', 'wcu_chapter_column_content', 10, 2 );

/**
 * Chapter list columns.
 *
 * @param array $columns Default columns.
 * @return array
 */
function wcu_chapter_columns( $columns ) {
	return array(
		'cb'                       => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'                    => __( 'Chapter', 'wcuganda' ),
		'wcu_chapter_city'         => __( 'City', 'wcuganda' ),
		'wcu_chapter_meetup'       => __( 'Meetup.com', 'wcuganda' ),
		'wcu_chapter_organizers'   => __( 'Organizers', 'wcuganda' ),
		'wcu_chapter_founded'      => __( 'Founded', 'wcuganda' ),
		'date'                     => __( 'Published', 'wcuganda' ),
	);
}

/**
 * Chapter column cell renderer.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function wcu_chapter_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'wcu_chapter_city':
			echo esc_html( get_post_meta( $post_id, '_wcu_chapter_city', true ) ?: '—' );
			break;

		case 'wcu_chapter_meetup':
			$url = get_post_meta( $post_id, '_wcu_chapter_meetup_url', true );
			if ( ! empty( $url ) ) {
				printf( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', esc_url( $url ), esc_html__( 'Open', 'wcuganda' ) );
			} else {
				echo '—';
			}
			break;

		case 'wcu_chapter_organizers':
			$organizers = get_post_meta( $post_id, '_wcu_chapter_organizers', true );
			echo $organizers ? esc_html( wp_trim_words( $organizers, 6 ) ) : '—';
			break;

		case 'wcu_chapter_founded':
			echo esc_html( get_post_meta( $post_id, '_wcu_chapter_founded', true ) ?: '—' );
			break;
	}
}
