<?php
/**
 * Chapters archive — list of all WCUganda chapters as cards.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main wcu-chapters-archive">

	<header class="wcu-chapters-archive__header">
		<div class="container">
			<p class="section-eyebrow"><?php esc_html_e( 'Local communities', 'wcuganda' ); ?></p>
			<h1 class="wcu-chapters-archive__title"><?php post_type_archive_title(); ?></h1>
			<p class="wcu-chapters-archive__lead">
				<?php esc_html_e( 'WordPress Community Uganda runs chapters across the country. Each one hosts regular meetups, workshops, and contributor days. Find one near you.', 'wcuganda' ); ?>
			</p>
		</div>
	</header>

	<div class="container">

		<?php if ( have_posts() ) :
			$wcu_chapter_count = (int) ( $GLOBALS['wp_query']->found_posts ?? 0 );
			$wcu_grid_class    = 'wcu-grid wcu-grid--' . (int) min( max( $wcu_chapter_count, 1 ), 3 );
			?>
			<section class="wcu-chapters-archive__section">
				<div class="<?php echo esc_attr( $wcu_grid_class ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/chapters/card' );
					endwhile;
					wp_reset_postdata();
					?>
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
			</section>

		<?php else : ?>

			<section class="wcu-chapters-archive__section">
				<div class="wcu-events-archive__empty">
					<p class="wcu-empty__title"><?php esc_html_e( 'No chapters yet', 'wcuganda' ); ?></p>
					<p class="wcu-empty__desc">
						<?php esc_html_e( 'New chapters launch a few times a year. Want to start one in your city? Get in touch.', 'wcuganda' ); ?>
					</p>
				</div>
			</section>

		<?php endif; ?>

	</div>

</main>

<?php
get_footer();
