<?php
/**
 * Sponsors archive — intro band, impact stats, tier-grouped cards,
 * "Become a sponsor" CTA at the bottom.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$wcu_intro_eyebrow = get_theme_mod( 'wcu_sponsors_intro_eyebrow', __( 'Backed by', 'wcuganda' ) );
$wcu_intro_heading = get_theme_mod( 'wcu_sponsors_intro_heading', __( 'Sponsors who power the community', 'wcuganda' ) );
$wcu_intro_lead    = get_theme_mod( 'wcu_sponsors_intro_lead', __( 'Every meetup, WordCamp, and contributor day on this calendar is made possible by the companies and individuals listed below.', 'wcuganda' ) );

$wcu_inquiry_email = get_theme_mod( 'wcu_sponsors_inquiry_email', '' );
if ( empty( $wcu_inquiry_email ) ) {
	$wcu_inquiry_email = get_option( 'admin_email' );
}

$wcu_inquiry_subject = sprintf(
	/* translators: %s: site name. */
	__( 'Sponsorship inquiry — %s', 'wcuganda' ),
	get_bloginfo( 'name' )
);

$wcu_stat_defaults = array(
	1 => array( '60+', __( 'Events sponsored', 'wcuganda' ) ),
	2 => array( '500+', __( 'Members enabled', 'wcuganda' ) ),
	3 => array( '3', __( 'Active chapters', 'wcuganda' ) ),
	4 => array( '8', __( 'Years of impact', 'wcuganda' ) ),
);

$wcu_stats = array();
foreach ( $wcu_stat_defaults as $i => $pair ) {
	$value = get_theme_mod( "wcu_sponsors_stat_{$i}_value", $pair[0] );
	$label = get_theme_mod( "wcu_sponsors_stat_{$i}_label", $pair[1] );
	if ( '' !== trim( (string) $value ) || '' !== trim( (string) $label ) ) {
		$wcu_stats[] = array(
			'value' => $value,
			'label' => $label,
		);
	}
}

// Group active sponsors by tier.
$wcu_sponsors_by_tier = array();

if ( taxonomy_exists( 'wcu_sponsor_tier' ) ) {
	$wcu_tiers = get_terms(
		array(
			'taxonomy'   => 'wcu_sponsor_tier',
			'hide_empty' => true,
			'orderby'    => 'term_order',
		)
	);

	if ( ! is_wp_error( $wcu_tiers ) ) {
		foreach ( $wcu_tiers as $wcu_tier ) {
			$wcu_q = new WP_Query(
				array(
					'post_type'      => 'wcu_sponsor',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'meta_query'     => array(
						array(
							'key'   => '_wcu_sponsor_active',
							'value' => '1',
						),
					),
					'tax_query'      => array(
						array(
							'taxonomy' => 'wcu_sponsor_tier',
							'field'    => 'term_id',
							'terms'    => $wcu_tier->term_id,
						),
					),
				)
			);

			if ( $wcu_q->have_posts() ) {
				$wcu_sponsors_by_tier[ $wcu_tier->slug ] = array(
					'name'  => $wcu_tier->name,
					'query' => $wcu_q,
				);
			}
		}
	}
}

// Past (inactive) sponsors — separate section below current.
$wcu_past_sponsors = new WP_Query(
	array(
		'post_type'      => 'wcu_sponsor',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'     => '_wcu_sponsor_active',
				'value'   => '1',
				'compare' => '!=',
			),
			array(
				'key'     => '_wcu_sponsor_active',
				'compare' => 'NOT EXISTS',
			),
		),
	)
);
?>

<main id="primary" class="site-main wcu-sponsors-archive">

	<header class="wcu-sponsors-archive__header">
		<div class="container">
			<?php if ( $wcu_intro_eyebrow ) : ?>
				<p class="section-eyebrow"><?php echo esc_html( $wcu_intro_eyebrow ); ?></p>
			<?php endif; ?>
			<h1 class="wcu-sponsors-archive__title"><?php echo esc_html( $wcu_intro_heading ); ?></h1>
			<?php if ( $wcu_intro_lead ) : ?>
				<div class="wcu-sponsors-archive__lead">
					<?php echo wp_kses_post( wpautop( $wcu_intro_lead ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</header>

	<?php if ( ! empty( $wcu_stats ) ) : ?>
		<section class="wcu-sponsors-impact section section--alt" aria-label="<?php esc_attr_e( 'Sponsor impact stats', 'wcuganda' ); ?>">
			<div class="container">
				<ul class="wcu-stats__grid wcu-grid wcu-grid--<?php echo esc_attr( count( $wcu_stats ) ); ?>">
					<?php foreach ( $wcu_stats as $wcu_stat ) : ?>
						<li class="wcu-stats__item">
							<span class="wcu-stats__value" data-wcu-stat="<?php echo esc_attr( $wcu_stat['value'] ); ?>">
								<?php echo esc_html( $wcu_stat['value'] ); ?>
							</span>
							<span class="wcu-stats__label"><?php echo esc_html( $wcu_stat['label'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<div class="container">

		<?php if ( ! empty( $wcu_sponsors_by_tier ) ) : ?>
			<?php foreach ( $wcu_sponsors_by_tier as $wcu_tier_slug => $wcu_tier ) :
				$wcu_q     = $wcu_tier['query'];
				$wcu_count = (int) $wcu_q->post_count;
				$wcu_grid  = 'wcu-grid wcu-grid--' . (int) min( max( $wcu_count, 1 ), 3 );
				?>
				<section class="wcu-sponsors-archive__tier wcu-sponsors-archive__tier--<?php echo esc_attr( $wcu_tier_slug ); ?>" aria-labelledby="wcu-sponsors-tier-<?php echo esc_attr( $wcu_tier_slug ); ?>">
					<header class="wcu-sponsors-archive__tier-header">
						<h2 id="wcu-sponsors-tier-<?php echo esc_attr( $wcu_tier_slug ); ?>" class="wcu-sponsors-archive__tier-title">
							<?php echo esc_html( $wcu_tier['name'] ); ?>
						</h2>
						<span class="wcu-badge wcu-badge--soft">
							<?php
							printf(
								/* translators: %d: sponsor count. */
								esc_html( _n( '%d sponsor', '%d sponsors', $wcu_count, 'wcuganda' ) ),
								(int) $wcu_count
							);
							?>
						</span>
					</header>

					<div class="<?php echo esc_attr( $wcu_grid ); ?>">
						<?php
						while ( $wcu_q->have_posts() ) :
							$wcu_q->the_post();
							get_template_part( 'template-parts/sponsors/card' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</section>
			<?php endforeach; ?>
		<?php else : ?>
			<section class="wcu-sponsors-archive__tier">
				<div class="wcu-empty">
					<p class="wcu-empty__title"><?php esc_html_e( 'No sponsors yet', 'wcuganda' ); ?></p>
					<p class="wcu-empty__desc">
						<?php esc_html_e( 'Be the first to back the community.', 'wcuganda' ); ?>
					</p>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $wcu_past_sponsors->have_posts() ) : ?>
			<section class="wcu-sponsors-archive__tier wcu-sponsors-archive__tier--past" aria-labelledby="wcu-sponsors-past-heading">
				<header class="wcu-sponsors-archive__tier-header">
					<h2 id="wcu-sponsors-past-heading" class="wcu-sponsors-archive__tier-title">
						<?php esc_html_e( 'Past sponsors', 'wcuganda' ); ?>
					</h2>
				</header>
				<div class="wcu-grid wcu-grid--3">
					<?php
					while ( $wcu_past_sponsors->have_posts() ) :
						$wcu_past_sponsors->the_post();
						get_template_part( 'template-parts/sponsors/card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

	</div>

	<section class="wcu-sponsors-cta section section--dark" aria-labelledby="wcu-sponsors-cta-heading">
		<div class="container container--narrow wcu-sponsors-cta__inner">
			<p class="section-eyebrow"><?php esc_html_e( 'Get involved', 'wcuganda' ); ?></p>
			<h2 id="wcu-sponsors-cta-heading" class="wcu-sponsors-cta__heading">
				<?php esc_html_e( 'Sponsor an upcoming WordCamp or meetup', 'wcuganda' ); ?>
			</h2>
			<p class="wcu-sponsors-cta__lead">
				<?php esc_html_e( 'Reach a focused audience of WordPress builders, contributors, and decision-makers in Uganda. We have packages for every budget — from a single meetup to a year-long partnership.', 'wcuganda' ); ?>
			</p>

			<div class="wcu-sponsors-cta__actions">
				<a class="wcu-btn wcu-btn--lg" href="mailto:<?php echo esc_attr( $wcu_inquiry_email ); ?>?subject=<?php echo esc_attr( rawurlencode( $wcu_inquiry_subject ) ); ?>">
					<?php wcu_svg_icon( 'mail', array( 'width' => 16, 'height' => 16 ) ); ?>
					<?php esc_html_e( 'Email us', 'wcuganda' ); ?>
				</a>
				<a class="wcu-btn wcu-btn--lg wcu-btn--outline-light" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_event' ) ); ?>">
					<?php esc_html_e( 'See upcoming events', 'wcuganda' ); ?>
				</a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
