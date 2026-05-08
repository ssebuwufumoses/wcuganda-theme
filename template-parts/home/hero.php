<?php
/**
 * Homepage hero.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_hero_eyebrow      = get_theme_mod( 'wcu_hero_eyebrow', __( 'WordPress Community Uganda', 'wcuganda' ) );
$wcu_hero_heading      = get_theme_mod( 'wcu_hero_heading', __( 'Build the open web together.', 'wcuganda' ) );
$wcu_hero_subheading   = get_theme_mod( 'wcu_hero_subheading', __( 'Meetups, WordCamps, and contributor days across Uganda — powered by makers like you.', 'wcuganda' ) );
$wcu_cta1_text         = get_theme_mod( 'wcu_hero_cta_primary_text', __( 'Join the community', 'wcuganda' ) );
$wcu_cta1_url          = get_theme_mod( 'wcu_hero_cta_primary_url', '/get-involved/' );
$wcu_cta2_text         = get_theme_mod( 'wcu_hero_cta_secondary_text', __( 'Upcoming events', 'wcuganda' ) );
$wcu_cta2_url          = get_theme_mod( 'wcu_hero_cta_secondary_url', '/events/' );
$wcu_hero_image_id     = (int) get_theme_mod( 'wcu_hero_image', 0 );
$wcu_hero_image_url    = $wcu_hero_image_id ? wp_get_attachment_image_url( $wcu_hero_image_id, 'wcu-hero' ) : '';
$wcu_hero_classes      = array( 'wcu-hero' );

if ( ! empty( $wcu_hero_image_url ) ) {
	$wcu_hero_classes[] = 'wcu-hero--has-image';
}
?>

<section class="<?php echo esc_attr( implode( ' ', $wcu_hero_classes ) ); ?>"
	<?php if ( ! empty( $wcu_hero_image_url ) ) : ?>
	style="--wcu-hero-image: url('<?php echo esc_url( $wcu_hero_image_url ); ?>');"
	<?php endif; ?>>

	<div class="wcu-hero__inner container">

		<?php if ( ! empty( $wcu_hero_eyebrow ) ) : ?>
			<p class="wcu-hero__eyebrow"><?php echo esc_html( $wcu_hero_eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $wcu_hero_heading ) ) : ?>
			<h1 class="wcu-hero__heading"><?php echo esc_html( $wcu_hero_heading ); ?></h1>
		<?php endif; ?>

		<?php if ( ! empty( $wcu_hero_subheading ) ) : ?>
			<div class="wcu-hero__subheading">
				<?php echo wp_kses_post( wpautop( $wcu_hero_subheading ) ); ?>
			</div>
		<?php endif; ?>

		<div class="wcu-hero__actions">
			<?php if ( ! empty( $wcu_cta1_text ) && ! empty( $wcu_cta1_url ) ) : ?>
				<a class="wcu-btn wcu-btn--lg" href="<?php echo esc_url( $wcu_cta1_url ); ?>">
					<?php echo esc_html( $wcu_cta1_text ); ?>
					<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $wcu_cta2_text ) && ! empty( $wcu_cta2_url ) ) : ?>
				<a class="wcu-btn wcu-btn--lg wcu-btn--outline-light" href="<?php echo esc_url( $wcu_cta2_url ); ?>">
					<?php echo esc_html( $wcu_cta2_text ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div>

</section>
