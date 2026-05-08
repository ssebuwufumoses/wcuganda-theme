<?php
/**
 * Events archive filter chips: chapter + event type.
 *
 * Reads `chapter` and `type` query vars from the current URL to highlight
 * the active chip. Submits via plain links so deep-linkable + crawlable.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_archive_url = get_post_type_archive_link( 'wcu_event' );
if ( empty( $wcu_archive_url ) ) {
	return;
}

$wcu_active_chapter = isset( $_GET['chapter'] ) ? sanitize_title( wp_unslash( $_GET['chapter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$wcu_active_type    = isset( $_GET['type'] ) ? sanitize_title( wp_unslash( $_GET['type'] ) ) : '';      // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$wcu_chapters = get_terms(
	array(
		'taxonomy'   => 'wcu_chapter_tax',
		'hide_empty' => false,
	)
);

$wcu_types = get_terms(
	array(
		'taxonomy'   => 'wcu_event_type',
		'hide_empty' => false,
	)
);

/**
 * Build a filter URL while preserving the *other* active filter.
 *
 * @param string $param Query var key being set ('chapter' or 'type').
 * @param string $slug  Term slug, or empty for "all".
 * @return string
 */
$wcu_build_url = function ( $param, $slug ) use ( $wcu_archive_url, $wcu_active_chapter, $wcu_active_type ) {
	$args = array();
	if ( 'chapter' === $param ) {
		if ( '' !== $slug ) {
			$args['chapter'] = $slug;
		}
		if ( '' !== $wcu_active_type ) {
			$args['type'] = $wcu_active_type;
		}
	} elseif ( 'type' === $param ) {
		if ( '' !== $slug ) {
			$args['type'] = $slug;
		}
		if ( '' !== $wcu_active_chapter ) {
			$args['chapter'] = $wcu_active_chapter;
		}
	}
	return empty( $args ) ? $wcu_archive_url : add_query_arg( $args, $wcu_archive_url );
};
?>

<div class="wcu-event-filters" role="group" aria-label="<?php esc_attr_e( 'Filter events', 'wcuganda' ); ?>">

	<?php if ( ! is_wp_error( $wcu_chapters ) && ! empty( $wcu_chapters ) ) : ?>
		<div class="wcu-event-filters__group">
			<span class="wcu-event-filters__label"><?php esc_html_e( 'Chapter:', 'wcuganda' ); ?></span>
			<a class="wcu-event-filters__chip<?php echo '' === $wcu_active_chapter ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( $wcu_build_url( 'chapter', '' ) ); ?>">
				<?php esc_html_e( 'All', 'wcuganda' ); ?>
			</a>
			<?php foreach ( $wcu_chapters as $wcu_term ) : ?>
				<a class="wcu-event-filters__chip<?php echo $wcu_active_chapter === $wcu_term->slug ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( $wcu_build_url( 'chapter', $wcu_term->slug ) ); ?>">
					<?php echo esc_html( $wcu_term->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! is_wp_error( $wcu_types ) && ! empty( $wcu_types ) ) : ?>
		<div class="wcu-event-filters__group">
			<span class="wcu-event-filters__label"><?php esc_html_e( 'Type:', 'wcuganda' ); ?></span>
			<a class="wcu-event-filters__chip<?php echo '' === $wcu_active_type ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( $wcu_build_url( 'type', '' ) ); ?>">
				<?php esc_html_e( 'All', 'wcuganda' ); ?>
			</a>
			<?php foreach ( $wcu_types as $wcu_term ) : ?>
				<a class="wcu-event-filters__chip<?php echo $wcu_active_type === $wcu_term->slug ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( $wcu_build_url( 'type', $wcu_term->slug ) ); ?>">
					<?php echo esc_html( $wcu_term->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

</div>
