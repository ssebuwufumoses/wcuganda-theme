<?php
/**
 * Site footer.
 *
 * Closes the page wrapper, renders the footer columns, social links, legal
 * menu, and copyright bar.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_year         = (int) gmdate( 'Y' );
$wcu_social_links = wcu_get_social_links();
?>

	</div><!-- #content.site-content -->

	<footer id="colophon" class="site-footer" role="contentinfo">

		<div class="site-footer__top">

			<div class="site-footer__column site-footer__column--brand">
				<div class="site-footer__brand">
					<?php wcu_svg_icon( 'wordpress', array( 'width' => 32, 'height' => 32, 'class' => 'site-footer__brand-mark' ) ); ?>
					<p class="site-footer__brand-name"><?php bloginfo( 'name' ); ?></p>
				</div>
				<p class="site-footer__about">
					<?php
					$wcu_footer_about = get_theme_mod(
						'wcu_footer_about',
						__( 'WordPress Community Uganda is the local hub for organizers, contributors, and learners building the open web together.', 'wcuganda' )
					);
					echo esc_html( $wcu_footer_about );
					?>
				</p>

				<?php if ( ! empty( $wcu_social_links ) ) : ?>
					<ul class="site-footer__social" aria-label="<?php esc_attr_e( 'Social links', 'wcuganda' ); ?>">
						<?php foreach ( $wcu_social_links as $wcu_network => $wcu_url ) : ?>
							<li>
								<a href="<?php echo esc_url( $wcu_url ); ?>" rel="me noopener" target="_blank">
									<?php
									wcu_svg_icon(
										$wcu_network,
										array(
											'width'  => 18,
											'height' => 18,
											'title'  => ucfirst( $wcu_network ),
										)
									);
									?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="site-footer__column">
				<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
					<?php dynamic_sidebar( 'footer-1' ); ?>
				<?php elseif ( has_nav_menu( 'footer-1' ) ) : ?>
					<h2 class="site-footer__heading"><?php esc_html_e( 'About', 'wcuganda' ); ?></h2>
					<?php wcu_render_footer_menu( 'footer-1' ); ?>
				<?php endif; ?>
			</div>

			<div class="site-footer__column">
				<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
					<?php dynamic_sidebar( 'footer-2' ); ?>
				<?php elseif ( has_nav_menu( 'footer-2' ) ) : ?>
					<h2 class="site-footer__heading"><?php esc_html_e( 'Get Involved', 'wcuganda' ); ?></h2>
					<?php wcu_render_footer_menu( 'footer-2' ); ?>
				<?php endif; ?>
			</div>

			<div class="site-footer__column">
				<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
					<?php dynamic_sidebar( 'footer-3' ); ?>
				<?php elseif ( has_nav_menu( 'footer-3' ) ) : ?>
					<h2 class="site-footer__heading"><?php esc_html_e( 'Resources', 'wcuganda' ); ?></h2>
					<?php wcu_render_footer_menu( 'footer-3' ); ?>
				<?php endif; ?>
			</div>

		</div><!-- .site-footer__top -->

		<div class="site-footer__bottom">
			<div class="site-footer__bottom-inner">
				<p class="site-footer__copyright">
					&copy; <?php echo esc_html( $wcu_year ); ?>
					<?php echo esc_html( get_bloginfo( 'name' ) ); ?>.
					<?php
					echo wp_kses(
						wcu_get_powered_by(),
						array(
							'a' => array(
								'href' => array(),
								'rel'  => array(),
							),
						)
					);
					?>
				</p>

				<?php if ( has_nav_menu( 'legal' ) ) : ?>
					<nav class="site-footer__legal" aria-label="<?php esc_attr_e( 'Legal', 'wcuganda' ); ?>">
						<?php wcu_render_footer_menu( 'legal' ); ?>
					</nav>
				<?php endif; ?>
			</div>
		</div>

	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
