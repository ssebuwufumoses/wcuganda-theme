<?php
/**
 * Homepage WordCamp banner.
 *
 * Hidden when no title is set in the Customizer.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_wc_title = get_theme_mod( 'wcu_wc_title', '' );

if ( '' === trim( $wcu_wc_title ) ) {
	return;
}

$wcu_wc_eyebrow  = get_theme_mod( 'wcu_wc_eyebrow', __( 'Save the date', 'wcuganda' ) );
$wcu_wc_date     = get_theme_mod( 'wcu_wc_date', '' );
$wcu_wc_location = get_theme_mod( 'wcu_wc_location', '' );
$wcu_wc_cta_text = get_theme_mod( 'wcu_wc_cta_text', __( 'Get your ticket', 'wcuganda' ) );
$wcu_wc_cta_url  = get_theme_mod( 'wcu_wc_cta_url', '#' );
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
				<a class="wcu-btn wcu-btn--lg wcu-btn--accent" href="<?php echo esc_url( $wcu_wc_cta_url ); ?>">
					<?php echo esc_html( $wcu_wc_cta_text ); ?>
					<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
				</a>
			</div>
		<?php endif; ?>

	</div>
</section>
