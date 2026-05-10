<?php
/**
 * 404 — page not found.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main wcu-misc-page">

	<div class="container container--narrow wcu-misc-page__inner">

		<header class="wcu-misc-page__header">
			<p class="section-eyebrow wcu-misc-page__code">404</p>
			<h1 class="wcu-misc-page__title"><?php esc_html_e( 'Page not found', 'wcuganda' ); ?></h1>
			<p class="wcu-misc-page__lead">
				<?php esc_html_e( 'The page you tried to reach is missing or has moved. Try the links below, or search the site.', 'wcuganda' ); ?>
			</p>
		</header>

		<div class="wcu-misc-page__search">
			<?php get_search_form(); ?>
		</div>

		<section class="wcu-misc-page__shortcuts" aria-label="<?php esc_attr_e( 'Quick links', 'wcuganda' ); ?>">
			<h2 class="wcu-misc-page__shortcuts-heading">
				<?php esc_html_e( 'Or jump to', 'wcuganda' ); ?>
			</h2>
			<ul class="wcu-misc-page__shortcuts-list">
				<li>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php esc_html_e( 'Home', 'wcuganda' ); ?>
					</a>
				</li>
				<?php if ( post_type_exists( 'wcu_event' ) ) : ?>
					<li>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'wcu_event' ) ); ?>">
							<?php esc_html_e( 'Events', 'wcuganda' ); ?>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( post_type_exists( 'wcu_chapter' ) ) : ?>
					<li>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'wcu_chapter' ) ); ?>">
							<?php esc_html_e( 'Chapters', 'wcuganda' ); ?>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( post_type_exists( 'wcu_member' ) ) : ?>
					<li>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'wcu_member' ) ); ?>">
							<?php esc_html_e( 'Members', 'wcuganda' ); ?>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( post_type_exists( 'wcu_wordcamp' ) ) : ?>
					<li>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'wcu_wordcamp' ) ); ?>">
							<?php esc_html_e( 'WordCamps', 'wcuganda' ); ?>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( post_type_exists( 'wcu_sponsor' ) ) : ?>
					<li>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'wcu_sponsor' ) ); ?>">
							<?php esc_html_e( 'Sponsors', 'wcuganda' ); ?>
						</a>
					</li>
				<?php endif; ?>
			</ul>
		</section>

	</div>

</main>

<?php
get_footer();
