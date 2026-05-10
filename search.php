<?php
/**
 * Search results template.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$wcu_query = trim( get_search_query() );
?>

<main id="primary" class="site-main wcu-misc-page wcu-search-page">

	<header class="wcu-misc-page__header wcu-misc-page__header--block">
		<div class="container container--narrow">
			<p class="section-eyebrow"><?php esc_html_e( 'Search', 'wcuganda' ); ?></p>
			<h1 class="wcu-misc-page__title">
				<?php
				if ( '' !== $wcu_query ) {
					printf(
						/* translators: %s: search query (already escaped). */
						esc_html__( 'Results for: %s', 'wcuganda' ),
						'<span class="wcu-search-page__query">' . esc_html( $wcu_query ) . '</span>'
					);
				} else {
					esc_html_e( 'Search the site', 'wcuganda' );
				}
				?>
			</h1>
			<div class="wcu-misc-page__search">
				<?php get_search_form(); ?>
			</div>
		</div>
	</header>

	<div class="container">

		<?php if ( have_posts() ) :
			$wcu_post_count = (int) ( $GLOBALS['wp_query']->post_count ?? 0 );
			$wcu_grid_class = 'wcu-grid wcu-grid--' . (int) min( max( $wcu_post_count, 1 ), 3 );
			?>
			<section class="wcu-blog-archive__section">
				<p class="wcu-search-page__count">
					<?php
					printf(
						/* translators: %d: result count. */
						esc_html( _n( '%d result', '%d results', (int) $GLOBALS['wp_query']->found_posts, 'wcuganda' ) ),
						(int) $GLOBALS['wp_query']->found_posts
					);
					?>
				</p>

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
					<p class="wcu-empty__title"><?php esc_html_e( 'No results found', 'wcuganda' ); ?></p>
					<p class="wcu-empty__desc">
						<?php esc_html_e( 'Try different keywords, or browse the events, chapters, members, or blog instead.', 'wcuganda' ); ?>
					</p>
				</div>
			</section>
		<?php endif; ?>

	</div>

</main>

<?php
get_footer();
