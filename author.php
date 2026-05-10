<?php
/**
 * Author archive template — author bio block at top, then their posts.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$wcu_author_id = get_queried_object_id();
?>

<main id="primary" class="site-main wcu-blog-archive wcu-author-archive">

	<header class="wcu-blog-archive__header">
		<div class="container container--narrow">
			<a class="wcu-event-hero__back" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">
				<?php wcu_svg_icon( 'arrow-left', array( 'width' => 18, 'height' => 18 ) ); ?>
				<?php esc_html_e( 'All posts', 'wcuganda' ); ?>
			</a>

			<?php get_template_part( 'template-parts/blog/author-bio', null, array( 'author_id' => $wcu_author_id ) ); ?>
		</div>
	</header>

	<div class="container">

		<?php if ( have_posts() ) :
			$wcu_post_count = (int) ( $GLOBALS['wp_query']->post_count ?? 0 );
			$wcu_grid_class = 'wcu-grid wcu-grid--' . (int) min( max( $wcu_post_count, 1 ), 3 );
			?>
			<section class="wcu-blog-archive__section">
				<header class="section-header">
					<h2><?php esc_html_e( 'Posts', 'wcuganda' ); ?></h2>
				</header>

				<div class="<?php echo esc_attr( $wcu_grid_class ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/blog/card' );
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
			</section>

		<?php else : ?>
			<section class="wcu-blog-archive__section">
				<div class="wcu-empty">
					<p class="wcu-empty__title"><?php esc_html_e( 'No posts yet', 'wcuganda' ); ?></p>
					<p class="wcu-empty__desc">
						<?php esc_html_e( 'This author has not published any posts yet.', 'wcuganda' ); ?>
					</p>
				</div>
			</section>
		<?php endif; ?>

	</div>

</main>

<?php
get_footer();
