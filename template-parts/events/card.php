<?php
/**
 * Reusable event card. Accepts a normalized card array via `$args['card']`
 * so callers (homepage preview, archive list, related events) can build the
 * shape from either a `wcu_event` post or a WP.org Events API payload.
 *
 * Expected $args['card'] keys: title (string), url (string), date (string),
 * venue (string), image (string url), is_local (bool), meetup (string).
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $args ) || ! is_array( $args ) || empty( $args['card'] ) ) {
	return;
}

$wcu_card = wp_parse_args(
	$args['card'],
	array(
		'title'    => '',
		'url'      => '',
		'date'     => '',
		'venue'    => '',
		'image'    => '',
		'is_local' => true,
		'meetup'   => '',
	)
);

if ( empty( $wcu_card['title'] ) ) {
	return;
}
?>

<article class="wcu-card wcu-event-card<?php echo $wcu_card['is_local'] ? '' : ' wcu-event-card--remote'; ?>">

	<?php if ( ! empty( $wcu_card['image'] ) ) : ?>
		<a class="wcu-card__media" href="<?php echo esc_url( $wcu_card['url'] ); ?>">
			<img src="<?php echo esc_url( $wcu_card['image'] ); ?>"
				alt="<?php echo esc_attr( $wcu_card['title'] ); ?>"
				loading="lazy">
		</a>
	<?php endif; ?>

	<div class="wcu-card__body">
		<?php if ( ! empty( $wcu_card['date'] ) ) : ?>
			<p class="wcu-card__eyebrow">
				<?php
				wcu_svg_icon(
					'calendar',
					array(
						'width'  => 14,
						'height' => 14,
					)
				);
				$wcu_ts = strtotime( $wcu_card['date'] );
				echo esc_html( $wcu_ts ? wp_date( 'l, j M Y', $wcu_ts ) : $wcu_card['date'] );
				?>
			</p>
		<?php endif; ?>

		<h3 class="wcu-card__title">
			<a href="<?php echo esc_url( $wcu_card['url'] ); ?>"<?php echo $wcu_card['is_local'] ? '' : ' rel="nofollow noopener" target="_blank"'; ?>>
				<?php echo esc_html( $wcu_card['title'] ); ?>
			</a>
		</h3>

		<?php if ( ! empty( $wcu_card['venue'] ) ) : ?>
			<p class="wcu-card__meta">
				<?php
				wcu_svg_icon(
					'map-pin',
					array(
						'width'  => 14,
						'height' => 14,
					)
				);
				?>
				<span><?php echo esc_html( $wcu_card['venue'] ); ?></span>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $wcu_card['meetup'] ) ) : ?>
			<p class="wcu-card__meta wcu-card__meta--source">
				<?php
				printf(
					/* translators: %s: meetup group name. */
					esc_html__( 'via %s', 'wcuganda' ),
					esc_html( $wcu_card['meetup'] )
				);
				?>
			</p>
		<?php endif; ?>
	</div>

</article>
