<?php
/**
 * Single member profile.
 *
 * Solid blue hero with avatar + name + role + chapter; two-column body
 * with the bio (post content) on the left and a sidebar of social
 * links, WP.org link, contact, role/skill badges on the right.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$wcu_member_id    = get_the_ID();
	$wcu_role_title   = get_post_meta( $wcu_member_id, '_wcu_member_role_title', true );
	$wcu_wporg        = get_post_meta( $wcu_member_id, '_wcu_member_wporg_username', true );
	$wcu_twitter      = get_post_meta( $wcu_member_id, '_wcu_member_twitter', true );
	$wcu_github       = get_post_meta( $wcu_member_id, '_wcu_member_github', true );
	$wcu_linkedin     = get_post_meta( $wcu_member_id, '_wcu_member_linkedin', true );
	$wcu_website      = get_post_meta( $wcu_member_id, '_wcu_member_website', true );
	$wcu_is_speaker   = '1' === get_post_meta( $wcu_member_id, '_wcu_member_speaker', true );
	$wcu_is_organizer = '1' === get_post_meta( $wcu_member_id, '_wcu_member_organizer', true );

	$wcu_chapter_terms = get_the_terms( $wcu_member_id, 'wcu_chapter_tax' );
	$wcu_skill_terms   = get_the_terms( $wcu_member_id, 'wcu_skills' );
	$wcu_role_terms    = get_the_terms( $wcu_member_id, 'wcu_member_role' );

	$wcu_socials = array_filter(
		array(
			'twitter'   => $wcu_twitter,
			'github'    => $wcu_github,
			'linkedin'  => $wcu_linkedin,
		)
	);
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'wcu-member-single' ); ?>>

		<header class="wcu-member-hero">
			<div class="container wcu-member-hero__inner">
				<a class="wcu-event-hero__back" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_member' ) ); ?>">
					<?php wcu_svg_icon( 'arrow-left', array( 'width' => 18, 'height' => 18 ) ); ?>
					<?php esc_html_e( 'All members', 'wcuganda' ); ?>
				</a>

				<div class="wcu-member-hero__identity">
					<div class="wcu-member-hero__avatar">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'wcu-avatar' ); ?>
						<?php else : ?>
							<span class="wcu-member-hero__avatar-placeholder" aria-hidden="true">
								<?php echo esc_html( strtoupper( mb_substr( get_the_title(), 0, 1 ) ) ); ?>
							</span>
						<?php endif; ?>
					</div>

					<div class="wcu-member-hero__text">
						<h1 class="wcu-member-hero__name"><?php the_title(); ?></h1>
						<?php if ( ! empty( $wcu_role_title ) ) : ?>
							<p class="wcu-member-hero__role"><?php echo esc_html( $wcu_role_title ); ?></p>
						<?php endif; ?>

						<?php if ( $wcu_chapter_terms && ! is_wp_error( $wcu_chapter_terms ) ) : ?>
							<p class="wcu-member-hero__chapter">
								<?php wcu_svg_icon( 'map-pin', array( 'width' => 16, 'height' => 16 ) ); ?>
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
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</header>

		<div class="container wcu-event-single__inner">

			<div class="wcu-event-single__main">
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

			<aside class="wcu-event-single__sidebar" aria-label="<?php esc_attr_e( 'Member details', 'wcuganda' ); ?>">

				<?php if ( ! empty( $wcu_socials ) ) : ?>
					<ul class="wcu-member-socials" aria-label="<?php esc_attr_e( 'Social links', 'wcuganda' ); ?>">
						<?php foreach ( $wcu_socials as $wcu_network => $wcu_url ) : ?>
							<li>
								<a class="wcu-member-socials__link" href="<?php echo esc_url( $wcu_url ); ?>" rel="me noopener" target="_blank">
									<?php
									wcu_svg_icon(
										$wcu_network,
										array(
											'width'  => 18,
											'height' => 18,
											'title'  => ucfirst( $wcu_network ),
										)
									);
									?>
									<span><?php echo esc_html( ucfirst( $wcu_network ) ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<dl class="wcu-event-meta">

					<?php if ( ! empty( $wcu_wporg ) ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'WordPress.org', 'wcuganda' ); ?></dt>
							<dd>
								<a href="<?php echo esc_url( 'https://profiles.wordpress.org/' . rawurlencode( $wcu_wporg ) . '/' ); ?>" rel="noopener" target="_blank">
									@<?php echo esc_html( $wcu_wporg ); ?>
								</a>
							</dd>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $wcu_website ) ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Website', 'wcuganda' ); ?></dt>
							<dd>
								<a href="<?php echo esc_url( $wcu_website ); ?>" rel="me noopener" target="_blank">
									<?php echo esc_html( wp_parse_url( $wcu_website, PHP_URL_HOST ) ?: $wcu_website ); ?>
								</a>
							</dd>
						</div>
					<?php endif; ?>

					<?php if ( $wcu_is_speaker || $wcu_is_organizer ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Active as', 'wcuganda' ); ?></dt>
							<dd>
								<?php
								$wcu_active = array();
								if ( $wcu_is_organizer ) {
									$wcu_active[] = esc_html__( 'Organizer', 'wcuganda' );
								}
								if ( $wcu_is_speaker ) {
									$wcu_active[] = esc_html__( 'Speaker', 'wcuganda' );
								}
								echo esc_html( implode( ', ', $wcu_active ) );
								?>
							</dd>
						</div>
					<?php endif; ?>

					<?php if ( $wcu_role_terms && ! is_wp_error( $wcu_role_terms ) ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Roles', 'wcuganda' ); ?></dt>
							<dd>
								<?php
								$wcu_role_links = array();
								foreach ( $wcu_role_terms as $wcu_term ) {
									$wcu_role_links[] = sprintf(
										'<a href="%1$s">%2$s</a>',
										esc_url( get_term_link( $wcu_term ) ),
										esc_html( $wcu_term->name )
									);
								}
								echo wp_kses(
									implode( ', ', $wcu_role_links ),
									array( 'a' => array( 'href' => array() ) )
								);
								?>
							</dd>
						</div>
					<?php endif; ?>

					<?php if ( $wcu_skill_terms && ! is_wp_error( $wcu_skill_terms ) ) : ?>
						<div class="wcu-event-meta__row">
							<dt><?php esc_html_e( 'Skills', 'wcuganda' ); ?></dt>
							<dd>
								<ul class="wcu-member-skills">
									<?php foreach ( $wcu_skill_terms as $wcu_term ) : ?>
										<li><span class="wcu-badge wcu-badge--soft"><?php echo esc_html( $wcu_term->name ); ?></span></li>
									<?php endforeach; ?>
								</ul>
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
