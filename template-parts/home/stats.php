<?php
/**
 * Homepage stats — four numeric blocks with labels.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_stat_defaults = array(
	1 => array( '500', __( 'Community members', 'wcuganda' ) ),
	2 => array( '60',  __( 'Events hosted', 'wcuganda' ) ),
	3 => array( '3',   __( 'Active chapters', 'wcuganda' ) ),
	4 => array( '8',   __( 'Years building', 'wcuganda' ) ),
);

$wcu_stats = array();
foreach ( $wcu_stat_defaults as $i => $pair ) {
	$value = get_theme_mod( "wcu_stat_{$i}_value", $pair[0] );
	$label = get_theme_mod( "wcu_stat_{$i}_label", $pair[1] );

	if ( '' !== trim( $value ) || '' !== trim( $label ) ) {
		$wcu_stats[] = array(
			'value' => $value,
			'label' => $label,
		);
	}
}

if ( empty( $wcu_stats ) ) {
	return;
}
?>

<section class="wcu-stats section section--alt" aria-label="<?php esc_attr_e( 'Community stats', 'wcuganda' ); ?>">
	<div class="container">
		<ul class="wcu-stats__grid wcu-grid wcu-grid--<?php echo esc_attr( count( $wcu_stats ) ); ?>">
			<?php foreach ( $wcu_stats as $wcu_stat ) : ?>
				<li class="wcu-stats__item">
					<span class="wcu-stats__value" data-wcu-stat="<?php echo esc_attr( $wcu_stat['value'] ); ?>">
						<?php echo esc_html( $wcu_stat['value'] ); ?>
					</span>
					<span class="wcu-stats__label">
						<?php echo esc_html( $wcu_stat['label'] ); ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
