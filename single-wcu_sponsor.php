<?php
/**
 * Single sponsor template.
 *
 * Light hero with logo + tier badge + name + visit-website CTA;
 * full-width content section; meta sidebar with year, active status,
 * tier link.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$wcu_sponsor_id     = get_the_ID();
	$wcu_sponsor_url    = get_post_meta( $wcu_sponsor_id, '_wcu_sponsor_url', true );
	$wcu_sponsor_active = '1' === get_post_meta( $wcu_sponsor_id, '_wcu_sponsor_active', true );
	$wcu_sponsor_year   = get_post_meta( $wcu_sponsor_id, '_wcu_sponsor_year', true );

	$wcu_tier_terms = get_the_terms( $wcu_sponsor_id, 'wcu_sponsor_tier' );
	$wcu_tier_term  = ( $wcu_tier_terms && ! is_wp_error( $wcu_tier_terms ) ) ? $wcu_tier_terms[0] : null;
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'wcu-sponsor-single' ); ?>>

		<header class="wcu-sponsor-hero">
			<div class="container">
				<a class="wcu-event-hero__back" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_sponsor' ) ); ?>">
					<?php wcu_svg_icon( 'arrow-left', array( 'width' => 18, 'height' => 18 ) ); ?>
					<?php esc_html_e( 'All sponsors', 'wcuganda' ); ?>
				</a>

				<div class="wcu-sponsor-hero__inner">
					<div class="wcu-sponsor-hero__logo">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium', array( 'loading' => 'eager' ) ); ?>
						<?php else : ?>
							<span class="wcu-sponsor-hero__logo-placeholder" aria-hidden="true">
								<?php echo esc_html( strtoupper( mb_substr( get_the_title(), 0, 1 ) ) ); ?>
							</span>
						<?php endif; ?>
					</div>

					<div class="wcu-sponsor-hero__text">
						<?php if ( $wcu_tier_term ) : ?>
							<p class="wcu-sponsor-hero__tier">
								<a href="<?php echo esc_url( get_term_link( $wcu_tier_term ) ); ?>">
									<?php echo esc_html( $wcu_tier_term->name ); ?>
								</a>
								<?php if ( ! $wcu_sponsor_active ) : ?>
									&nbsp;&middot;&nbsp;<?php esc_html_e( 'Past sponsor', 'wcuganda' ); ?>
								<?php endif; ?>
							</p>
						<?php endif; ?>

						<h1 class="wcu-sponsor-hero__name"><?php the_title(); ?></h1>

						<?php if ( $wcu_sponsor_url ) : ?>
							<div class="wcu-sponsor-hero__actions">
								<a class="wcu-btn" href="<?php echo esc_url( $wcu_sponsor_url ); ?>"
									rel="nofollow noopener" target="_blank">
									<?php esc_html_e( 'Visit website', 'wcuganda' ); ?>
									<?php wcu_svg_icon( 'arrow-right', array( 'width' => 16, 'height' => 16 ) ); ?>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</header>

		<div class="container wcu-event-single__inner">

			<div class="wcu-event-single__main">
				<div class="wcu-event-single__content">
					<?php
					if ( get_the_content() ) {
						the_content();
					} else {
						printf(
							'<p>%s</p>',
							esc_html__( 'No description has been added for this sponsor yet.', 'wcuganda' )
						);
					}

					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wcuganda' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>
			</div>

			<aside class="wcu-event-single__sidebar" aria-label="<?php esc_attr_e( 'Sponsor details', 'wcuganda' ); ?>">

				<dl class="wcu-event-meta">

					<?php if ( $wcu_tier_term ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Tier', 'wcuganda' ); ?></dt>
							<dd>
								<a href="<?php echo esc_url( get_term_link( $wcu_tier_term ) ); ?>">
									<?php echo esc_html( $wcu_tier_term->name ); ?>
								</a>
							</dd>
						</div>
					<?php endif; ?>

					<div class="wcu-event-meta__row">
						<dt><?php esc_html_e( 'Status', 'wcuganda' ); ?></dt>
						<dd>
							<?php if ( $wcu_sponsor_active ) : ?>
								<span class="wcu-folks-availability__pill wcu-folks-availability__pill--hire">
									<?php esc_html_e( 'Active', 'wcuganda' ); ?>
								</span>
							<?php else : ?>
								<?php esc_html_e( 'Past sponsor', 'wcuganda' ); ?>
							<?php endif; ?>
						</dd>
					</div>

					<?php if ( $wcu_sponsor_year ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Year(s)', 'wcuganda' ); ?></dt>
							<dd><?php echo esc_html( $wcu_sponsor_year ); ?></dd>
						</div>
					<?php endif; ?>

					<?php if ( $wcu_sponsor_url ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Website', 'wcuganda' ); ?></dt>
							<dd>
								<a href="<?php echo esc_url( $wcu_sponsor_url ); ?>" rel="nofollow noopener" target="_blank">
									<?php echo esc_html( wp_parse_url( $wcu_sponsor_url, PHP_URL_HOST ) ?: $wcu_sponsor_url ); ?>
								</a>
							</dd>
						</div>
					<?php endif; ?>

				</dl>

			</aside>

		</div>

	</article>

	<?php
endwhile;

get_footer();
