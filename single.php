<?php
/**
 * Single blog post template.
 *
 * Hero with category eyebrow + title + author/date meta, optional
 * featured image, post content in narrow container, author bio block,
 * related posts (same category), comments.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$wcu_post_id    = get_the_ID();
	$wcu_categories = get_the_category( $wcu_post_id );
	$wcu_primary    = ! empty( $wcu_categories ) ? $wcu_categories[0] : null;
	$wcu_author_id  = (int) get_post_field( 'post_author', $wcu_post_id );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'wcu-blog-single' ); ?>>

		<header class="wcu-blog-single__header">
			<div class="container container--narrow">
				<a class="wcu-event-hero__back" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">
					<?php wcu_svg_icon( 'arrow-left', array( 'width' => 18, 'height' => 18 ) ); ?>
					<?php esc_html_e( 'All posts', 'wcuganda' ); ?>
				</a>

				<?php if ( $wcu_primary ) : ?>
					<p class="wcu-blog-single__category">
						<a href="<?php echo esc_url( get_category_link( $wcu_primary->term_id ) ); ?>">
							<?php echo esc_html( $wcu_primary->name ); ?>
						</a>
					</p>
				<?php endif; ?>

				<h1 class="wcu-blog-single__title"><?php the_title(); ?></h1>

				<p class="wcu-blog-single__meta">
					<?php
					echo get_avatar( $wcu_author_id, 32, '', '', array( 'class' => 'wcu-blog-single__avatar' ) );
					printf(
						/* translators: 1: author name, 2: published date. */
						esc_html__( 'By %1$s · %2$s', 'wcuganda' ),
						esc_html( get_the_author_meta( 'display_name', $wcu_author_id ) ),
						esc_html( get_the_date( 'j M Y', $wcu_post_id ) )
					);
					?>
				</p>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="container container--narrow">
				<figure class="wcu-blog-single__media">
					<?php the_post_thumbnail( 'wcu-hero', array( 'loading' => 'eager' ) ); ?>
				</figure>
			</div>
		<?php endif; ?>

		<div class="container container--narrow">
			<div class="wcu-blog-single__content">
				<?php
				the_content();

				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wcuganda' ),
						'after'  => '</div>',
					)
				);
				?>
			</div>

			<?php if ( has_tag() ) : ?>
				<div class="wcu-blog-single__tags">
					<?php
					the_tags(
						'<span class="wcu-blog-single__tags-label">' . esc_html__( 'Tagged:', 'wcuganda' ) . '</span> ',
						' '
					);
					?>
				</div>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/blog/author-bio', null, array( 'author_id' => $wcu_author_id ) ); ?>
		</div>

		<?php
		// Related posts — same primary category, exclude current.
		if ( $wcu_primary ) :
			$wcu_related = new WP_Query(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 3,
					'post_status'    => 'publish',
					'post__not_in'   => array( $wcu_post_id ),
					'category__in'   => array( $wcu_primary->term_id ),
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);

			if ( $wcu_related->have_posts() ) :
				?>
				<section class="wcu-blog-related section section--alt" aria-labelledby="wcu-blog-related-heading">
					<div class="container">
						<header class="section-header">
							<p class="section-eyebrow"><?php esc_html_e( 'Keep reading', 'wcuganda' ); ?></p>
							<h2 id="wcu-blog-related-heading">
								<?php
								printf(
									/* translators: %s: category name. */
									esc_html__( 'More from %s', 'wcuganda' ),
									esc_html( $wcu_primary->name )
								);
								?>
							</h2>
						</header>

						<?php
						$wcu_count = $wcu_related->post_count;
						$wcu_grid  = 'wcu-grid wcu-grid--' . (int) min( $wcu_count, 3 );
						?>
						<div class="<?php echo esc_attr( $wcu_grid ); ?>">
							<?php
							while ( $wcu_related->have_posts() ) :
								$wcu_related->the_post();
								get_template_part( 'template-parts/blog/card' );
							endwhile;
							wp_reset_postdata();
							?>
						</div>
					</div>
				</section>
				<?php
			endif;
		endif;
		?>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<div class="container container--narrow wcu-blog-single__comments">
				<?php comments_template(); ?>
			</div>
		<?php endif; ?>

	</article>

	<?php
endwhile;

get_footer();
