<?php
/**
 * WordCamp archive — chronological list with Upcoming and Past sections.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main wcu-wordcamps-archive">

	<header class="wcu-wordcamps-archive__header">
		<div class="container">
			<p class="section-eyebrow"><?php esc_html_e( 'Annual gatherings', 'wcuganda' ); ?></p>
			<h1 class="wcu-wordcamps-archive__title"><?php post_type_archive_title(); ?></h1>
			<p class="wcu-wordcamps-archive__lead">
				<?php esc_html_e( 'WordCamp Uganda is the local edition of the global WordPress conference series. Each edition gathers contributors, speakers, sponsors, and attendees from across the country.', 'wcuganda' ); ?>
			</p>
		</div>
	</header>

	<div class="container">

		<?php
		// Split into upcoming + past based on the start date meta.
		$wcu_today = current_time( 'Y-m-d' );

		$wcu_upcoming = new WP_Query(
			array(
				'post_type'      => 'wcu_wordcamp',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_key'       => '_wcu_wc_date',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array(
					array(
						'key'     => '_wcu_wc_date',
						'value'   => $wcu_today,
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
			)
		);

		$wcu_past = new WP_Query(
			array(
				'post_type'      => 'wcu_wordcamp',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_key'       => '_wcu_wc_date',
				'orderby'        => 'meta_value',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => '_wcu_wc_date',
						'value'   => $wcu_today,
						'compare' => '<',
						'type'    => 'DATE',
					),
				),
			)
		);
		?>

		<?php if ( $wcu_upcoming->have_posts() ) : ?>
			<section class="wcu-wordcamps-archive__section" aria-labelledby="wcu-wc-upcoming-heading">
				<h2 id="wcu-wc-upcoming-heading" class="wcu-wordcamps-archive__section-title">
					<?php esc_html_e( 'Upcoming', 'wcuganda' ); ?>
				</h2>

				<?php
				$wcu_upcoming_count = (int) $wcu_upcoming->post_count;
				$wcu_grid_class     = 'wcu-grid wcu-grid--' . (int) min( max( $wcu_upcoming_count, 1 ), 3 );
				?>
				<div class="<?php echo esc_attr( $wcu_grid_class ); ?>">
					<?php
					while ( $wcu_upcoming->have_posts() ) :
						$wcu_upcoming->the_post();
						get_template_part( 'template-parts/wordcamps/card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $wcu_past->have_posts() ) : ?>
			<section class="wcu-wordcamps-archive__section wcu-wordcamps-archive__section--past" aria-labelledby="wcu-wc-past-heading">
				<h2 id="wcu-wc-past-heading" class="wcu-wordcamps-archive__section-title">
					<?php esc_html_e( 'Past editions', 'wcuganda' ); ?>
				</h2>

				<div class="wcu-grid wcu-grid--3">
					<?php
					while ( $wcu_past->have_posts() ) :
						$wcu_past->the_post();
						get_template_part( 'template-parts/wordcamps/card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! $wcu_upcoming->have_posts() && ! $wcu_past->have_posts() ) : ?>
			<section class="wcu-wordcamps-archive__section">
				<div class="wcu-empty">
					<p class="wcu-empty__title"><?php esc_html_e( 'No WordCamps yet', 'wcuganda' ); ?></p>
					<p class="wcu-empty__desc">
						<?php esc_html_e( 'The first edition of WordCamp Uganda is being planned. Check back for date and venue announcements.', 'wcuganda' ); ?>
					</p>
				</div>
			</section>
		<?php endif; ?>

	</div>

</main>

<?php
get_footer();
