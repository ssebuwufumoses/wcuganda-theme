<?php
/**
 * WordPress.org plugins + themes repo client.
 *
 * Queries the public api.wordpress.org endpoints for a username's authored
 * plugins and themes. Cached 24h.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCU_WPOrg_Repo
 */
class WCU_WPOrg_Repo {

	const PLUGINS_ENDPOINT = 'https://api.wordpress.org/plugins/info/1.2/';
	const THEMES_ENDPOINT  = 'https://api.wordpress.org/themes/info/1.2/';
	const CACHE_LIFETIME   = 24 * HOUR_IN_SECONDS;
	const CACHE_NEGATIVE   = 30 * MINUTE_IN_SECONDS;

	/**
	 * Get plugins authored by a wp.org username.
	 *
	 * @param string $username Username.
	 * @param int    $limit    Max results.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_plugins( $username, $limit = 12 ) {
		return self::query(
			'plugins',
			$username,
			$limit,
			array(
				'action'  => 'query_plugins',
				'request' => array(
					'author'   => $username,
					'per_page' => max( 1, (int) $limit ),
					'fields'   => array(
						'short_description' => true,
						'rating'            => true,
						'active_installs'   => true,
						'icons'             => true,
					),
				),
			)
		);
	}

	/**
	 * Get themes authored by a wp.org username.
	 *
	 * @param string $username Username.
	 * @param int    $limit    Max results.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_themes( $username, $limit = 12 ) {
		return self::query(
			'themes',
			$username,
			$limit,
			array(
				'action'  => 'query_themes',
				'request' => array(
					'author'   => $username,
					'per_page' => max( 1, (int) $limit ),
					'fields'   => array(
						'description'    => true,
						'rating'         => true,
						'screenshot_url' => true,
					),
				),
			)
		);
	}

	/**
	 * Run a query against the appropriate endpoint and normalize results.
	 *
	 * @param string $kind  'plugins' or 'themes'.
	 * @param string $username Raw username.
	 * @param int    $limit Max results.
	 * @param array  $args  POST args.
	 * @return array
	 */
	private static function query( $kind, $username, $limit, $args ) {
		$username = sanitize_user( ltrim( (string) $username, '@' ), true );
		if ( '' === $username ) {
			return array();
		}

		$cache_key = 'wcu_wporg_' . $kind . '_' . md5( $username );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return array_slice( $cached, 0, $limit );
		}

		$endpoint = ( 'plugins' === $kind ) ? self::PLUGINS_ENDPOINT : self::THEMES_ENDPOINT;

		$response = wp_remote_post(
			$endpoint,
			array(
				'timeout'    => 8,
				'body'       => $args,
				'user-agent' => 'WCUganda/' . WCU_THEME_VERSION . '; ' . home_url(),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			set_transient( $cache_key, array(), self::CACHE_NEGATIVE );
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$key  = ( 'plugins' === $kind ) ? 'plugins' : 'themes';

		if ( empty( $body[ $key ] ) || ! is_array( $body[ $key ] ) ) {
			set_transient( $cache_key, array(), self::CACHE_LIFETIME );
			return array();
		}

		$items = array();
		foreach ( $body[ $key ] as $item ) {
			$items[] = self::normalize_item( $item, $kind );
		}

		set_transient( $cache_key, $items, self::CACHE_LIFETIME );

		return array_slice( $items, 0, $limit );
	}

	/**
	 * Normalize a plugin/theme payload.
	 *
	 * @param array  $item Raw API item.
	 * @param string $kind 'plugins' or 'themes'.
	 * @return array
	 */
	private static function normalize_item( $item, $kind ) {
		$slug   = isset( $item['slug'] ) ? sanitize_title( $item['slug'] ) : '';
		$name   = isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
		$rating = isset( $item['rating'] ) ? (float) $item['rating'] : 0;

		$description = '';
		if ( 'plugins' === $kind ) {
			$description = isset( $item['short_description'] ) ? wp_strip_all_tags( $item['short_description'] ) : '';
		} else {
			$description = isset( $item['description'] ) ? wp_strip_all_tags( $item['description'] ) : '';
		}

		$icon = '';
		if ( 'plugins' === $kind && ! empty( $item['icons'] ) && is_array( $item['icons'] ) ) {
			foreach ( array( '1x', 'svg', '2x', 'default' ) as $icon_size ) {
				if ( ! empty( $item['icons'][ $icon_size ] ) ) {
					$icon = esc_url_raw( $item['icons'][ $icon_size ] );
					break;
				}
			}
		} elseif ( 'themes' === $kind && ! empty( $item['screenshot_url'] ) ) {
			$icon = esc_url_raw( $item['screenshot_url'] );
		}

		$installs = isset( $item['active_installs'] ) ? (int) $item['active_installs'] : 0;

		$base = ( 'plugins' === $kind ) ? 'https://wordpress.org/plugins/' : 'https://wordpress.org/themes/';

		return array(
			'name'             => $name,
			'slug'             => $slug,
			'description'      => $description,
			'rating'           => $rating,
			'active_installs'  => $installs,
			'icon'             => $icon,
			'url'              => $base . $slug . '/',
		);
	}
}
