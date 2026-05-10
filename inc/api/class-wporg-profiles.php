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
	 * The profile page emits each row as either:
	 *   <li class="wporgactivity wporgactivity-{cat} wporgactivity-{action}">
	 *     <p>...action HTML, with optional <span> excerpt after a <br>...</p>
	 *     <time class="ago" datetime="...">3 weeks ago</time>
	 *   </li>
	 * or the trac-commit variant:
	 *   <li class="tracplugins"> <p>Committed [...]</p> <time>...</time> </li>
	 *
	 * @param string $html Page HTML.
	 * @return array<int,array<string,string>>
	 */
	private static function parse_activity_html( $html ) {
		// Match both `wporgactivity-*` and the `tracplugins` variants.
		$row_pattern = '/<li[^>]+class="[^"]*(?:wporgactivity|tracplugins)[^"]*"[^>]*>(.*?)<\/li>/is';
		if ( ! preg_match_all( $row_pattern, $html, $matches, PREG_SET_ORDER ) ) {
			return array();
		}

		$items = array();
		foreach ( $matches as $match ) {
			$row = $match[1];

			// Pull the inner <p>...</p> — it carries the action HTML.
			$action_html = '';
			$excerpt     = '';
			if ( preg_match( '/<p[^>]*>(.*?)<\/p>/is', $row, $p ) ) {
				$inner = $p[1];

				// The trailing <span>...</span> after a <br> (or directly inside the p)
				// is the post excerpt. Pull it out so it can render as a quote block,
				// and drop it from the action HTML so the action stays one short line.
				if ( preg_match( '/<span[^>]*>(.*?)<\/span>/is', $inner, $sp ) ) {
					$excerpt = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $sp[1] ) ) );
					$inner   = preg_replace( '/<br\s*\/?>\s*<span[^>]*>.*?<\/span>/is', '', $inner );
					$inner   = preg_replace( '/<span[^>]*>.*?<\/span>/is', '', $inner );
				}

				$action_html = self::clean_inline_html( $inner );

				if ( '' !== $excerpt && mb_strlen( $excerpt ) > 220 ) {
					$excerpt = mb_substr( $excerpt, 0, 217 ) . '…';
				}
			}

			// Friendly relative time ("3 weeks ago").
			$time = '';
			if ( preg_match( '/<time[^>]*>([^<]+)<\/time>/i', $row, $ts ) ) {
				$time = trim( wp_strip_all_tags( $ts[1] ) );
			}

			// Activity type — for icon selection. Pull the second wporgactivity-*
			// class (the action), or fall back to the row class for tracplugins.
			$type = 'activity';
			if ( preg_match( '/wporgactivity-([a-z0-9_-]+)\s+wporgactivity-([a-z0-9_-]+)/i', $row, $tm ) ) {
				$type = $tm[2];
			} elseif ( false !== strpos( $row, 'tracplugins' ) ) {
				$type = 'plugin_commit';
			}

			if ( '' === $action_html ) {
				continue;
			}

			$items[] = array(
				'type'    => sanitize_html_class( $type ),
				'action'  => $action_html,
				'excerpt' => sanitize_text_field( $excerpt ),
				'time'    => sanitize_text_field( $time ),
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
				'i'      => array(),
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

		// Current wp.org markup (BuddyPress group list):
		//   <li class="...">
		//     <div class="badge item dashicons badge-{slug} dashicons-{icon}"></div>
		//     {Display Name}
		//   </li>
		// The slug is in the badge-* class; the name is the text node after
		// the closing </div> and before the </li>.
		$pattern = '/<div\s+class="badge\s+item\s+dashicons\s+badge-([a-z0-9-]+)[^"]*"[^>]*>\s*<\/div>\s*([^<]+?)\s*<\/li>/is';
		preg_match_all( $pattern, $html, $matches );

		$badges = array();
		$seen   = array();
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $i => $slug ) {
				$slug = sanitize_title( $slug );
				if ( '' === $slug ) {
					continue;
				}

				$name = isset( $matches[2][ $i ] )
					? trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $matches[2][ $i ] ) ) )
					: '';
				if ( '' === $name ) {
					$name = ucwords( str_replace( '-', ' ', $slug ) );
				}

				// Dedup on slug+name. wp.org reuses the same badge slug for
				// related-but-distinct badges (e.g. `organizer` covers both
				// "Meetup Organizer" and "WordCamp Organizer"); the display
				// name is what disambiguates them.
				$key = $slug . '|' . strtolower( $name );
				if ( isset( $seen[ $key ] ) ) {
					continue;
				}
				$seen[ $key ] = true;

				$badges[] = array(
					'slug' => $slug,
					'name' => sanitize_text_field( $name ),
				);
			}
		}

		return $badges;
	}
}

/**
 * Flush every cached wp.org artifact (badges, avatar, activity, plugins,
 * themes) for a member when their post is saved. Catches the case where an
 * editor adds/changes the wp.org username and shouldn't have to wait 30 min
 * for the negative-cache window to expire before the data appears.
 */
add_action(
	'save_post_wcu_member',
	static function ( $post_id ) {
		if ( ! $post_id || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}
		$username = get_post_meta( $post_id, '_wcu_member_wporg_username', true );
		if ( empty( $username ) ) {
			return;
		}
		WCU_WPOrg_Profiles::flush_cache( $username );
		if ( class_exists( 'WCU_WPOrg_Repo' ) ) {
			WCU_WPOrg_Repo::flush_cache( $username );
		}
	}
);

/**
 * Admin-only `?wcu_refresh_wporg=1` query trigger — flushes the wp.org
 * caches for the currently-viewed member profile. Useful for verifying
 * after fixing a parser without waiting for the cache to age out.
 */
add_action(
	'template_redirect',
	static function () {
		if ( ! isset( $_GET['wcu_refresh_wporg'] ) || ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		if ( ! is_singular( 'wcu_member' ) ) {
			return;
		}
		$username = get_post_meta( get_queried_object_id(), '_wcu_member_wporg_username', true );
		if ( empty( $username ) ) {
			return;
		}
		WCU_WPOrg_Profiles::flush_cache( $username );
		if ( class_exists( 'WCU_WPOrg_Repo' ) ) {
			WCU_WPOrg_Repo::flush_cache( $username );
		}
		// Bounce to the same URL without the query var so the next render
		// is the freshly-fetched data (and the URL stays clean).
		wp_safe_redirect( remove_query_arg( 'wcu_refresh_wporg' ) );
		exit;
	}
);
