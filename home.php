<?php
/**
 * Blog index — used when "Posts page" is set in Settings → Reading,
 * or as the fallback for the posts archive when index.php is not used.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main wcu-blog-archive">

	<header class="wcu-blog-archive__header">
		<div class="container">
			<p class="section-eyebrow"><?php esc_html_e( 'From the community', 'wcuganda' ); ?></p>
			<h1 class="wcu-blog-archive__title">
				<?php
				$wcu_blog_page_id = (int) get_option( 'page_for_posts' );
				if ( $wcu_blog_page_id ) {
					echo esc_html( get_the_title( $wcu_blog_page_id ) );
				} else {
					esc_html_e( 'Blog', 'wcuganda' );
				}
				?>
			</h1>
			<p class="wcu-blog-archive__lead">
				<?php esc_html_e( 'Stories, tutorials, and community updates from contributors across Uganda.', 'wcuganda' ); ?>
			</p>
		</div>
	</header>

	<div class="container">

		<?php if ( have_posts() ) :
			$wcu_post_count = (int) ( $GLOBALS['wp_query']->post_count ?? 0 );
			$wcu_grid_class = 'wcu-grid wcu-grid--' . (int) min( max( $wcu_post_count, 1 ), 3 );
			?>
			<section class="wcu-blog-archive__section">
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
						<?php esc_html_e( 'The first post is being written. Subscribe below to be notified when it goes live.', 'wcuganda' ); ?>
					</p>
				</div>
			</section>
		<?php endif; ?>

	</div>

</main>

<?php
get_footer();
