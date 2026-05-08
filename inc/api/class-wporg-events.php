<?php
/**
 * WordPress.org Events API client.
 *
 * Fetches upcoming WordPress events for a given location, with transient
 * caching so the API is hit at most once per cache window.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCU_WPOrg_Events
 */
class WCU_WPOrg_Events {

	const ENDPOINT       = 'https://api.wordpress.org/events/1.0/';
	const CACHE_LIFETIME = 6 * HOUR_IN_SECONDS;
	const CACHE_PREFIX   = 'wcu_wporg_events_';

	/**
	 * Fetch events for a location, returning normalized event arrays.
	 *
	 * @param string $location Location string (e.g. "Uganda", "Kampala").
	 * @param int    $limit    Maximum events to return (after dedupe).
	 * @return array<int,array<string,string>> List of event arrays. Empty on failure.
	 */
	public static function get_events( $location = 'Uganda', $limit = 6 ) {
		$location = sanitize_text_field( $location );
		$limit    = max( 1, (int) $limit );

		$cache_key = self::CACHE_PREFIX . md5( $location );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return array_slice( $cached, 0, $limit );
		}

		$response = wp_remote_get(
			add_query_arg(
				array(
					'location' => rawurlencode( $location ),
					'locale'   => 'en_US',
				),
				self::ENDPOINT
			),
			array(
				'timeout'    => 5,
				'user-agent' => 'WCUganda/' . WCU_THEME_VERSION . '; ' . home_url(),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			// Cache the empty result briefly so we don't hammer a failing API.
			set_transient( $cache_key, array(), 15 * MINUTE_IN_SECONDS );
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['events'] ) || ! is_array( $body['events'] ) ) {
			set_transient( $cache_key, array(), self::CACHE_LIFETIME );
			return array();
		}

		$events = array();
		foreach ( $body['events'] as $event ) {
			$events[] = self::normalize_event( $event );
		}

		set_transient( $cache_key, $events, self::CACHE_LIFETIME );

		return array_slice( $events, 0, $limit );
	}

	/**
	 * Force a refresh of cached events for a location.
	 *
	 * @param string $location Location string.
	 * @return void
	 */
	public static function flush_cache( $location = 'Uganda' ) {
		delete_transient( self::CACHE_PREFIX . md5( sanitize_text_field( $location ) ) );
	}

	/**
	 * Normalize an event payload from the WP.org API to a stable shape.
	 *
	 * @param array $event Raw event data.
	 * @return array<string,string>
	 */
	private static function normalize_event( $event ) {
		$location = isset( $event['location'] ) && is_array( $event['location'] ) ? $event['location'] : array();

		return array(
			'type'       => isset( $event['type'] ) ? sanitize_text_field( $event['type'] ) : 'meetup',
			'title'      => isset( $event['title'] ) ? sanitize_text_field( $event['title'] ) : '',
			'url'        => isset( $event['url'] ) ? esc_url_raw( $event['url'] ) : '',
			'meetup'     => isset( $event['meetup'] ) ? sanitize_text_field( $event['meetup'] ) : '',
			'meetup_url' => isset( $event['meetup_url'] ) ? esc_url_raw( $event['meetup_url'] ) : '',
			'date'       => isset( $event['date'] ) ? sanitize_text_field( $event['date'] ) : '',
			'date_utc'   => isset( $event['date_utc'] ) ? sanitize_text_field( $event['date_utc'] ) : '',
			'location'   => isset( $location['location'] ) ? sanitize_text_field( $location['location'] ) : '',
			'country'    => isset( $location['country'] ) ? sanitize_text_field( $location['country'] ) : '',
			'latitude'   => isset( $location['latitude'] ) ? (string) $location['latitude'] : '',
			'longitude'  => isset( $location['longitude'] ) ? (string) $location['longitude'] : '',
		);
	}
}
