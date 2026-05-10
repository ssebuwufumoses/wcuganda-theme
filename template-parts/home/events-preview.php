<?php
/**
 * Homepage events preview.
 *
 * Prefers the `wcu_event` CPT. Falls back to the WordPress.org events API
 * for Uganda when no local events are scheduled yet.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_event_cards = array();

if ( post_type_exists( 'wcu_event' ) ) {
	$wcu_query = new WP_Query(
		array(
			'post_type'      => 'wcu_event',
			'posts_per_page' => 3,
			'post_status'    => 'publish',
			'meta_key'       => '_wcu_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_wcu_event_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		)
	);

	if ( $wcu_query->have_posts() ) {
		while ( $wcu_query->have_posts() ) {
			$wcu_query->the_post();
			$wcu_event_cards[] = wcu_event_to_card_array( get_the_ID() );
		}
		wp_reset_postdata();
	}
}

if ( empty( $wcu_event_cards ) && class_exists( 'WCU_WPOrg_Events' ) ) {
	$wcu_remote_events = WCU_WPOrg_Events::get_events( 'Uganda', 3 );
	foreach ( $wcu_remote_events as $wcu_event ) {
		$wcu_event_cards[] = wcu_wporg_event_to_card_array( $wcu_event );
	}
}
?>

<section class="wcu-events-preview section">
	<div class="container">

		<header class="section-header">
			<p class="section-eyebrow"><?php esc_html_e( 'What\'s next', 'wcuganda' ); ?></p>
			<h2><?php esc_html_e( 'Upcoming events', 'wcuganda' ); ?></h2>
		</header>

		<?php if ( ! empty( $wcu_event_cards ) ) :
			$wcu_event_count = min( count( $wcu_event_cards ), 3 );
			$wcu_grid_class  = 'wcu-grid wcu-grid--' . (int) $wcu_event_count;
			?>
			<div class="<?php echo esc_attr( $wcu_grid_class ); ?>">
				<?php foreach ( $wcu_event_cards as $wcu_card ) : ?>
					<?php get_template_part( 'template-parts/events/card', null, array( 'card' => $wcu_card ) ); ?>
				<?php endforeach; ?>
			</div>

			<p class="wcu-events-preview__cta">
				<a class="wcu-btn wcu-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_event' ) ); ?>">
					<?php esc_html_e( 'View all events', 'wcuganda' ); ?>
					<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
				</a>
			</p>

		<?php else : ?>
			<div class="wcu-events-preview__empty">
				<p class="wcu-empty__title"><?php esc_html_e( 'No upcoming events', 'wcuganda' ); ?></p>
				<p class="wcu-empty__desc">
					<?php esc_html_e( 'Subscribe to be notified when the next one is announced.', 'wcuganda' ); ?>
				</p>
			</div>
		<?php endif; ?>

	</div>
</section>
