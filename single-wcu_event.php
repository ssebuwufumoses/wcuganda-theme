<?php
/**
 * Single event template.
 *
 * Solid-blue hero with date + title; meta sidebar with venue, type,
 * chapter, capacity, RSVP CTA, Meetup link; main column for the
 * description (post content).
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$wcu_event_id    = get_the_ID();
	$wcu_date        = get_post_meta( $wcu_event_id, '_wcu_event_date', true );
	$wcu_time        = get_post_meta( $wcu_event_id, '_wcu_event_time', true );
	$wcu_end_date    = get_post_meta( $wcu_event_id, '_wcu_event_end_date', true );
	$wcu_venue       = get_post_meta( $wcu_event_id, '_wcu_event_venue', true );
	$wcu_address     = get_post_meta( $wcu_event_id, '_wcu_event_address', true );
	$wcu_rsvp        = get_post_meta( $wcu_event_id, '_wcu_event_rsvp_url', true );
	$wcu_meetup      = get_post_meta( $wcu_event_id, '_wcu_event_meetup_url', true );
	$wcu_capacity    = get_post_meta( $wcu_event_id, '_wcu_event_capacity', true );

	$wcu_chapter_terms = get_the_terms( $wcu_event_id, 'wcu_chapter_tax' );
	$wcu_type_terms    = get_the_terms( $wcu_event_id, 'wcu_event_type' );

	$wcu_date_ts = $wcu_date ? strtotime( $wcu_date ) : 0;
	$wcu_is_past = $wcu_date_ts && $wcu_date_ts < strtotime( current_time( 'Y-m-d' ) );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'wcu-event-single' ); ?>>

		<header class="wcu-event-hero">
			<div class="container">
				<a class="wcu-event-hero__back" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_event' ) ); ?>">
					<?php wcu_svg_icon( 'arrow-left', array( 'width' => 18, 'height' => 18 ) ); ?>
					<?php esc_html_e( 'All events', 'wcuganda' ); ?>
				</a>

				<?php if ( $wcu_date ) : ?>
					<p class="wcu-event-hero__date">
						<?php wcu_svg_icon( 'calendar', array( 'width' => 18, 'height' => 18 ) ); ?>
						<?php
						echo esc_html( $wcu_date_ts ? wp_date( 'l, j M Y', $wcu_date_ts ) : $wcu_date );
						if ( $wcu_time ) {
							$wcu_time_ts = strtotime( $wcu_date . ' ' . $wcu_time );
							if ( $wcu_time_ts ) {
								echo ' &middot; ' . esc_html( wp_date( 'g:i a', $wcu_time_ts ) );
							}
						}
						?>
					</p>
				<?php endif; ?>

				<h1 class="wcu-event-hero__title"><?php the_title(); ?></h1>

				<?php if ( $wcu_is_past ) : ?>
					<p class="wcu-event-hero__past-badge"><?php esc_html_e( 'Past event', 'wcuganda' ); ?></p>
				<?php endif; ?>
			</div>
		</header>

		<div class="container wcu-event-single__inner">

			<div class="wcu-event-single__main">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="wcu-event-single__media">
						<?php the_post_thumbnail( 'wcu-hero', array( 'loading' => 'eager' ) ); ?>
					</figure>
				<?php endif; ?>

				<div class="wcu-event-single__content">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wcuganda' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>
			</div>

			<aside class="wcu-event-single__sidebar" aria-label="<?php esc_attr_e( 'Event details', 'wcuganda' ); ?>">

				<?php if ( $wcu_rsvp && ! $wcu_is_past ) : ?>
					<a class="wcu-btn wcu-btn--block wcu-btn--lg" href="<?php echo esc_url( $wcu_rsvp ); ?>"
						rel="nofollow noopener" target="_blank">
						<?php esc_html_e( 'RSVP', 'wcuganda' ); ?>
						<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
					</a>
				<?php endif; ?>

				<dl class="wcu-event-meta">
					<?php if ( $wcu_venue ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Venue', 'wcuganda' ); ?></dt>
							<dd>
								<?php echo esc_html( $wcu_venue ); ?>
								<?php if ( $wcu_address ) : ?>
									<br><span class="wcu-event-meta__address"><?php echo esc_html( $wcu_address ); ?></span>
								<?php endif; ?>
							</dd>
						</div>
					<?php endif; ?>

					<?php if ( $wcu_end_date && $wcu_end_date !== $wcu_date ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Ends', 'wcuganda' ); ?></dt>
							<dd>
								<?php
								$wcu_end_ts = strtotime( $wcu_end_date );
								echo esc_html( $wcu_end_ts ? wp_date( 'l, j M Y', $wcu_end_ts ) : $wcu_end_date );
								?>
							</dd>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $wcu_chapter_terms ) && ! is_wp_error( $wcu_chapter_terms ) ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Chapter', 'wcuganda' ); ?></dt>
							<dd>
								<?php
								$wcu_chapter_links = array();
								foreach ( $wcu_chapter_terms as $wcu_term ) {
									$wcu_chapter_links[] = sprintf(
										'<a href="%1$s">%2$s</a>',
										esc_url( get_term_link( $wcu_term ) ),
										esc_html( $wcu_term->name )
									);
								}
								echo wp_kses(
									implode( ', ', $wcu_chapter_links ),
									array( 'a' => array( 'href' => array() ) )
								);
								?>
							</dd>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $wcu_type_terms ) && ! is_wp_error( $wcu_type_terms ) ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Type', 'wcuganda' ); ?></dt>
							<dd>
								<?php
								$wcu_type_links = array();
								foreach ( $wcu_type_terms as $wcu_term ) {
									$wcu_type_links[] = sprintf(
										'<a href="%1$s">%2$s</a>',
										esc_url( get_term_link( $wcu_term ) ),
										esc_html( $wcu_term->name )
									);
								}
								echo wp_kses(
									implode( ', ', $wcu_type_links ),
									array( 'a' => array( 'href' => array() ) )
								);
								?>
							</dd>
						</div>
					<?php endif; ?>

					<?php if ( $wcu_capacity ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Capacity', 'wcuganda' ); ?></dt>
							<dd><?php echo esc_html( number_format_i18n( (int) $wcu_capacity ) ); ?></dd>
						</div>
					<?php endif; ?>

					<?php if ( $wcu_meetup ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Meetup.com', 'wcuganda' ); ?></dt>
							<dd>
								<a href="<?php echo esc_url( $wcu_meetup ); ?>" rel="nofollow noopener" target="_blank">
									<?php esc_html_e( 'View on Meetup', 'wcuganda' ); ?>
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
