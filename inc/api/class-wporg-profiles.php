<?php
/**
 * WordPress.org Profiles API client.
 *
 * Fetches a user's contributor badges with two strategies in order:
 * 1. The wporg-internal JSON endpoint (when public).
 * 2. The public HTML profile page, parsed for `profile-badge-*` classes.
 *
 * Results cached 24h via transients. Negative results cached briefly so
 * a flaky upstream doesn't repeatedly hammer the API.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCU_WPOrg_Profiles
 */
class WCU_WPOrg_Profiles {

	const API_ENDPOINT     = 'https://profiles.wordpress.org/wp-json/wporg-internal/v1/users/';
	const USERS_ENDPOINT   = 'https://profiles.wordpress.org/wp-json/wp/v2/users';
	const PROFILE_URL_BASE = 'https://profiles.wordpress.org/';
	const CACHE_LIFETIME   = 24 * HOUR_IN_SECONDS;
	const CACHE_NEGATIVE   = 30 * MINUTE_IN_SECONDS;
	const CACHE_PREFIX     = 'wcu_wporg_badges_';
	const AVATAR_PREFIX    = 'wcu_wporg_avatar_';
	const ACTIVITY_PREFIX  = 'wcu_wporg_activity_';
	const ACTIVITY_TTL     = 6 * HOUR_IN_SECONDS;

	/**
	 * Get badges for a wp.org username.
	 *
	 * @param string $username wp.org username (no @, no URL).
	 * @return array<int,array<string,string>> List of {slug, name} pairs. Empty on failure.
	 */
	public static function get_badges( $username ) {
		$username = self::sanitize_username( $username );
		if ( '' === $username ) {
			return array();
		}

		$cache_key = self::CACHE_PREFIX . md5( $username );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$badges = self::fetch_via_api( $username );

		if ( empty( $badges ) ) {
			$badges = self::fetch_via_html( $username );
		}

		$lifetime = empty( $badges ) ? self::CACHE_NEGATIVE : self::CACHE_LIFETIME;
		set_transient( $cache_key, $badges, $lifetime );

		return $badges;
	}

	/**
	 * Force a refresh.
	 *
	 * @param string $username Username.
	 * @return void
	 */
	public static function flush_cache( $username ) {
		$username = self::sanitize_username( $username );
		if ( '' !== $username ) {
			delete_transient( self::CACHE_PREFIX . md5( $username ) );
			delete_transient( self::AVATAR_PREFIX . md5( $username ) );
			delete_transient( self::ACTIVITY_PREFIX . md5( $username ) );
		}
	}

	/**
	 * Get recent activity items for a wp.org username.
	 *
	 * Scrapes the public profile HTML because there's no documented JSON
	 * endpoint for the BuddyPress activity stream. Defensive: any parse
	 * mismatch returns an empty array (and gets cached as a negative
	 * result so we don't hammer the upstream on every page view).
	 *
	 * @param string $username Username.
	 * @param int    $limit    Max items to return.
	 * @return array<int,array<string,string>> List of {type, html, time, url, excerpt} pairs.
	 */
	public static function get_activity( $username, $limit = 6 ) {
		$username = self::sanitize_username( $username );
		if ( '' === $username ) {
			return array();
		}

		$limit = max( 1, min( 20, (int) $limit ) );

		$cache_key = self::ACTIVITY_PREFIX . md5( $username );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return array_slice( $cached, 0, $limit );
		}

		$response = wp_remote_get(
			self::PROFILE_URL_BASE . rawurlencode( $username ) . '/',
			array(
				'timeout'    => 6,
				'user-agent' => 'WCUganda/' . WCU_THEME_VERSION . '; ' . home_url(),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			set_transient( $cache_key, array(), self::CACHE_NEGATIVE );
			return array();
		}

		$html = (string) wp_remote_retrieve_body( $response );
		if ( '' === $html ) {
			set_transient( $cache_key, array(), self::CACHE_NEGATIVE );
			return array();
		}

		$items = self::parse_activity_html( $html );

		$lifetime = empty( $items ) ? self::CACHE_NEGATIVE : self::ACTIVITY_TTL;
		set_transient( $cache_key, $items, $lifetime );

		return array_slice( $items, 0, $limit );
	}

	/**
	 * Parse activity items out of a wp.org profile HTML page.
	 *
	 * The profile page renders a BuddyPress activity stream as `<li class="activity-item">`
	 * rows. Each row carries a header (action description with linked names),
	 * an inner excerpt, and a "X ago" timestamp.
	 *
	 * @param string $html Page HTML.
	 * @return array<int,array<string,string>>
	 */
	private static function parse_activity_html( $html ) {
		// Each activity row.
		if ( ! preg_match_all( '/<li[^>]+class="[^"]*activity-item[^"]*"[^>]*>(.*?)<\/li>/is', $html, $matches ) ) {
			return array();
		}

		$items = array();
		foreach ( $matches[1] as $row ) {
			// Action description ("Reacted to a post by Foo Bar").
			$action_html = '';
			if ( preg_match( '/<div[^>]+class="[^"]*activity-header[^"]*"[^>]*>(.*?)<\/div>/is', $row, $head ) ) {
				$action_html = self::clean_inline_html( $head[1] );
			} elseif ( preg_match( '/<p[^>]+class="[^"]*activity-header[^"]*"[^>]*>(.*?)<\/p>/is', $row, $head ) ) {
				$action_html = self::clean_inline_html( $head[1] );
			}

			// Excerpt body, if any.
			$excerpt = '';
			if ( preg_match( '/<div[^>]+class="[^"]*activity-inner[^"]*"[^>]*>(.*?)<\/div>/is', $row, $inner ) ) {
				$excerpt = trim( wp_strip_all_tags( $inner[1] ) );
				$excerpt = preg_replace( '/\s+/', ' ', $excerpt );
				if ( mb_strlen( $excerpt ) > 220 ) {
					$excerpt = mb_substr( $excerpt, 0, 217 ) . '…';
				}
			}

			// "2 hours ago" timestamp.
			$time = '';
			if ( preg_match( '/class="[^"]*time-since[^"]*"[^>]*>([^<]+)<\/a>/i', $row, $ts ) ) {
				$time = trim( wp_strip_all_tags( $ts[1] ) );
			} elseif ( preg_match( '/<time[^>]*>([^<]+)<\/time>/i', $row, $ts ) ) {
				$time = trim( wp_strip_all_tags( $ts[1] ) );
			}

			// Activity type — derived from the row's class list.
			$type = 'activity';
			if ( preg_match( '/class="[^"]*activity-item[^"]*"/i', $row, $cls ) ) {
				if ( preg_match( '/\b(reaction|favorite|new_blog_post|new_plugin|new_theme|wp_org_meeting|new_member|added_plugin|updated_plugin|added_theme|updated_theme)\b/', $cls[0], $tm ) ) {
					$type = $tm[1];
				}
			}

			if ( '' === $action_html ) {
				continue;
			}

			$items[] = array(
				'type'    => $type,
				'action'  => $action_html,
				'excerpt' => $excerpt,
				'time'    => $time,
			);
		}

		return $items;
	}

	/**
	 * Strip block tags + scripts but keep links and basic inline emphasis.
	 *
	 * @param string $html Raw chunk.
	 * @return string Sanitized HTML safe to render with wp_kses.
	 */
	private static function clean_inline_html( $html ) {
		$html = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $html );
		$html = trim( wp_kses(
			$html,
			array(
				'a'      => array( 'href' => array(), 'title' => array() ),
				'strong' => array(),
				'em'     => array(),
				'span'   => array(),
			)
		) );
		// Collapse the link href to absolute (wp.org links may be relative).
		$html = preg_replace_callback(
			'/href="(\/[^"]*)"/i',
			static function ( $m ) {
				return 'href="' . esc_url( self::PROFILE_URL_BASE . ltrim( $m[1], '/' ) ) . '"';
			},
			$html
		);
		return preg_replace( '/\s+/', ' ', $html );
	}

	/**
	 * Get the WordPress.org avatar URL for a username, sized.
	 *
	 * Uses the standard `wp/v2/users?slug=` endpoint on profiles.wordpress.org
	 * and rewrites the gravatar `s=` query var to the requested pixel size.
	 *
	 * @param string $username Username.
	 * @param int    $size     Pixel size (32-2048).
	 * @return string Avatar URL, or '' on failure.
	 */
	public static function get_avatar_url( $username, $size = 256 ) {
		$username = self::sanitize_username( $username );
		if ( '' === $username ) {
			return '';
		}

		$size = max( 32, min( 2048, (int) $size ) );

		$cache_key = self::AVATAR_PREFIX . md5( $username );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_string( $cached ) ) {
			return self::resize_avatar_url( $cached, $size );
		}

		$response = wp_remote_get(
			add_query_arg( 'slug', rawurlencode( $username ), self::USERS_ENDPOINT ),
			array(
				'timeout'    => 5,
				'user-agent' => 'WCUganda/' . WCU_THEME_VERSION . '; ' . home_url(),
			)
		);

		$url = '';
		if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( is_array( $body ) && ! empty( $body[0]['avatar_urls'] ) && is_array( $body[0]['avatar_urls'] ) ) {
				// Prefer the largest size advertised — WP usually exposes 24/48/96.
				$avatars = $body[0]['avatar_urls'];
				$url     = (string) ( $avatars['96'] ?? end( $avatars ) );
			}
		}

		// Cache the *base* URL (without size override) so we can rescale on demand.
		$lifetime = '' === $url ? self::CACHE_NEGATIVE : self::CACHE_LIFETIME;
		set_transient( $cache_key, $url, $lifetime );

		if ( '' === $url ) {
			return '';
		}

		return self::resize_avatar_url( $url, $size );
	}

	/**
	 * Rewrite the `s=` query var on a gravatar URL to the desired pixel size.
	 *
	 * @param string $url  Original URL.
	 * @param int    $size Pixel size.
	 * @return string
	 */
	private static function resize_avatar_url( $url, $size ) {
		if ( '' === $url ) {
			return '';
		}
		if ( false !== strpos( $url, 's=' ) ) {
			return preg_replace( '/([?&])s=\d+/', '$1s=' . (int) $size, $url );
		}
		return add_query_arg( 's', (int) $size, $url );
	}

	/**
	 * Strip @ and full-URL noise from a username field.
	 *
	 * @param string $raw Raw user input.
	 * @return string
	 */
	private static function sanitize_username( $raw ) {
		$raw = (string) $raw;
		if ( false !== strpos( $raw, 'profiles.wordpress.org' ) ) {
			$parsed = wp_parse_url( $raw );
			if ( ! empty( $parsed['path'] ) ) {
				$raw = trim( $parsed['path'], '/' );
			}
		}
		$raw = ltrim( $raw, '@' );
		return sanitize_user( $raw, true );
	}

	/**
	 * Try the wporg-internal JSON endpoint.
	 *
	 * @param string $username Username.
	 * @return array
	 */
	private static function fetch_via_api( $username ) {
		$response = wp_remote_get(
			self::API_ENDPOINT . rawurlencode( $username ),
			array(
				'timeout'    => 5,
				'user-agent' => 'WCUganda/' . WCU_THEME_VERSION . '; ' . home_url(),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body ) || ! is_array( $body ) ) {
			return array();
		}

		// The shape isn't documented and may vary; check both common keys.
		$raw_badges = array();
		if ( ! empty( $body['badges'] ) && is_array( $body['badges'] ) ) {
			$raw_badges = $body['badges'];
		} elseif ( ! empty( $body['data']['badges'] ) && is_array( $body['data']['badges'] ) ) {
			$raw_badges = $body['data']['badges'];
		}

		$badges = array();
		foreach ( $raw_badges as $badge ) {
			if ( is_array( $badge ) ) {
				$slug = isset( $badge['slug'] ) ? sanitize_title( $badge['slug'] ) : '';
				$name = isset( $badge['name'] ) ? sanitize_text_field( $badge['name'] ) : '';
			} elseif ( is_string( $badge ) ) {
				$slug = sanitize_title( $badge );
				$name = ucwords( str_replace( '-', ' ', $slug ) );
			} else {
				continue;
			}
			if ( '' === $slug ) {
				continue;
			}
			if ( '' === $name ) {
				$name = ucwords( str_replace( '-', ' ', $slug ) );
			}
			$badges[] = array(
				'slug' => $slug,
				'name' => $name,
			);
		}

		return $badges;
	}

	/**
	 * Fall back to scraping the public profile HTML.
	 *
	 * @param string $username Username.
	 * @return array
	 */
	private static function fetch_via_html( $username ) {
		$response = wp_remote_get(
			self::PROFILE_URL_BASE . rawurlencode( $username ) . '/',
			array(
				'timeout'    => 6,
				'user-agent' => 'WCUganda/' . WCU_THEME_VERSION . '; ' . home_url(),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$html = (string) wp_remote_retrieve_body( $response );
		if ( '' === $html ) {
			return array();
		}

		// wp.org renders badges as `<span class="profile-badge profile-badge-<slug>" title="<name>">`.
		$pattern = '/profile-badge\s+profile-badge-([a-z0-9-]+)[^"]*"\s*[^>]*title="([^"]*)"/i';
		preg_match_all( $pattern, $html, $matches );

		$badges = array();
		$seen   = array();
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $i => $slug ) {
				$slug = sanitize_title( $slug );
				if ( '' === $slug || isset( $seen[ $slug ] ) ) {
					continue;
				}
				$seen[ $slug ] = true;

				$name = isset( $matches[2][ $i ] ) ? sanitize_text_field( $matches[2][ $i ] ) : '';
				if ( '' === $name ) {
					$name = ucwords( str_replace( '-', ' ', $slug ) );
				}
				$badges[] = array(
					'slug' => $slug,
					'name' => $name,
				);
			}
		}

		return $badges;
	}
}
