<?php
/**
 * Members directory archive.
 *
 * Light header band, chapter/role/skill filter chips, then a 4-up grid
 * of member cards. Pagination via the_posts_pagination().
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main wcu-members-archive">

	<header class="wcu-members-archive__header">
		<div class="container">
			<p class="section-eyebrow"><?php esc_html_e( 'The community', 'wcuganda' ); ?></p>
			<h1 class="wcu-members-archive__title"><?php post_type_archive_title(); ?></h1>
			<p class="wcu-members-archive__lead">
				<?php esc_html_e( 'Organizers, speakers, contributors, and learners building WordPress in Uganda. Filter by chapter, role, or skill to find people you can collaborate with.', 'wcuganda' ); ?>
			</p>
		</div>
	</header>

	<div class="container">

		<?php get_template_part( 'template-parts/members/filters' ); ?>

		<section class="wcu-members-archive__section">

			<?php if ( have_posts() ) :
				$wcu_member_count = (int) ( $GLOBALS['wp_query']->found_posts ?? 0 );
				$wcu_grid_class   = 'wcu-grid wcu-grid--' . (int) min( max( $wcu_member_count, 1 ), 4 );
				?>
				<div class="<?php echo esc_attr( $wcu_grid_class ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/members/card' );
					endwhile;
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

			<?php else : ?>
				<div class="wcu-events-archive__empty">
					<p class="wcu-empty__title"><?php esc_html_e( 'No matching members', 'wcuganda' ); ?></p>
					<p class="wcu-empty__desc">
						<?php esc_html_e( 'Try clearing the filters or browse all chapters.', 'wcuganda' ); ?>
					</p>
				</div>
			<?php endif; ?>

		</section>

	</div>

</main>

<?php
get_footer();
