<?php
/**
 * Homepage WordCamp banner.
 *
 * Prefers the next upcoming `wcu_wordcamp` CPT entry when one exists;
 * falls back to the Customizer-defined values. Hidden when neither
 * source provides a title.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Customizer-driven defaults (acts as fallback).
$wcu_wc_eyebrow  = get_theme_mod( 'wcu_wc_eyebrow', __( 'Save the date', 'wcuganda' ) );
$wcu_wc_title    = get_theme_mod( 'wcu_wc_title', '' );
$wcu_wc_date     = get_theme_mod( 'wcu_wc_date', '' );
$wcu_wc_location = get_theme_mod( 'wcu_wc_location', '' );
$wcu_wc_cta_text = get_theme_mod( 'wcu_wc_cta_text', __( 'Get your ticket', 'wcuganda' ) );
$wcu_wc_cta_url  = get_theme_mod( 'wcu_wc_cta_url', '#' );

// Prefer next upcoming wcu_wordcamp post when available.
if ( post_type_exists( 'wcu_wordcamp' ) ) {
	$wcu_wc_query = new WP_Query(
		array(
			'post_type'      => 'wcu_wordcamp',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'meta_key'       => '_wcu_wc_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_wcu_wc_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		)
	);

	if ( $wcu_wc_query->have_posts() ) {
		$wcu_wc_query->the_post();
		$wcu_wc_post_id = get_the_ID();

		$wcu_wc_title    = get_the_title();
		$wcu_wc_cta_url  = get_permalink();
		$wcu_wc_cta_text = __( 'See details', 'wcuganda' );

		$wcu_wc_post_date    = get_post_meta( $wcu_wc_post_id, '_wcu_wc_date', true );
		$wcu_wc_post_end     = get_post_meta( $wcu_wc_post_id, '_wcu_wc_end_date', true );
		$wcu_wc_post_venue   = get_post_meta( $wcu_wc_post_id, '_wcu_wc_venue', true );

		if ( $wcu_wc_post_date ) {
			$wcu_wc_ts     = strtotime( $wcu_wc_post_date );
			$wcu_wc_end_ts = $wcu_wc_post_end ? strtotime( $wcu_wc_post_end ) : $wcu_wc_ts;
			if ( $wcu_wc_ts && $wcu_wc_end_ts && $wcu_wc_end_ts !== $wcu_wc_ts ) {
				$wcu_wc_date = sprintf(
					/* translators: 1: start date, 2: end date. */
					__( '%1$s — %2$s', 'wcuganda' ),
					wp_date( 'j M', $wcu_wc_ts ),
					wp_date( 'j M Y', $wcu_wc_end_ts )
				);
			} elseif ( $wcu_wc_ts ) {
				$wcu_wc_date = wp_date( 'l, j M Y', $wcu_wc_ts );
			}
		}

		if ( $wcu_wc_post_venue ) {
			$wcu_wc_location = $wcu_wc_post_venue;
		}

		wp_reset_postdata();
	}
}

if ( '' === trim( (string) $wcu_wc_title ) ) {
	return;
}
?>

<section class="wcu-wordcamp-banner" aria-label="<?php esc_attr_e( 'WordCamp announcement', 'wcuganda' ); ?>">
	<div class="container wcu-wordcamp-banner__inner">

		<div class="wcu-wordcamp-banner__copy">
			<?php if ( ! empty( $wcu_wc_eyebrow ) ) : ?>
				<p class="wcu-wordcamp-banner__eyebrow"><?php echo esc_html( $wcu_wc_eyebrow ); ?></p>
			<?php endif; ?>

			<h2 class="wcu-wordcamp-banner__title"><?php echo esc_html( $wcu_wc_title ); ?></h2>

			<ul class="wcu-wordcamp-banner__meta">
				<?php if ( ! empty( $wcu_wc_date ) ) : ?>
					<li>
						<?php wcu_svg_icon( 'calendar', array( 'width' => 18, 'height' => 18 ) ); ?>
						<span><?php echo esc_html( $wcu_wc_date ); ?></span>
					</li>
				<?php endif; ?>
				<?php if ( ! empty( $wcu_wc_location ) ) : ?>
					<li>
						<?php wcu_svg_icon( 'map-pin', array( 'width' => 18, 'height' => 18 ) ); ?>
						<span><?php echo esc_html( $wcu_wc_location ); ?></span>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<?php if ( ! empty( $wcu_wc_cta_text ) && ! empty( $wcu_wc_cta_url ) ) : ?>
			<div class="wcu-wordcamp-banner__action">
				<a class="wcu-btn wcu-btn--lg" href="<?php echo esc_url( $wcu_wc_cta_url ); ?>">
					<?php echo esc_html( $wcu_wc_cta_text ); ?>
					<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
				</a>
			</div>
		<?php endif; ?>

	</div>
</section>
