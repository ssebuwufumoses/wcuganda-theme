<?php
/**
 * Homepage map embed.
 *
 * Hidden when no Google Maps embed URL is set.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_map_embed = get_theme_mod( 'wcu_map_embed', '' );

if ( '' === trim( $wcu_map_embed ) ) {
	return;
}

$wcu_map_heading = get_theme_mod( 'wcu_map_heading', __( 'Find a chapter near you', 'wcuganda' ) );
?>

<section class="wcu-map section">
	<div class="container">

		<header class="section-header">
			<p class="section-eyebrow"><?php esc_html_e( 'On the ground', 'wcuganda' ); ?></p>
			<h2><?php echo esc_html( $wcu_map_heading ); ?></h2>
		</header>

		<div class="wcu-map__embed embed-responsive">
			<iframe src="<?php echo esc_url( $wcu_map_embed ); ?>"
				title="<?php echo esc_attr( $wcu_map_heading ); ?>"
				loading="lazy"
				referrerpolicy="no-referrer-when-downgrade"
				allowfullscreen></iframe>
		</div>

	</div>
</section>
