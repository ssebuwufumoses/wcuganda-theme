<?php
/**
 * Single chapter template.
 *
 * Solid blue hero with chapter name + city; two-column body with
 * description + sticky meta sidebar (organizers, contact, Meetup CTA).
 * Below: upcoming events for this chapter (matched by post title to
 * the wcu_chapter_tax term name) and the optional Meetup.com embed.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$wcu_chapter_id        = get_the_ID();
	$wcu_chapter_city      = get_post_meta( $wcu_chapter_id, '_wcu_chapter_city', true );
	$wcu_chapter_meetup    = get_post_meta( $wcu_chapter_id, '_wcu_chapter_meetup_url', true );
	$wcu_chapter_embed     = get_post_meta( $wcu_chapter_id, '_wcu_chapter_meetup_embed', true );
	$wcu_chapter_email     = get_post_meta( $wcu_chapter_id, '_wcu_chapter_email', true );
	$wcu_chapter_organizers = get_post_meta( $wcu_chapter_id, '_wcu_chapter_organizers', true );
	$wcu_chapter_founded   = get_post_meta( $wcu_chapter_id, '_wcu_chapter_founded', true );

	// Look up the matching wcu_chapter_tax term by post title (convention).
	$wcu_chapter_term = get_term_by( 'name', get_the_title(), 'wcu_chapter_tax' );

	$wcu_organizers_list = array();
	if ( ! empty( $wcu_chapter_organizers ) ) {
		$wcu_organizers_list = array_filter( array_map( 'trim', explode( ',', (string) $wcu_chapter_organizers ) ) );
	}
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'wcu-chapter-single' ); ?>>

		<header class="wcu-chapter-hero">
			<div class="container">
				<a class="wcu-event-hero__back" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_chapter' ) ); ?>">
					<?php wcu_svg_icon( 'arrow-left', array( 'width' => 18, 'height' => 18 ) ); ?>
					<?php esc_html_e( 'All chapters', 'wcuganda' ); ?>
				</a>

				<?php if ( ! empty( $wcu_chapter_city ) ) : ?>
					<p class="wcu-chapter-hero__city">
						<?php wcu_svg_icon( 'map-pin', array( 'width' => 18, 'height' => 18 ) ); ?>
						<?php echo esc_html( $wcu_chapter_city ); ?>
					</p>
				<?php endif; ?>

				<h1 class="wcu-chapter-hero__title"><?php the_title(); ?></h1>
			</div>
		</header>

		<div class="container wcu-chapter-single__inner">

			<div class="wcu-chapter-single__main">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="wcu-event-single__media">
						<?php the_post_thumbnail( 'wcu-hero', array( 'loading' => 'eager' ) ); ?>
					</figure>
				<?php endif; ?>

				<div class="wcu-event-single__content">
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
			</div>

			<aside class="wcu-event-single__sidebar" aria-label="<?php esc_attr_e( 'Chapter details', 'wcuganda' ); ?>">

				<?php if ( ! empty( $wcu_chapter_meetup ) ) : ?>
					<a class="wcu-btn wcu-btn--block wcu-btn--lg" href="<?php echo esc_url( $wcu_chapter_meetup ); ?>"
						rel="nofollow noopener" target="_blank">
						<?php esc_html_e( 'Join on Meetup.com', 'wcuganda' ); ?>
						<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
					</a>
				<?php endif; ?>

				<dl class="wcu-event-meta">
					<?php if ( ! empty( $wcu_chapter_city ) ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'City', 'wcuganda' ); ?></dt>
							<dd><?php echo esc_html( $wcu_chapter_city ); ?></dd>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $wcu_chapter_founded ) ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Founded', 'wcuganda' ); ?></dt>
							<dd><?php echo esc_html( $wcu_chapter_founded ); ?></dd>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $wcu_organizers_list ) ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php echo esc_html( _n( 'Organizer', 'Organizers', count( $wcu_organizers_list ), 'wcuganda' ) ); ?></dt>
							<dd>
								<ul class="wcu-chapter-organizers">
									<?php foreach ( $wcu_organizers_list as $wcu_organizer ) : ?>
										<li><?php echo esc_html( $wcu_organizer ); ?></li>
									<?php endforeach; ?>
								</ul>
							</dd>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $wcu_chapter_email ) ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Contact', 'wcuganda' ); ?></dt>
							<dd>
								<a href="mailto:<?php echo esc_attr( $wcu_chapter_email ); ?>">
									<?php echo esc_html( $wcu_chapter_email ); ?>
								</a>
							</dd>
						</div>
					<?php endif; ?>
				</dl>

			</aside>

		</div>

		<?php
		// Upcoming events at this chapter — match by post title → term name.
		if ( $wcu_chapter_term && ! is_wp_error( $wcu_chapter_term ) ) :
			$wcu_chapter_events = new WP_Query(
				array(
					'post_type'      => 'wcu_event',
					'posts_per_page' => 6,
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
					'tax_query'      => array(
						array(
							'taxonomy' => 'wcu_chapter_tax',
							'field'    => 'term_id',
							'terms'    => $wcu_chapter_term->term_id,
						),
					),
				)
			);

			if ( $wcu_chapter_events->have_posts() ) : ?>
				<section class="wcu-chapter-events section section--alt" aria-labelledby="wcu-chapter-events-heading">
					<div class="container">
						<header class="section-header">
							<p class="section-eyebrow"><?php esc_html_e( 'What\'s next here', 'wcuganda' ); ?></p>
							<h2 id="wcu-chapter-events-heading">
								<?php
								printf(
									/* translators: %s: chapter name. */
									esc_html__( 'Upcoming in %s', 'wcuganda' ),
									esc_html( get_the_title() )
								);
								?>
							</h2>
						</header>

						<?php
						$wcu_count      = $wcu_chapter_events->post_count;
						$wcu_grid_class = 'wcu-grid wcu-grid--' . (int) min( $wcu_count, 3 );
						?>
						<div class="<?php echo esc_attr( $wcu_grid_class ); ?>">
							<?php
							while ( $wcu_chapter_events->have_posts() ) :
								$wcu_chapter_events->the_post();
								get_template_part( 'template-parts/events/card', null, array( 'card' => wcu_event_to_card_array( get_the_ID() ) ) );
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

		<?php if ( ! empty( $wcu_chapter_embed ) ) : ?>
			<section class="wcu-chapter-meetup section" aria-label="<?php esc_attr_e( 'Meetup.com embed', 'wcuganda' ); ?>">
				<div class="container">
					<header class="section-header">
						<p class="section-eyebrow"><?php esc_html_e( 'On Meetup.com', 'wcuganda' ); ?></p>
						<h2><?php esc_html_e( 'Live from the group', 'wcuganda' ); ?></h2>
					</header>
					<div class="wcu-chapter-meetup__embed">
						<?php
						echo wp_kses(
							$wcu_chapter_embed,
							array(
								'iframe' => array(
									'src'             => array(),
									'width'           => array(),
									'height'          => array(),
									'frameborder'     => array(),
									'scrolling'       => array(),
									'marginheight'    => array(),
									'marginwidth'     => array(),
									'allowfullscreen' => array(),
									'loading'         => array(),
									'title'           => array(),
									'referrerpolicy'  => array(),
									'style'           => array(),
								),
							)
						);
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>

	</article>

	<?php
endwhile;

get_footer();
