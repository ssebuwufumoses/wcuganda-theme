<?php
/**
 * WordCamp card. Renders inside the loop — caller handles iteration.
 * Used on the WordCamp archive (and reusable elsewhere).
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_wc_id        = get_the_ID();
$wcu_wc_year      = get_post_meta( $wcu_wc_id, '_wcu_wc_year', true );
$wcu_wc_date      = get_post_meta( $wcu_wc_id, '_wcu_wc_date', true );
$wcu_wc_end_date  = get_post_meta( $wcu_wc_id, '_wcu_wc_end_date', true );
$wcu_wc_venue     = get_post_meta( $wcu_wc_id, '_wcu_wc_venue', true );
$wcu_wc_attendees = get_post_meta( $wcu_wc_id, '_wcu_wc_attendee_count', true );

$wcu_wc_date_ts = $wcu_wc_date ? strtotime( $wcu_wc_date ) : 0;
$wcu_wc_end_ts  = $wcu_wc_end_date ? strtotime( $wcu_wc_end_date ) : $wcu_wc_date_ts;
$wcu_wc_today   = strtotime( current_time( 'Y-m-d' ) );
$wcu_wc_is_past = $wcu_wc_end_ts && $wcu_wc_end_ts < $wcu_wc_today;
?>

<article <?php post_class( 'wcu-card wcu-wordcamp-card' . ( $wcu_wc_is_past ? ' wcu-wordcamp-card--past' : '' ) ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<a class="wcu-card__media" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( 'wcu-card', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="wcu-card__body">
		<?php if ( $wcu_wc_year ) : ?>
			<p class="wcu-card__eyebrow">
				<?php echo esc_html( $wcu_wc_year ); ?>
				<?php if ( $wcu_wc_is_past ) : ?>
					&nbsp;&middot;&nbsp;<?php esc_html_e( 'Past', 'wcuganda' ); ?>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<h3 class="wcu-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( $wcu_wc_date_ts ) : ?>
			<p class="wcu-card__meta">
				<?php wcu_svg_icon( 'calendar', array( 'width' => 14, 'height' => 14 ) ); ?>
				<span>
					<?php
					if ( $wcu_wc_end_ts && $wcu_wc_end_ts !== $wcu_wc_date_ts ) {
						printf(
							/* translators: 1: start date, 2: end date. */
							esc_html__( '%1$s — %2$s', 'wcuganda' ),
							esc_html( wp_date( 'j M', $wcu_wc_date_ts ) ),
							esc_html( wp_date( 'j M Y', $wcu_wc_end_ts ) )
						);
					} else {
						echo esc_html( wp_date( 'j M Y', $wcu_wc_date_ts ) );
					}
					?>
				</span>
			</p>
		<?php endif; ?>

		<?php if ( $wcu_wc_venue ) : ?>
			<p class="wcu-card__meta">
				<?php wcu_svg_icon( 'map-pin', array( 'width' => 14, 'height' => 14 ) ); ?>
				<span><?php echo esc_html( $wcu_wc_venue ); ?></span>
			</p>
		<?php endif; ?>

		<?php if ( $wcu_wc_attendees ) : ?>
			<p class="wcu-card__meta wcu-card__meta--source">
				<?php
				printf(
					/* translators: %s: attendee count. */
					esc_html__( '%s attendees', 'wcuganda' ),
					esc_html( number_format_i18n( (int) $wcu_wc_attendees ) )
				);
				?>
			</p>
		<?php endif; ?>
	</div>

</article>
