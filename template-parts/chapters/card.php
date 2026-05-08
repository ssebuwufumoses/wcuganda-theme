<?php
/**
 * Chapter card. Used on the chapters archive (and reusable elsewhere).
 *
 * Renders the post inside the loop — caller handles the_post() iteration.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_chapter_id        = get_the_ID();
$wcu_chapter_city      = get_post_meta( $wcu_chapter_id, '_wcu_chapter_city', true );
$wcu_chapter_meetup    = get_post_meta( $wcu_chapter_id, '_wcu_chapter_meetup_url', true );
$wcu_chapter_founded   = get_post_meta( $wcu_chapter_id, '_wcu_chapter_founded', true );
$wcu_chapter_organizers = get_post_meta( $wcu_chapter_id, '_wcu_chapter_organizers', true );
$wcu_chapter_excerpt   = get_the_excerpt();
?>

<article <?php post_class( 'wcu-card wcu-chapter-card' ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<a class="wcu-card__media" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( 'wcu-card', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="wcu-card__body">

		<?php if ( ! empty( $wcu_chapter_city ) ) : ?>
			<p class="wcu-card__eyebrow">
				<?php wcu_svg_icon( 'map-pin', array( 'width' => 14, 'height' => 14 ) ); ?>
				<?php echo esc_html( $wcu_chapter_city ); ?>
			</p>
		<?php endif; ?>

		<h3 class="wcu-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( ! empty( $wcu_chapter_excerpt ) ) : ?>
			<p class="wcu-card__excerpt"><?php echo esc_html( wp_trim_words( $wcu_chapter_excerpt, 22 ) ); ?></p>
		<?php endif; ?>

		<?php
		$wcu_meta_bits = array();
		if ( ! empty( $wcu_chapter_founded ) ) {
			$wcu_meta_bits[] = sprintf(
				/* translators: %s: year, e.g. 2018. */
				esc_html__( 'Since %s', 'wcuganda' ),
				esc_html( $wcu_chapter_founded )
			);
		}
		if ( ! empty( $wcu_chapter_organizers ) ) {
			$wcu_organizers_count = count(
				array_filter( array_map( 'trim', explode( ',', (string) $wcu_chapter_organizers ) ) )
			);
			if ( $wcu_organizers_count > 0 ) {
				$wcu_meta_bits[] = sprintf(
					/* translators: %d: organizer count. */
					_n( '%d organizer', '%d organizers', $wcu_organizers_count, 'wcuganda' ),
					(int) $wcu_organizers_count
				);
			}
		}
		?>

		<?php if ( ! empty( $wcu_meta_bits ) ) : ?>
			<p class="wcu-card__meta wcu-card__meta--source">
				<?php echo esc_html( implode( ' · ', $wcu_meta_bits ) ); ?>
			</p>
		<?php endif; ?>

	</div>

	<div class="wcu-card__footer">
		<a class="wcu-btn wcu-btn--ghost wcu-btn--sm" href="<?php the_permalink(); ?>">
			<?php esc_html_e( 'Visit chapter', 'wcuganda' ); ?>
			<?php wcu_svg_icon( 'arrow-right', array( 'width' => 14, 'height' => 14 ) ); ?>
		</a>
		<?php if ( ! empty( $wcu_chapter_meetup ) ) : ?>
			<a class="wcu-card__meetup-link" href="<?php echo esc_url( $wcu_chapter_meetup ); ?>" rel="nofollow noopener" target="_blank" aria-label="<?php esc_attr_e( 'Open on Meetup.com', 'wcuganda' ); ?>">
				<?php esc_html_e( 'Meetup.com', 'wcuganda' ); ?>
				<?php wcu_svg_icon( 'arrow-right', array( 'width' => 12, 'height' => 12 ) ); ?>
			</a>
		<?php endif; ?>
	</div>

</article>
