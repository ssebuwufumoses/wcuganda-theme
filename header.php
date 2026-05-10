<?php
/**
 * Site header.
 *
 * Renders the document head, opens the main page wrapper, and outputs the
 * branding plus primary navigation.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#primary"><?php esc_html_e( 'Skip to content', 'wcuganda' ); ?></a>

<div id="page" class="site">

	<header id="masthead" class="site-header" role="banner">
		<div class="site-header__inner">

			<div class="site-branding">
				<?php
				if ( has_custom_logo() ) {
					the_custom_logo();
				}

				$wcu_blog_name        = get_bloginfo( 'name' );
				$wcu_blog_description = get_bloginfo( 'description', 'display' );

				if ( ! empty( $wcu_blog_name ) ) :
					$wcu_title_tag = ( is_front_page() && is_home() ) ? 'h1' : 'p';
					?>
					<<?php echo esc_html( $wcu_title_tag ); ?> class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<?php echo esc_html( $wcu_blog_name ); ?>
						</a>
					</<?php echo esc_html( $wcu_title_tag ); ?>>
					<?php
				endif;

				if ( $wcu_blog_description || is_customize_preview() ) :
					?>
					<p class="site-description"><?php echo esc_html( $wcu_blog_description ); ?></p>
					<?php
				endif;
				?>
			</div><!-- .site-branding -->

			<div class="site-navigation">
				<nav id="site-navigation" class="wcu-nav" aria-label="<?php esc_attr_e( 'Primary', 'wcuganda' ); ?>">
					<button
						class="wcu-nav__toggle"
						type="button"
						aria-controls="wcu-primary-menu"
						aria-expanded="false"
						aria-label="<?php esc_attr_e( 'Toggle primary menu', 'wcuganda' ); ?>"
					>
						<span class="wcu-nav__icon-bar" aria-hidden="true"></span>
						<span class="wcu-nav__icon-bar" aria-hidden="true"></span>
						<span class="wcu-nav__icon-bar" aria-hidden="true"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'wcuganda' ); ?></span>
					</button>

					<?php wcu_render_primary_menu(); ?>
				</nav>

				<button type="button"
					class="site-header__theme-toggle"
					data-wcu-theme-toggle
					aria-label="<?php esc_attr_e( 'Switch to light theme', 'wcuganda' ); ?>"
					aria-pressed="false">
					<span class="site-header__theme-toggle-icon site-header__theme-toggle-icon--moon" aria-hidden="true">
						<?php wcu_svg_icon( 'moon', array( 'width' => 16, 'height' => 16 ) ); ?>
					</span>
					<span class="site-header__theme-toggle-icon site-header__theme-toggle-icon--sun" aria-hidden="true">
						<?php wcu_svg_icon( 'sun', array( 'width' => 16, 'height' => 16 ) ); ?>
					</span>
				</button>

				<?php
				$wcu_join_url = get_theme_mod( 'wcu_join_url', '' );
				if ( ! empty( $wcu_join_url ) ) :
					?>
					<a class="wcu-btn wcu-btn--sm site-header__cta" href="<?php echo esc_url( $wcu_join_url ); ?>">
						<?php esc_html_e( 'Join Us', 'wcuganda' ); ?>
					</a>
					<?php
				endif;
				?>
			</div>

		</div><!-- .site-header__inner -->
	</header><!-- #masthead -->

	<div id="content" class="site-content">
