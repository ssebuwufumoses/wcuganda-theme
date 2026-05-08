<?php
/**
 * Homepage sponsors strip.
 *
 * Renders sponsor logos grouped by tier when the `wcu_sponsor` CPT is
 * registered (Phase 3+). Falls back to a simple "Become a sponsor" CTA.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_sponsors_by_tier = array();

if ( post_type_exists( 'wcu_sponsor' ) && taxonomy_exists( 'wcu_sponsor_tier' ) ) {
	$wcu_tiers = get_terms(
		array(
			'taxonomy'   => 'wcu_sponsor_tier',
			'hide_empty' => true,
			'orderby'    => 'term_order',
		)
	);

	if ( ! is_wp_error( $wcu_tiers ) ) {
		foreach ( $wcu_tiers as $wcu_tier ) {
			$wcu_query = new WP_Query(
				array(
					'post_type'      => 'wcu_sponsor',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'tax_query'      => array(
						array(
							'taxonomy' => 'wcu_sponsor_tier',
							'field'    => 'term_id',
							'terms'    => $wcu_tier->term_id,
						),
					),
				)
			);

			if ( $wcu_query->have_posts() ) {
				$wcu_logos = array();
				while ( $wcu_query->have_posts() ) {
					$wcu_query->the_post();
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
?>

<section class="wcu-sponsors section section--alt">
	<div class="container">

		<header class="section-header">
			<p class="section-eyebrow"><?php esc_html_e( 'Backed by', 'wcuganda' ); ?></p>
			<h2><?php esc_html_e( 'Sponsors who power us', 'wcuganda' ); ?></h2>
		</header>

		<?php if ( ! empty( $wcu_sponsors_by_tier ) ) : ?>
			<?php foreach ( $wcu_sponsors_by_tier as $wcu_tier_slug => $wcu_tier ) : ?>
				<div class="wcu-sponsors__tier wcu-sponsors__tier--<?php echo esc_attr( $wcu_tier_slug ); ?>">
					<h3 class="wcu-sponsors__tier-name">
						<?php echo esc_html( $wcu_tier['name'] ); ?>
					</h3>
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
		<?php else : ?>
			<p class="wcu-sponsors__empty">
				<?php esc_html_e( 'Sponsors are coming soon.', 'wcuganda' ); ?>
			</p>
		<?php endif; ?>

		<p class="wcu-sponsors__cta">
			<a class="wcu-btn wcu-btn--outline" href="<?php echo esc_url( home_url( '/sponsors/' ) ); ?>">
				<?php esc_html_e( 'Become a sponsor', 'wcuganda' ); ?>
				<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
			</a>
		</p>

	</div>
</section>
