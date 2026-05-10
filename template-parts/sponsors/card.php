<?php
/**
 * Sponsor card. Renders inside the loop — caller handles iteration.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_sponsor_id     = get_the_ID();
$wcu_sponsor_url    = get_post_meta( $wcu_sponsor_id, '_wcu_sponsor_url', true );
$wcu_sponsor_active = '1' === get_post_meta( $wcu_sponsor_id, '_wcu_sponsor_active', true );
$wcu_sponsor_year   = get_post_meta( $wcu_sponsor_id, '_wcu_sponsor_year', true );

$wcu_tier_terms = get_the_terms( $wcu_sponsor_id, 'wcu_sponsor_tier' );
$wcu_tier_name  = ( $wcu_tier_terms && ! is_wp_error( $wcu_tier_terms ) ) ? $wcu_tier_terms[0]->name : '';
$wcu_tier_slug  = ( $wcu_tier_terms && ! is_wp_error( $wcu_tier_terms ) ) ? $wcu_tier_terms[0]->slug : '';
?>

<article <?php post_class( 'wcu-card wcu-sponsor-card' ); ?>>

	<a class="wcu-sponsor-card__logo" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium', array( 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<span class="wcu-sponsor-card__placeholder" aria-hidden="true">
				<?php echo esc_html( strtoupper( mb_substr( get_the_title(), 0, 1 ) ) ); ?>
			</span>
		<?php endif; ?>
	</a>

	<div class="wcu-card__body wcu-sponsor-card__body">

		<?php if ( $wcu_tier_name ) : ?>
			<p class="wcu-card__eyebrow">
				<?php echo esc_html( $wcu_tier_name ); ?>
				<?php if ( ! $wcu_sponsor_active ) : ?>
					&nbsp;&middot;&nbsp;<?php esc_html_e( 'Past', 'wcuganda' ); ?>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<h3 class="wcu-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( has_excerpt() || get_the_content() ) : ?>
			<p class="wcu-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<?php endif; ?>

		<?php if ( $wcu_sponsor_year ) : ?>
			<p class="wcu-card__meta wcu-card__meta--source">
				<?php
				printf(
					/* translators: %s: year, e.g. 2026 or "2024-2026". */
					esc_html__( 'Sponsored %s', 'wcuganda' ),
					esc_html( $wcu_sponsor_year )
				);
				?>
			</p>
		<?php endif; ?>

	</div>

	<?php if ( ! empty( $wcu_sponsor_url ) ) : ?>
		<div class="wcu-card__footer">
			<a class="wcu-btn wcu-btn--ghost wcu-btn--sm" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'View profile', 'wcuganda' ); ?>
				<?php wcu_svg_icon( 'arrow-right', array( 'width' => 14, 'height' => 14 ) ); ?>
			</a>
			<a class="wcu-card__meetup-link" href="<?php echo esc_url( $wcu_sponsor_url ); ?>" rel="nofollow noopener" target="_blank" aria-label="<?php esc_attr_e( 'Visit sponsor website', 'wcuganda' ); ?>">
				<?php esc_html_e( 'Website', 'wcuganda' ); ?>
				<?php wcu_svg_icon( 'arrow-right', array( 'width' => 12, 'height' => 12 ) ); ?>
			</a>
		</div>
	<?php endif; ?>

</article>
