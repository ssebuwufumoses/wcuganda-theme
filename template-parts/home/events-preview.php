<?php
/**
 * Homepage events preview.
 *
 * Prefers the `wcu_event` CPT (when registered in Phase 3). Falls back to
 * the WordPress.org events API for Uganda when no local events exist yet.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_event_cards = array();

// Local CPT path (Phase 3+).
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
			$wcu_event_cards[] = array(
				'title'    => get_the_title(),
				'url'      => get_permalink(),
				'date'     => get_post_meta( get_the_ID(), '_wcu_event_date', true ),
				'venue'    => get_post_meta( get_the_ID(), '_wcu_event_venue', true ),
				'image'    => get_the_post_thumbnail_url( get_the_ID(), 'wcu-card' ),
				'is_local' => true,
			);
		}
		wp_reset_postdata();
	}
}

// Fallback to WP.org Events API.
if ( empty( $wcu_event_cards ) && class_exists( 'WCU_WPOrg_Events' ) ) {
	$wcu_remote_events = WCU_WPOrg_Events::get_events( 'Uganda', 3 );
	foreach ( $wcu_remote_events as $wcu_event ) {
		$wcu_event_cards[] = array(
			'title'    => $wcu_event['title'],
			'url'      => $wcu_event['url'],
			'date'     => $wcu_event['date'],
			'venue'    => $wcu_event['location'],
			'image'    => '',
			'is_local' => false,
			'meetup'   => $wcu_event['meetup'],
		);
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
					<article class="wcu-card">
						<?php if ( ! empty( $wcu_card['image'] ) ) : ?>
							<a class="wcu-card__media" href="<?php echo esc_url( $wcu_card['url'] ); ?>">
								<img src="<?php echo esc_url( $wcu_card['image'] ); ?>"
									alt="<?php echo esc_attr( $wcu_card['title'] ); ?>"
									loading="lazy">
							</a>
						<?php endif; ?>
						<div class="wcu-card__body">
							<?php if ( ! empty( $wcu_card['date'] ) ) : ?>
								<p class="wcu-card__eyebrow">
									<?php
									wcu_svg_icon(
										'calendar',
										array(
											'width'  => 14,
											'height' => 14,
										)
									);
									$wcu_ts = strtotime( $wcu_card['date'] );
									echo esc_html( $wcu_ts ? wp_date( 'l, j M Y', $wcu_ts ) : $wcu_card['date'] );
									?>
								</p>
							<?php endif; ?>

							<h3 class="wcu-card__title">
								<a href="<?php echo esc_url( $wcu_card['url'] ); ?>" <?php echo $wcu_card['is_local'] ? '' : 'rel="nofollow noopener" target="_blank"'; ?>>
									<?php echo esc_html( $wcu_card['title'] ); ?>
								</a>
							</h3>

							<?php if ( ! empty( $wcu_card['venue'] ) ) : ?>
								<p class="wcu-card__meta">
									<?php
									wcu_svg_icon(
										'map-pin',
										array(
											'width'  => 14,
											'height' => 14,
										)
									);
									echo esc_html( $wcu_card['venue'] );
									?>
								</p>
							<?php endif; ?>

							<?php if ( ! empty( $wcu_card['meetup'] ) ) : ?>
								<p class="wcu-card__meta wcu-card__meta--source">
									<?php
									printf(
										/* translators: %s: meetup group name. */
										esc_html__( 'via %s', 'wcuganda' ),
										esc_html( $wcu_card['meetup'] )
									);
									?>
								</p>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<p class="wcu-events-preview__cta">
				<a class="wcu-btn wcu-btn--outline" href="<?php echo esc_url( home_url( '/events/' ) ); ?>">
					<?php esc_html_e( 'View all events', 'wcuganda' ); ?>
					<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
				</a>
			</p>

		<?php else : ?>
			<p class="wcu-events-preview__empty">
				<?php esc_html_e( 'No upcoming events yet — check back soon, or follow us to be the first to know.', 'wcuganda' ); ?>
			</p>
		<?php endif; ?>

	</div>
</section>
