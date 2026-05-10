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
	 * The profile page emits each row as one of three flavours, all with
	 * the same inner structure (<p>action</p> + <time>):
	 *   wp.org activity:  <li class="wporgactivity wporgactivity-{cat} wporgactivity-{action}">
	 *   trac commits:     <li class="tracplugins">
	 *   GitHub repo work: <li class="github-{Org}-{Repo}">
	 *                     (e.g. github-WordPress-Community-Team for closed
	 *                     issues, opened PRs, etc. on that repo)
	 *
	 * @param string $html Page HTML.
	 * @return array<int,array<string,string>>
	 */
	private static function parse_activity_html( $html ) {
		// Capture the class attribute (group 1) so we can detect the
		// category, and the inner content (group 2) so we can extract the
		// action HTML / time / excerpt. The class attribute also tells us
		// which of the three row flavours we're in.
		$row_pattern = '/<li[^>]+class="([^"]*(?:wporgactivity|tracplugins|github-)[^"]*)"[^>]*>(.*?)<\/li>/is';
		if ( ! preg_match_all( $row_pattern, $html, $matches, PREG_SET_ORDER ) ) {
			return array();
		}

		$items = array();
		foreach ( $matches as $match ) {
			$class_attr = $match[1];
			$row        = $match[2];

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

			// Category + type are read from the OUTER class attribute (now
			// captured separately). Three flavours:
			//   - `wporgactivity wporgactivity-{cat} wporgactivity-{action}`
			//     (blogs/forums/slack/learn/glotpress/wordcamp/photos/etc.)
			//   - `tracplugins` for plugin-SVN commits.
			//   - `github-{Org}-{Repo}` for issues/PRs/commits on a
			//     wp.org-tracked GitHub repo.
			$category = 'activity';
			$type     = 'activity';
			if ( preg_match( '/wporgactivity-([a-z0-9_-]+)\s+wporgactivity-([a-z0-9_-]+)/i', $class_attr, $tm ) ) {
				$category = $tm[1];
				$type     = $tm[2];
			} elseif ( false !== strpos( $class_attr, 'tracplugins' ) ) {
				$category = 'plugins';
				$type     = 'plugin_commit';
			} elseif ( false !== strpos( $class_attr, 'github-' ) ) {
				$category = 'github';
				$type     = 'github_activity';
			}

			if ( '' === $action_html ) {
				continue;
			}

			$items[] = array(
				'category' => sanitize_html_class( $category ),
				'type'     => sanitize_html_class( $type ),
				'action'   => $action_html,
				'excerpt'  => sanitize_text_field( $excerpt ),
				'time'     => sanitize_text_field( $time ),
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
				'icon' => '',
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
		//   <li class="..."><div class="badge item dashicons badge-{slug} [...] [dashicons-{icon}]"></div> Display Name </li>
		//
		// The dashicon-{icon} class is OPTIONAL — some badges (campus-connect-
		// participant, credits-graduate, credits-mentor, openverse, etc.)
		// don't have one because wp.org renders them via a CSS
		// `background-image: url(data:...)` instead of a font glyph.
		// Earlier passes required the dashicon class and rejected those
		// badges entirely. Now we match on the badge-{slug} class alone
		// and extract the dashicon name from the captured class attribute
		// in a second pass when present.
		//
		// Match the whole `<div class="..."></div> Display Name </li>` block
		// in two captures: full class attribute, then display name.
		$pattern = '/<div\s+class="([^"]*\bbadge-[a-z0-9-]+\b[^"]*)"[^>]*>\s*<\/div>\s*([^<]+?)\s*<\/li>/is';
		preg_match_all( $pattern, $html, $matches );

		$badges = array();
		$seen   = array();
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $i => $class_attr ) {
				// Extract badge-{slug} from the class attribute.
				if ( ! preg_match( '/\bbadge-([a-z0-9-]+)\b/', $class_attr, $sm ) ) {
					continue;
				}
				$slug = sanitize_title( $sm[1] );
				if ( '' === $slug ) {
					continue;
				}

				// dashicons-{icon} is optional; capture if present so the
				// renderer can map it to the matching SVG glyph.
				$icon = '';
				if ( preg_match( '/\bdashicons-([a-z0-9-]+)\b/', $class_attr, $im ) ) {
					$icon = sanitize_html_class( $im[1] );
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
					'icon' => $icon,
					'name' => sanitize_text_field( $name ),
				);
			}
		}

		return $badges;
	}
}

/**
 * Flush every cached wp.org artifact for a member as soon as their wp.org
 * username field is added or changed. We hook into the meta lifecycle
 * directly (added_post_meta, updated_post_meta) instead of save_post,
 * because Gutenberg saves meta via a separate REST request that fires
 * AFTER save_post — meaning a save_post handler that read the meta would
 * see the OLD value (or no value) on a fresh post and skip the flush.
 *
 * Hooking the meta itself catches both the editor save flow and any
 * programmatic update_post_meta() calls from import scripts.
 */
$wcu_flush_wporg_for_member = static function ( $post_id, $username ) {
	if ( ! $post_id || empty( $username ) ) {
		return;
	}
	if ( 'wcu_member' !== get_post_type( $post_id ) ) {
		return;
	}
	WCU_WPOrg_Profiles::flush_cache( $username );
	if ( class_exists( 'WCU_WPOrg_Repo' ) ) {
		WCU_WPOrg_Repo::flush_cache( $username );
	}
};

add_action(
	'updated_post_meta',
	static function ( $meta_id, $post_id, $meta_key, $meta_value ) use ( $wcu_flush_wporg_for_member ) {
		if ( '_wcu_member_wporg_username' === $meta_key ) {
			$wcu_flush_wporg_for_member( $post_id, $meta_value );
		}
	},
	10,
	4
);

add_action(
	'added_post_meta',
	static function ( $meta_id, $post_id, $meta_key, $meta_value ) use ( $wcu_flush_wporg_for_member ) {
		if ( '_wcu_member_wporg_username' === $meta_key ) {
			$wcu_flush_wporg_for_member( $post_id, $meta_value );
		}
	},
	10,
	4
);

// Belt-and-suspenders: still hook save_post in case the meta was written
// the legacy way (e.g. from a meta-box save handler) so save_post catches
// it without needing the meta lifecycle hooks above.
add_action(
	'save_post_wcu_member',
	static function ( $post_id ) use ( $wcu_flush_wporg_for_member ) {
		if ( ! $post_id || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}
		$username = get_post_meta( $post_id, '_wcu_member_wporg_username', true );
		$wcu_flush_wporg_for_member( $post_id, $username );
	},
	999
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
