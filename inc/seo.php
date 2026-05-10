<?php
/**
 * SEO module — OpenGraph, Twitter Cards, JSON-LD structured data,
 * and resource hints for external services.
 *
 * Schema rendered by context:
 * - Site-wide: Organization
 * - Homepage: WebSite + SearchAction
 * - Single event:    Event
 * - Single member:   Person
 * - Single sponsor:  Organization (the sponsor)
 * - Single chapter:  Organization
 * - Single WordCamp: Event
 * - Single post:     Article
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------------
// OpenGraph + Twitter Card meta tags
// -----------------------------------------------------------------------------
add_action( 'wp_head', 'wcu_seo_meta_tags', 5 );

/**
 * Output OG + Twitter Card meta tags.
 *
 * @return void
 */
function wcu_seo_meta_tags() {
	$site_name   = get_bloginfo( 'name' );
	$site_locale = str_replace( '-', '_', get_locale() );
	$image_url   = wcu_seo_get_image_url();

	$title       = wcu_seo_get_title();
	$description = wcu_seo_get_description();
	$url         = wcu_seo_get_canonical();
	$type        = wcu_seo_get_og_type();

	echo "\n<!-- Open Graph -->\n";
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $site_name ) );
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( $site_locale ) );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $description ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	if ( $image_url ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image_url ) );
	}

	echo "<!-- Twitter Card -->\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $description ) {
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	}
	if ( $image_url ) {
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image_url ) );
	}

	$twitter_handle = get_theme_mod( 'wcu_social_twitter', '' );
	if ( $twitter_handle ) {
		$handle = trim( wp_parse_url( $twitter_handle, PHP_URL_PATH ) ?? '', '/' );
		if ( $handle ) {
			printf( '<meta name="twitter:site" content="@%s">' . "\n", esc_attr( $handle ) );
		}
	}

	echo "<!-- Canonical -->\n";
	printf( '<link rel="canonical" href="%s">' . "\n\n", esc_url( $url ) );
}

/**
 * Title for OG / Twitter — strip site name from front-end title.
 *
 * @return string
 */
function wcu_seo_get_title() {
	if ( is_singular() ) {
		return wp_strip_all_tags( get_the_title() );
	}
	if ( is_home() || is_front_page() ) {
		$tagline = get_bloginfo( 'description' );
		return $tagline ? get_bloginfo( 'name' ) . ' — ' . $tagline : get_bloginfo( 'name' );
	}
	if ( is_post_type_archive() ) {
		return wp_strip_all_tags( post_type_archive_title( '', false ) );
	}
	if ( is_archive() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}
	if ( is_search() ) {
		/* translators: %s: search query. */
		return sprintf( __( 'Search results for: %s', 'wcuganda' ), get_search_query() );
	}
	if ( is_404() ) {
		return __( 'Page not found', 'wcuganda' );
	}
	return get_bloginfo( 'name' );
}

/**
 * Description for OG / Twitter — uses excerpt, post content, or tagline.
 *
 * @return string
 */
function wcu_seo_get_description() {
	if ( is_singular() && has_excerpt() ) {
		return wp_strip_all_tags( get_the_excerpt() );
	}
	if ( is_singular() ) {
		$content = get_post_field( 'post_content', get_the_ID() );
		if ( $content ) {
			return wp_trim_words( wp_strip_all_tags( $content ), 30, '…' );
		}
	}
	if ( is_archive() ) {
		$desc = term_description();
		if ( $desc ) {
			return wp_strip_all_tags( $desc );
		}
	}
	return wp_strip_all_tags( get_bloginfo( 'description' ) );
}

/**
 * Best image URL for the page — featured image, hero image setting,
 * or site icon as final fallback.
 *
 * @return string
 */
function wcu_seo_get_image_url() {
	if ( is_singular() && has_post_thumbnail() ) {
		$url = get_the_post_thumbnail_url( null, 'large' );
		if ( $url ) {
			return $url;
		}
	}

	$hero_id = (int) get_theme_mod( 'wcu_hero_image', 0 );
	if ( $hero_id ) {
		$url = wp_get_attachment_image_url( $hero_id, 'large' );
		if ( $url ) {
			return $url;
		}
	}

	$site_icon = get_site_icon_url( 512 );
	if ( $site_icon ) {
		return $site_icon;
	}

	return '';
}

/**
 * Canonical URL for the current view.
 *
 * @return string
 */
function wcu_seo_get_canonical() {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_post_type_archive() ) {
		return get_post_type_archive_link( get_post_type() );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			return get_term_link( $term );
		}
	}
	if ( is_author() ) {
		return get_author_posts_url( get_queried_object_id() );
	}
	if ( is_home() || is_front_page() ) {
		return home_url( '/' );
	}
	return home_url( add_query_arg( null, null ) );
}

/**
 * og:type per context.
 *
 * @return string
 */
function wcu_seo_get_og_type() {
	if ( is_singular( array( 'post', 'wcu_event', 'wcu_wordcamp' ) ) ) {
		return 'article';
	}
	if ( is_singular( 'wcu_member' ) ) {
		return 'profile';
	}
	return 'website';
}

// -----------------------------------------------------------------------------
// JSON-LD structured data
// -----------------------------------------------------------------------------
add_action( 'wp_head', 'wcu_seo_jsonld', 6 );

/**
 * Emit structured-data JSON-LD blocks based on the current view.
 *
 * @return void
 */
function wcu_seo_jsonld() {
	$blocks = array();

	// Site-wide Organization.
	$blocks[] = wcu_seo_jsonld_organization();

	// WebSite + SearchAction on the homepage.
	if ( is_home() || is_front_page() ) {
		$blocks[] = wcu_seo_jsonld_website();
	}

	if ( is_singular( 'wcu_event' ) || is_singular( 'wcu_wordcamp' ) ) {
		$blocks[] = wcu_seo_jsonld_event( get_post() );
	}

	if ( is_singular( 'wcu_member' ) ) {
		$blocks[] = wcu_seo_jsonld_person( get_post() );
	}

	if ( is_singular( 'wcu_sponsor' ) || is_singular( 'wcu_chapter' ) ) {
		$blocks[] = wcu_seo_jsonld_organization_post( get_post() );
	}

	if ( is_singular( 'post' ) ) {
		$blocks[] = wcu_seo_jsonld_article( get_post() );
	}

	foreach ( $blocks as $block ) {
		if ( ! empty( $block ) ) {
			echo "<script type=\"application/ld+json\">" . wp_json_encode( $block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON_UNESCAPED_SLASHES + UTF-8 is safe inside script type=application/ld+json.
		}
	}
}

/**
 * @return array
 */
function wcu_seo_jsonld_organization() {
	return array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
		'logo'     => get_site_icon_url( 512 ) ?: '',
		'sameAs'   => array_values(
			array_filter(
				array(
					get_theme_mod( 'wcu_social_twitter', '' ),
					get_theme_mod( 'wcu_social_github', '' ),
					get_theme_mod( 'wcu_social_linkedin', '' ),
					get_theme_mod( 'wcu_social_youtube', '' ),
					get_theme_mod( 'wcu_social_meetup', '' ),
				)
			)
		),
	);
}

/**
 * @return array
 */
function wcu_seo_jsonld_website() {
	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'name'            => get_bloginfo( 'name' ),
		'url'             => home_url( '/' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * @param WP_Post $post Event or WordCamp.
 * @return array
 */
function wcu_seo_jsonld_event( $post ) {
	$is_wc = ( 'wcu_wordcamp' === $post->post_type );

	$date_meta_key = $is_wc ? '_wcu_wc_date' : '_wcu_event_date';
	$end_meta_key  = $is_wc ? '_wcu_wc_end_date' : '_wcu_event_end_date';
	$venue_key     = $is_wc ? '_wcu_wc_venue' : '_wcu_event_venue';
	$address_key   = $is_wc ? '_wcu_wc_address' : '_wcu_event_address';

	$start_date = get_post_meta( $post->ID, $date_meta_key, true );
	$end_date   = get_post_meta( $post->ID, $end_meta_key, true );
	$venue      = get_post_meta( $post->ID, $venue_key, true );
	$address    = get_post_meta( $post->ID, $address_key, true );

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Event',
		'name'        => get_the_title( $post ),
		'url'         => get_permalink( $post ),
		'description' => wp_trim_words( wp_strip_all_tags( $post->post_content ), 50, '…' ),
	);

	if ( $start_date ) {
		$schema['startDate'] = $start_date;
	}
	if ( $end_date ) {
		$schema['endDate'] = $end_date;
	} elseif ( $start_date ) {
		$schema['endDate'] = $start_date;
	}

	if ( has_post_thumbnail( $post ) ) {
		$schema['image'] = get_the_post_thumbnail_url( $post, 'large' );
	}

	if ( $venue ) {
		$schema['location'] = array(
			'@type' => 'Place',
			'name'  => $venue,
		);
		if ( $address ) {
			$schema['location']['address'] = $address;
		}
	}

	$schema['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';
	$schema['eventStatus']         = 'https://schema.org/EventScheduled';

	$schema['organizer'] = array(
		'@type' => 'Organization',
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	return $schema;
}

/**
 * @param WP_Post $post Member.
 * @return array
 */
function wcu_seo_jsonld_person( $post ) {
	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Person',
		'name'     => get_the_title( $post ),
		'url'      => get_permalink( $post ),
	);

	if ( has_post_thumbnail( $post ) ) {
		$schema['image'] = get_the_post_thumbnail_url( $post, 'large' );
	}

	$role = get_post_meta( $post->ID, '_wcu_member_role_title', true );
	if ( $role ) {
		$schema['jobTitle'] = $role;
	}

	$company = get_post_meta( $post->ID, '_wcu_member_company', true );
	if ( $company ) {
		$schema['worksFor'] = array(
			'@type' => 'Organization',
			'name'  => $company,
		);
	}

	$same_as = array_values(
		array_filter(
			array(
				get_post_meta( $post->ID, '_wcu_member_twitter', true ),
				get_post_meta( $post->ID, '_wcu_member_github', true ),
				get_post_meta( $post->ID, '_wcu_member_linkedin', true ),
				get_post_meta( $post->ID, '_wcu_member_website', true ),
			)
		)
	);
	if ( $same_as ) {
		$schema['sameAs'] = $same_as;
	}

	return $schema;
}

/**
 * @param WP_Post $post Sponsor or Chapter.
 * @return array
 */
function wcu_seo_jsonld_organization_post( $post ) {
	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => get_the_title( $post ),
		'url'      => get_permalink( $post ),
	);

	if ( has_post_thumbnail( $post ) ) {
		$schema['logo'] = get_the_post_thumbnail_url( $post, 'large' );
	}

	if ( 'wcu_sponsor' === $post->post_type ) {
		$site_url = get_post_meta( $post->ID, '_wcu_sponsor_url', true );
		if ( $site_url ) {
			$schema['url']    = $site_url;
			$schema['sameAs'] = array( $site_url );
		}
	}

	if ( 'wcu_chapter' === $post->post_type ) {
		$city = get_post_meta( $post->ID, '_wcu_chapter_city', true );
		if ( $city ) {
			$schema['address'] = array(
				'@type'           => 'PostalAddress',
				'addressLocality' => $city,
				'addressCountry'  => 'UG',
			);
		}
	}

	return $schema;
}

/**
 * @param WP_Post $post Blog post.
 * @return array
 */
function wcu_seo_jsonld_article( $post ) {
	$author_id = (int) $post->post_author;

	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'Article',
		'headline'      => get_the_title( $post ),
		'url'           => get_permalink( $post ),
		'datePublished' => get_post_time( 'c', true, $post ),
		'dateModified'  => get_post_modified_time( 'c', true, $post ),
		'author'        => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', $author_id ),
			'url'   => get_author_posts_url( $author_id ),
		),
		'publisher'     => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'logo'  => array(
				'@type' => 'ImageObject',
				'url'   => get_site_icon_url( 512 ) ?: '',
			),
		),
	);

	if ( has_post_thumbnail( $post ) ) {
		$schema['image'] = get_the_post_thumbnail_url( $post, 'large' );
	}

	if ( has_excerpt( $post ) ) {
		$schema['description'] = wp_strip_all_tags( get_the_excerpt( $post ) );
	}

	return $schema;
}

// -----------------------------------------------------------------------------
// Resource hints — DNS-prefetch external services we contact server-side.
// -----------------------------------------------------------------------------
add_filter( 'wp_resource_hints', 'wcu_resource_hints', 10, 2 );

/**
 * Add dns-prefetch hints for the WP.org / WordCamp services we call from
 * server-side wp_remote_get calls. They appear in the page so browsers
 * resolve the IP early in case the user's session triggers a related
 * client-side fetch (e.g. Meetup embed iframe).
 *
 * @param array  $hints    Existing hints.
 * @param string $relation Relation type.
 * @return array
 */
function wcu_resource_hints( $hints, $relation ) {
	if ( 'dns-prefetch' === $relation ) {
		$hints[] = '//api.wordpress.org';
		$hints[] = '//profiles.wordpress.org';
		$hints[] = '//central.wordcamp.org';
		$hints[] = '//www.meetup.com';
	}
	return $hints;
}
