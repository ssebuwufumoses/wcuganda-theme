<?php
/**
 * Single WordCamp template.
 *
 * Solid green hero with year + name + dates + venue + ticket CTA;
 * full-width content section; speakers section (queries wcu_member with
 * the speaker meta flag); sponsors section (queries active wcu_sponsor
 * posts grouped by tier).
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$wcu_wc_id        = get_the_ID();
	$wcu_wc_year      = get_post_meta( $wcu_wc_id, '_wcu_wc_year', true );
	$wcu_wc_date      = get_post_meta( $wcu_wc_id, '_wcu_wc_date', true );
	$wcu_wc_end_date  = get_post_meta( $wcu_wc_id, '_wcu_wc_end_date', true );
	$wcu_wc_venue     = get_post_meta( $wcu_wc_id, '_wcu_wc_venue', true );
	$wcu_wc_address   = get_post_meta( $wcu_wc_id, '_wcu_wc_address', true );
	$wcu_wc_ticket    = get_post_meta( $wcu_wc_id, '_wcu_wc_ticket_url', true );
	$wcu_wc_attendees = get_post_meta( $wcu_wc_id, '_wcu_wc_attendee_count', true );

	$wcu_wc_date_ts = $wcu_wc_date ? strtotime( $wcu_wc_date ) : 0;
	$wcu_wc_end_ts  = $wcu_wc_end_date ? strtotime( $wcu_wc_end_date ) : $wcu_wc_date_ts;
	$wcu_wc_today   = strtotime( current_time( 'Y-m-d' ) );
	$wcu_wc_is_past = $wcu_wc_end_ts && $wcu_wc_end_ts < $wcu_wc_today;

	$wcu_wc_date_label = '';
	if ( $wcu_wc_date_ts ) {
		if ( $wcu_wc_end_ts && $wcu_wc_end_ts !== $wcu_wc_date_ts ) {
			$wcu_wc_date_label = sprintf(
				/* translators: 1: start date, 2: end date. */
				__( '%1$s — %2$s', 'wcuganda' ),
				wp_date( 'j M', $wcu_wc_date_ts ),
				wp_date( 'j M Y', $wcu_wc_end_ts )
			);
		} else {
			$wcu_wc_date_label = wp_date( 'l, j M Y', $wcu_wc_date_ts );
		}
	}
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'wcu-wordcamp-single' ); ?>>

		<header class="wcu-wordcamp-hero">
			<div class="container">
				<a class="wcu-event-hero__back" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_wordcamp' ) ); ?>">
					<?php wcu_svg_icon( 'arrow-left', array( 'width' => 18, 'height' => 18 ) ); ?>
					<?php esc_html_e( 'All WordCamps', 'wcuganda' ); ?>
				</a>

				<?php if ( $wcu_wc_year ) : ?>
					<p class="wcu-wordcamp-hero__year"><?php echo esc_html( $wcu_wc_year ); ?></p>
				<?php endif; ?>

				<h1 class="wcu-wordcamp-hero__title"><?php the_title(); ?></h1>

				<?php if ( $wcu_wc_is_past ) : ?>
					<p class="wcu-wordcamp-hero__past-badge">
						<?php esc_html_e( 'Past event', 'wcuganda' ); ?>
					</p>
				<?php endif; ?>

				<ul class="wcu-wordcamp-hero__meta">
					<?php if ( $wcu_wc_date_label ) : ?>
						<li>
							<?php wcu_svg_icon( 'calendar', array( 'width' => 16, 'height' => 16 ) ); ?>
							<span><?php echo esc_html( $wcu_wc_date_label ); ?></span>
						</li>
					<?php endif; ?>
					<?php if ( $wcu_wc_venue ) : ?>
						<li>
							<?php wcu_svg_icon( 'map-pin', array( 'width' => 16, 'height' => 16 ) ); ?>
							<span><?php echo esc_html( $wcu_wc_venue ); ?></span>
						</li>
					<?php endif; ?>
					<?php if ( $wcu_wc_attendees && $wcu_wc_is_past ) : ?>
						<li>
							<?php
							printf(
								/* translators: %s: attendee count. */
								esc_html__( '%s attendees', 'wcuganda' ),
								esc_html( number_format_i18n( (int) $wcu_wc_attendees ) )
							);
							?>
						</li>
					<?php endif; ?>
				</ul>

				<?php if ( $wcu_wc_ticket && ! $wcu_wc_is_past ) : ?>
					<div class="wcu-wordcamp-hero__cta">
						<a class="wcu-btn wcu-btn--lg" href="<?php echo esc_url( $wcu_wc_ticket ); ?>"
							rel="nofollow noopener" target="_blank">
							<?php esc_html_e( 'Get tickets', 'wcuganda' ); ?>
							<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="container">
				<figure class="wcu-wordcamp-single__media">
					<?php the_post_thumbnail( 'wcu-hero', array( 'loading' => 'eager' ) ); ?>
				</figure>
			</div>
		<?php endif; ?>

		<?php if ( get_the_content() ) : ?>
			<section class="wcu-wordcamp-single__content section">
				<div class="container container--narrow">
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
			</section>
		<?php endif; ?>

		<?php
		// =====================================================================
		// Speakers — wcu_member posts with the speaker meta flag.
		// =====================================================================
		$wcu_speakers = new WP_Query(
			array(
				'post_type'      => 'wcu_member',
				'posts_per_page' => 12,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'   => '_wcu_member_speaker',
						'value' => '1',
					),
				),
				'orderby'        => 'rand',
			)
		);

		if ( $wcu_speakers->have_posts() ) :
			?>
			<section class="wcu-wordcamp-speakers section section--alt" aria-labelledby="wcu-wc-speakers-heading">
				<div class="container">
					<header class="section-header">
						<p class="section-eyebrow"><?php esc_html_e( 'Voices', 'wcuganda' ); ?></p>
						<h2 id="wcu-wc-speakers-heading"><?php esc_html_e( 'Speakers', 'wcuganda' ); ?></h2>
					</header>

					<?php
					$wcu_speaker_count = (int) min( $wcu_speakers->post_count, 4 );
					$wcu_grid_class    = 'wcu-grid wcu-grid--' . (int) $wcu_speaker_count;
					?>
					<div class="<?php echo esc_attr( $wcu_grid_class ); ?>">
						<?php
						while ( $wcu_speakers->have_posts() ) :
							$wcu_speakers->the_post();
							get_template_part( 'template-parts/members/card' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>

					<p class="wcu-wordcamp-speakers__cta">
						<a class="wcu-btn wcu-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_member' ) ); ?>">
							<?php esc_html_e( 'Browse all members', 'wcuganda' ); ?>
							<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
						</a>
					</p>
				</div>
			</section>
			<?php
		endif;
		?>

		<?php
		// =====================================================================
		// Sponsors — active wcu_sponsor posts grouped by tier.
		// =====================================================================
		$wcu_sponsors_by_tier = array();

		if ( taxonomy_exists( 'wcu_sponsor_tier' ) ) {
			$wcu_tiers = get_terms(
				array(
					'taxonomy'   => 'wcu_sponsor_tier',
					'hide_empty' => true,
				)
			);

			if ( ! is_wp_error( $wcu_tiers ) ) {
				foreach ( $wcu_tiers as $wcu_tier ) {
					$wcu_q = new WP_Query(
						array(
							'post_type'      => 'wcu_sponsor',
							'posts_per_page' => -1,
							'post_status'    => 'publish',
							'meta_query'     => array(
								array(
									'key'   => '_wcu_sponsor_active',
									'value' => '1',
								),
							),
							'tax_query'      => array(
								array(
									'taxonomy' => 'wcu_sponsor_tier',
									'field'    => 'term_id',
									'terms'    => $wcu_tier->term_id,
								),
							),
						)
					);

					if ( $wcu_q->have_posts() ) {
						$wcu_logos = array();
						while ( $wcu_q->have_posts() ) {
							$wcu_q->the_post();
							$wcu_logos[] = array(
								'name' => get_the_title(),
								'url'  => get_post_meta( get_the_ID(), '_wcu_sponsor_url', true ),
								'logo' => get_the_post_thumbnail_url( get_the_ID(), 'medium' ),
							);
						}
						wp_reset_postdata();

						if ( ! empty( $wcu_logos ) ) {
							$wcu_sponsors_by_tier[ $wcu_tier->slug ] = array(
								'name'  => $wcu_tier->name,
								'logos' => $wcu_logos,
							);
						}
					}
				}
			}
		}

		if ( ! empty( $wcu_sponsors_by_tier ) ) :
			?>
			<section class="wcu-wordcamp-sponsors section" aria-labelledby="wcu-wc-sponsors-heading">
				<div class="container">
					<header class="section-header">
						<p class="section-eyebrow"><?php esc_html_e( 'Backed by', 'wcuganda' ); ?></p>
						<h2 id="wcu-wc-sponsors-heading"><?php esc_html_e( 'Sponsors', 'wcuganda' ); ?></h2>
					</header>

					<?php foreach ( $wcu_sponsors_by_tier as $wcu_tier_slug => $wcu_tier ) : ?>
						<div class="wcu-sponsors__tier wcu-sponsors__tier--<?php echo esc_attr( $wcu_tier_slug ); ?>">
							<h3 class="wcu-sponsors__tier-name"><?php echo esc_html( $wcu_tier['name'] ); ?></h3>
							<ul class="wcu-sponsors__logos">
								<?php foreach ( $wcu_tier['logos'] as $wcu_logo ) : ?>
									<li class="wcu-sponsors__logo">
										<?php if ( ! empty( $wcu_logo['url'] ) ) : ?>
											<a href="<?php echo esc_url( $wcu_logo['url'] ); ?>" rel="nofollow noopener" target="_blank">
										<?php endif; ?>
										<?php if ( ! empty( $wcu_logo['logo'] ) ) : ?>
											<img src="<?php echo esc_url( $wcu_logo['logo'] ); ?>"
												alt="<?php echo esc_attr( $wcu_logo['name'] ); ?>"
												loading="lazy">
										<?php else : ?>
											<span class="wcu-sponsors__name"><?php echo esc_html( $wcu_logo['name'] ); ?></span>
										<?php endif; ?>
										<?php if ( ! empty( $wcu_logo['url'] ) ) : ?>
											</a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>

					<p class="wcu-sponsors__cta">
						<a class="wcu-btn wcu-btn--outline" href="<?php echo esc_url( home_url( '/sponsors/' ) ); ?>">
							<?php esc_html_e( 'Become a sponsor', 'wcuganda' ); ?>
							<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
						</a>
					</p>
				</div>
			</section>
			<?php
		endif;
		?>

	</article>

	<?php
endwhile;

get_footer();
