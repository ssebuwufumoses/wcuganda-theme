<?php
/**
 * Events archive template.
 *
 * Splits results into "Upcoming" and "Past" sections (past below the fold),
 * with chapter + event-type filter chips above. Falls back to a WP.org
 * Events API list ("Discover more across Uganda") when no local events
 * are scheduled.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main wcu-events-archive">

	<header class="wcu-events-archive__header">
		<div class="container">
			<p class="section-eyebrow"><?php esc_html_e( 'Community calendar', 'wcuganda' ); ?></p>
			<h1 class="wcu-events-archive__title"><?php post_type_archive_title(); ?></h1>
			<p class="wcu-events-archive__lead">
				<?php esc_html_e( 'Meetups, WordCamps, workshops, and contributor days across Uganda. Filter by chapter or type to find what\'s near you.', 'wcuganda' ); ?>
			</p>
		</div>
	</header>

	<div class="container">

		<?php get_template_part( 'template-parts/events/filters' ); ?>

		<?php
		// Build queries: $wp_query covers upcoming (filtered by pre_get_posts);
		// past events are queried separately for the "past" section.

		$wcu_upcoming_cards = array();
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$wcu_upcoming_cards[] = wcu_event_to_card_array( get_the_ID() );
			}
			wp_reset_postdata();
		}
		?>

		<section class="wcu-events-archive__section" aria-labelledby="wcu-upcoming-heading">
			<h2 id="wcu-upcoming-heading" class="wcu-events-archive__section-title">
				<?php esc_html_e( 'Upcoming', 'wcuganda' ); ?>
			</h2>

			<?php if ( ! empty( $wcu_upcoming_cards ) ) :
				$wcu_count      = count( $wcu_upcoming_cards );
				$wcu_grid_class = 'wcu-grid wcu-grid--' . (int) min( $wcu_count, 3 );
				?>
				<div class="<?php echo esc_attr( $wcu_grid_class ); ?>">
					<?php foreach ( $wcu_upcoming_cards as $wcu_card ) : ?>
						<?php get_template_part( 'template-parts/events/card', null, array( 'card' => $wcu_card ) ); ?>
					<?php endforeach; ?>
				</div>

				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => __( '&larr; Newer', 'wcuganda' ),
						'next_text' => __( 'Older &rarr;', 'wcuganda' ),
					)
				);
				?>

			<?php else : ?>
				<p class="wcu-events-archive__empty">
					<?php esc_html_e( 'No upcoming events match the current filters.', 'wcuganda' ); ?>
				</p>
			<?php endif; ?>
		</section>

		<?php
		// Past events — only show on page 1, no filters in this section.
		$wcu_paged = max( 1, (int) get_query_var( 'paged' ) );
		if ( 1 === $wcu_paged ) :
			$wcu_past = new WP_Query(
				array(
					'post_type'      => 'wcu_event',
					'posts_per_page' => 6,
					'post_status'    => 'publish',
					'meta_key'       => '_wcu_event_date',
					'orderby'        => 'meta_value',
					'order'          => 'DESC',
					'meta_query'     => array(
						array(
							'key'     => '_wcu_event_date',
							'value'   => current_time( 'Y-m-d' ),
							'compare' => '<',
							'type'    => 'DATE',
						),
					),
				)
			);

			if ( $wcu_past->have_posts() ) :
				?>
				<section class="wcu-events-archive__section wcu-events-archive__section--past" aria-labelledby="wcu-past-heading">
					<h2 id="wcu-past-heading" class="wcu-events-archive__section-title">
						<?php esc_html_e( 'Past events', 'wcuganda' ); ?>
					</h2>

					<div class="wcu-grid wcu-grid--3">
						<?php
						while ( $wcu_past->have_posts() ) :
							$wcu_past->the_post();
							get_template_part( 'template-parts/events/card', null, array( 'card' => wcu_event_to_card_array( get_the_ID() ) ) );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</section>
				<?php
			endif;
		endif;
		?>

		<?php
		// WP.org Events API surface — only on the unfiltered page 1, only if it
		// returns anything. Useful when the local CPT is empty or sparse.
		if ( 1 === $wcu_paged && '' === ( isset( $_GET['chapter'] ) ? sanitize_title( wp_unslash( $_GET['chapter'] ) ) : '' ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& '' === ( isset( $_GET['type'] ) ? sanitize_title( wp_unslash( $_GET['type'] ) ) : '' ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& class_exists( 'WCU_WPOrg_Events' ) ) :
			$wcu_remote = WCU_WPOrg_Events::get_events( 'Uganda', 6 );
			if ( ! empty( $wcu_remote ) ) :
				?>
				<section class="wcu-events-archive__section wcu-events-archive__section--remote" aria-labelledby="wcu-remote-heading">
					<h2 id="wcu-remote-heading" class="wcu-events-archive__section-title">
						<?php esc_html_e( 'Discover more across Uganda', 'wcuganda' ); ?>
					</h2>
					<p class="wcu-events-archive__section-lead">
						<?php esc_html_e( 'WordPress events listed on wordpress.org &mdash; from organizers across the country.', 'wcuganda' ); ?>
					</p>

					<div class="wcu-grid wcu-grid--3">
						<?php foreach ( $wcu_remote as $wcu_remote_event ) :
							get_template_part( 'template-parts/events/card', null, array( 'card' => wcu_wporg_event_to_card_array( $wcu_remote_event ) ) );
						endforeach; ?>
					</div>
				</section>
				<?php
			endif;
		endif;
		?>

	</div>

</main>

<?php
get_footer();
