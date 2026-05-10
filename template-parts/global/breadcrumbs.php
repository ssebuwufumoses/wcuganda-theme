<?php
/**
 * Breadcrumb navigation. Renders only on single-post / archive views;
 * silently skips on the homepage.
 *
 * Pure semantic <nav> with an ordered list of links — no JS required.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_front_page() || is_home() ) {
	return;
}

$wcu_crumbs = array();

// Always start with the home crumb.
$wcu_crumbs[] = array(
	'label' => __( 'Home', 'wcuganda' ),
	'url'   => home_url( '/' ),
);

if ( is_singular() ) {
	$wcu_post   = get_post();
	$wcu_pt_obj = get_post_type_object( $wcu_post->post_type );

	if ( $wcu_pt_obj && $wcu_pt_obj->has_archive ) {
		$wcu_crumbs[] = array(
			'label' => $wcu_pt_obj->labels->name,
			'url'   => get_post_type_archive_link( $wcu_post->post_type ),
		);
	} elseif ( 'post' === $wcu_post->post_type ) {
		$wcu_blog_url = get_permalink( (int) get_option( 'page_for_posts' ) );
		if ( $wcu_blog_url ) {
			$wcu_crumbs[] = array(
				'label' => __( 'Blog', 'wcuganda' ),
				'url'   => $wcu_blog_url,
			);
		}
	}

	$wcu_crumbs[] = array(
		'label' => get_the_title( $wcu_post ),
		'url'   => '',
	);
} elseif ( is_post_type_archive() ) {
	$wcu_crumbs[] = array(
		'label' => post_type_archive_title( '', false ),
		'url'   => '',
	);
} elseif ( is_category() || is_tag() || is_tax() ) {
	$wcu_term = get_queried_object();
	if ( $wcu_term && ! is_wp_error( $wcu_term ) ) {
		$wcu_crumbs[] = array(
			'label' => $wcu_term->name,
			'url'   => '',
		);
	}
} elseif ( is_author() ) {
	$wcu_crumbs[] = array(
		'label' => get_the_author_meta( 'display_name', get_queried_object_id() ),
		'url'   => '',
	);
} elseif ( is_search() ) {
	/* translators: %s: search query. */
	$wcu_crumbs[] = array(
		'label' => sprintf( __( 'Search: %s', 'wcuganda' ), get_search_query() ),
		'url'   => '',
	);
} elseif ( is_404() ) {
	$wcu_crumbs[] = array(
		'label' => __( 'Page not found', 'wcuganda' ),
		'url'   => '',
	);
}

if ( count( $wcu_crumbs ) < 2 ) {
	return;
}
?>

<nav class="wcu-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wcuganda' ); ?>">
	<ol class="wcu-breadcrumbs__list">
		<?php foreach ( $wcu_crumbs as $i => $wcu_crumb ) : ?>
			<li class="wcu-breadcrumbs__item">
				<?php if ( ! empty( $wcu_crumb['url'] ) ) : ?>
					<a href="<?php echo esc_url( $wcu_crumb['url'] ); ?>"><?php echo esc_html( $wcu_crumb['label'] ); ?></a>
				<?php else : ?>
					<span aria-current="page"><?php echo esc_html( $wcu_crumb['label'] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
