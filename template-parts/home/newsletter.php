<?php
/**
 * Homepage newsletter signup.
 *
 * Renders the MailPoet shortcode if one is configured in the Customizer;
 * otherwise renders a non-functional fallback form (a TODO until MailPoet
 * is wired up). This keeps the visual design intact even before backend
 * integration is complete.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_nl_heading    = get_theme_mod( 'wcu_newsletter_heading', __( 'Stay in the loop', 'wcuganda' ) );
$wcu_nl_subheading = get_theme_mod( 'wcu_newsletter_subheading', __( 'Get monthly highlights, upcoming events, and contributor opportunities in your inbox.', 'wcuganda' ) );
$wcu_nl_button     = get_theme_mod( 'wcu_newsletter_button', __( 'Subscribe', 'wcuganda' ) );
$wcu_nl_shortcode  = get_theme_mod( 'wcu_newsletter_shortcode', '' );
?>

<section class="wcu-newsletter section section--dark">
	<div class="container container--narrow">

		<header class="section-header">
			<h2 class="wcu-newsletter__heading"><?php echo esc_html( $wcu_nl_heading ); ?></h2>
			<?php if ( ! empty( $wcu_nl_subheading ) ) : ?>
				<div class="wcu-newsletter__subheading">
					<?php echo wp_kses_post( wpautop( $wcu_nl_subheading ) ); ?>
				</div>
			<?php endif; ?>
		</header>

		<div class="wcu-newsletter__form">
			<?php if ( ! empty( $wcu_nl_shortcode ) && shortcode_exists( 'mailpoet_form' ) ) : ?>
				<?php echo do_shortcode( wp_kses_post( $wcu_nl_shortcode ) ); ?>
			<?php else : ?>
				<form class="wcu-form wcu-form--inline"
					action="#"
					method="post"
					aria-label="<?php esc_attr_e( 'Newsletter signup', 'wcuganda' ); ?>">
					<label class="screen-reader-text" for="wcu-newsletter-email">
						<?php esc_html_e( 'Your email address', 'wcuganda' ); ?>
					</label>
					<input id="wcu-newsletter-email"
						type="email"
						name="email"
						autocomplete="email"
						placeholder="<?php esc_attr_e( 'you@example.com', 'wcuganda' ); ?>"
						required>
					<button type="submit" class="wcu-btn wcu-btn--accent">
						<?php echo esc_html( $wcu_nl_button ); ?>
						<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
					</button>
				</form>
				<p class="wcu-newsletter__note">
					<?php esc_html_e( 'We\'ll only email a handful of times a year. Unsubscribe any time.', 'wcuganda' ); ?>
				</p>
			<?php endif; ?>
		</div>

	</div>
</section>
