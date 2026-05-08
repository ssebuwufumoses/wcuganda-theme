<?php
/**
 * Member card. Renders inside the loop — caller handles iteration.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_member_id    = get_the_ID();
$wcu_role_title   = get_post_meta( $wcu_member_id, '_wcu_member_role_title', true );
$wcu_is_speaker   = '1' === get_post_meta( $wcu_member_id, '_wcu_member_speaker', true );
$wcu_is_organizer = '1' === get_post_meta( $wcu_member_id, '_wcu_member_organizer', true );

$wcu_chapter_terms = get_the_terms( $wcu_member_id, 'wcu_chapter_tax' );
$wcu_chapter_name  = ( $wcu_chapter_terms && ! is_wp_error( $wcu_chapter_terms ) )
	? $wcu_chapter_terms[0]->name
	: '';

$wcu_skill_terms = get_the_terms( $wcu_member_id, 'wcu_skills' );

// WordPress.org avatar fallback.
$wcu_wporg_username_card = get_post_meta( $wcu_member_id, '_wcu_member_wporg_username', true );
$wcu_card_avatar         = '';
if ( ! has_post_thumbnail() && ! empty( $wcu_wporg_username_card ) && class_exists( 'WCU_WPOrg_Profiles' ) ) {
	$wcu_card_avatar = WCU_WPOrg_Profiles::get_avatar_url( $wcu_wporg_username_card, 160 );
}
?>

<article <?php post_class( 'wcu-card wcu-member-card' ); ?>>

	<a class="wcu-member-card__avatar" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'wcu-avatar', array( 'loading' => 'lazy' ) ); ?>
		<?php elseif ( ! empty( $wcu_card_avatar ) ) : ?>
			<img src="<?php echo esc_url( $wcu_card_avatar ); ?>"
				alt="<?php echo esc_attr( get_the_title() ); ?>"
				loading="lazy">
		<?php else : ?>
			<span class="wcu-member-card__avatar-placeholder" aria-hidden="true">
				<?php echo esc_html( strtoupper( mb_substr( get_the_title(), 0, 1 ) ) ); ?>
			</span>
		<?php endif; ?>
	</a>

	<div class="wcu-card__body wcu-member-card__body">

		<h3 class="wcu-card__title wcu-member-card__name">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( ! empty( $wcu_role_title ) ) : ?>
			<p class="wcu-member-card__role"><?php echo esc_html( $wcu_role_title ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $wcu_chapter_name ) ) : ?>
			<p class="wcu-card__meta wcu-member-card__chapter">
				<?php wcu_svg_icon( 'map-pin', array( 'width' => 14, 'height' => 14 ) ); ?>
				<span><?php echo esc_html( $wcu_chapter_name ); ?></span>
			</p>
		<?php endif; ?>

		<?php if ( $wcu_is_speaker || $wcu_is_organizer || ( $wcu_skill_terms && ! is_wp_error( $wcu_skill_terms ) ) ) : ?>
			<ul class="wcu-member-card__tags" aria-label="<?php esc_attr_e( 'Tags', 'wcuganda' ); ?>">
				<?php if ( $wcu_is_organizer ) : ?>
					<li><span class="wcu-badge wcu-badge--soft"><?php esc_html_e( 'Organizer', 'wcuganda' ); ?></span></li>
				<?php endif; ?>
				<?php if ( $wcu_is_speaker ) : ?>
					<li><span class="wcu-badge wcu-badge--soft"><?php esc_html_e( 'Speaker', 'wcuganda' ); ?></span></li>
				<?php endif; ?>
				<?php if ( $wcu_skill_terms && ! is_wp_error( $wcu_skill_terms ) ) : ?>
					<?php foreach ( array_slice( $wcu_skill_terms, 0, 3 ) as $wcu_skill ) : ?>
						<li><span class="wcu-badge wcu-badge--soft"><?php echo esc_html( $wcu_skill->name ); ?></span></li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		<?php endif; ?>

	</div>

</article>
