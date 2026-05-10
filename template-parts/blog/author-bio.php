<?php
/**
 * Author bio block. Used at the bottom of single posts and on the
 * author archive header.
 *
 * Accepts an optional author ID via $args['author_id']; defaults to
 * the post author when inside the loop.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_author_id = isset( $args['author_id'] ) ? (int) $args['author_id'] : (int) get_the_author_meta( 'ID' );
if ( ! $wcu_author_id ) {
	return;
}

$wcu_author_name = get_the_author_meta( 'display_name', $wcu_author_id );
$wcu_author_bio  = get_the_author_meta( 'description', $wcu_author_id );
$wcu_author_url  = get_author_posts_url( $wcu_author_id );
$wcu_author_web  = get_the_author_meta( 'user_url', $wcu_author_id );

// Try to find a matching wcu_member post (by login or display name).
$wcu_member_link = '';
if ( post_type_exists( 'wcu_member' ) ) {
	$wcu_login = get_the_author_meta( 'user_login', $wcu_author_id );
	$wcu_match = get_page_by_title( $wcu_author_name, OBJECT, 'wcu_member' );
	if ( ! $wcu_match && $wcu_login ) {
		$wcu_match = get_page_by_title( $wcu_login, OBJECT, 'wcu_member' );
	}
	if ( $wcu_match ) {
		$wcu_member_link = get_permalink( $wcu_match->ID );
	}
}
?>

<aside class="wcu-author-bio" aria-label="<?php esc_attr_e( 'About the author', 'wcuganda' ); ?>">

	<div class="wcu-author-bio__avatar">
		<?php echo get_avatar( $wcu_author_id, 80, '', $wcu_author_name, array( 'class' => 'wcu-author-bio__avatar-img' ) ); ?>
	</div>

	<div class="wcu-author-bio__body">
		<p class="wcu-author-bio__eyebrow"><?php esc_html_e( 'Written by', 'wcuganda' ); ?></p>
		<h3 class="wcu-author-bio__name">
			<a href="<?php echo esc_url( $wcu_author_url ); ?>"><?php echo esc_html( $wcu_author_name ); ?></a>
		</h3>

		<?php if ( ! empty( $wcu_author_bio ) ) : ?>
			<p class="wcu-author-bio__desc"><?php echo esc_html( $wcu_author_bio ); ?></p>
		<?php endif; ?>

		<ul class="wcu-author-bio__links">
			<li>
				<a href="<?php echo esc_url( $wcu_author_url ); ?>">
					<?php esc_html_e( 'All posts', 'wcuganda' ); ?>
				</a>
			</li>
			<?php if ( $wcu_member_link ) : ?>
				<li>
					<a href="<?php echo esc_url( $wcu_member_link ); ?>">
						<?php esc_html_e( 'Member profile', 'wcuganda' ); ?>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $wcu_author_web ) : ?>
				<li>
					<a href="<?php echo esc_url( $wcu_author_web ); ?>" rel="noopener" target="_blank">
						<?php esc_html_e( 'Website', 'wcuganda' ); ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>

</aside>
