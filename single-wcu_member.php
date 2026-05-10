<?php
/**
 * Single member profile (WPFolks-style hero card layout).
 *
 * The hero is a rounded dark card centered in the container. Inside,
 * the left column carries identity (avatar, name, role, location, socials,
 * action buttons) and the right column carries the bio and skill tags.
 * Below the card sit availability pills, an info grid (company / hosting
 * / WP version / birthday), and a specialties chip cluster.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$wcu_member_id          = get_the_ID();
	$wcu_role_title         = get_post_meta( $wcu_member_id, '_wcu_member_role_title', true );
	$wcu_pronouns           = get_post_meta( $wcu_member_id, '_wcu_member_pronouns', true );
	$wcu_short_bio          = get_post_meta( $wcu_member_id, '_wcu_member_short_bio', true );
	$wcu_company            = get_post_meta( $wcu_member_id, '_wcu_member_company', true );
	$wcu_hosting            = get_post_meta( $wcu_member_id, '_wcu_member_preferred_hosting', true );
	$wcu_first_wp           = get_post_meta( $wcu_member_id, '_wcu_member_first_wp_version', true );
	$wcu_birthday           = get_post_meta( $wcu_member_id, '_wcu_member_birthday', true );
	$wcu_specialties        = get_post_meta( $wcu_member_id, '_wcu_member_specialties', true );
	$wcu_wporg              = get_post_meta( $wcu_member_id, '_wcu_member_wporg_username', true );
	$wcu_twitter            = get_post_meta( $wcu_member_id, '_wcu_member_twitter', true );
	$wcu_github             = get_post_meta( $wcu_member_id, '_wcu_member_github', true );
	$wcu_linkedin           = get_post_meta( $wcu_member_id, '_wcu_member_linkedin', true );
	$wcu_website            = get_post_meta( $wcu_member_id, '_wcu_member_website', true );
	$wcu_is_speaker         = '1' === get_post_meta( $wcu_member_id, '_wcu_member_speaker', true );
	$wcu_is_organizer       = '1' === get_post_meta( $wcu_member_id, '_wcu_member_organizer', true );
	$wcu_is_featured        = '1' === get_post_meta( $wcu_member_id, '_wcu_member_featured', true );
	$wcu_is_for_hire        = '1' === get_post_meta( $wcu_member_id, '_wcu_member_available_for_hire', true );
	$wcu_open_sponsorship   = '1' === get_post_meta( $wcu_member_id, '_wcu_member_open_sponsorship', true );
	$wcu_open_volunteering  = '1' === get_post_meta( $wcu_member_id, '_wcu_member_open_volunteering', true );

	$wcu_chapter_terms = get_the_terms( $wcu_member_id, 'wcu_chapter_tax' );
	$wcu_skill_terms   = get_the_terms( $wcu_member_id, 'wcu_skills' );
	$wcu_role_terms    = get_the_terms( $wcu_member_id, 'wcu_member_role' );

	// Resilient WP.org username (handles full URL + leading @ paste).
	$wcu_wporg_username = '';
	if ( ! empty( $wcu_wporg ) ) {
		$wcu_wporg_username = $wcu_wporg;
		if ( false !== strpos( $wcu_wporg_username, 'profiles.wordpress.org' ) ) {
			$wcu_parsed = wp_parse_url( $wcu_wporg_username );
			if ( ! empty( $wcu_parsed['path'] ) ) {
				$wcu_wporg_username = trim( $wcu_parsed['path'], '/' );
			}
		}
		$wcu_wporg_username = ltrim( $wcu_wporg_username, '@' );
	}

	$wcu_handle = ! empty( $wcu_wporg_username ) ? $wcu_wporg_username : get_post_field( 'post_name', $wcu_member_id );

	// Fall back to the WordPress.org avatar when no featured image is set.
	$wcu_wporg_avatar = '';
	if ( ! has_post_thumbnail() && ! empty( $wcu_wporg_username ) && class_exists( 'WCU_WPOrg_Profiles' ) ) {
		$wcu_wporg_avatar = WCU_WPOrg_Profiles::get_avatar_url( $wcu_wporg_username, 256 );
	}

	$wcu_socials = array_filter(
		array(
			'twitter'   => $wcu_twitter,
			'github'    => $wcu_github,
			'linkedin'  => $wcu_linkedin,
			'wordpress' => ! empty( $wcu_wporg_username ) ? 'https://profiles.wordpress.org/' . rawurlencode( $wcu_wporg_username ) . '/' : '',
		)
	);

	$wcu_specialty_list = array_filter( array_map( 'trim', explode( ',', (string) $wcu_specialties ) ) );

	$wcu_join_date = get_the_date( 'M Y', $wcu_member_id );

	$wcu_share_title = wp_strip_all_tags( get_the_title( $wcu_member_id ) );
	$wcu_share_url   = get_permalink( $wcu_member_id );

	// Pull contact email from chapter or fallback to admin email; site_admin email
	// would be tighter but admins can override via member website if wanted.
	$wcu_contact_email = get_option( 'admin_email' );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'wcu-member-single wcu-member-single--folks' ); ?>>

		<div class="container">
			<a class="wcu-event-hero__back wcu-member-single__back" href="<?php echo esc_url( get_post_type_archive_link( 'wcu_member' ) ); ?>">
				<?php wcu_svg_icon( 'arrow-left', array( 'width' => 18, 'height' => 18 ) ); ?>
				<?php esc_html_e( 'All members', 'wcuganda' ); ?>
			</a>
		</div>

		<div class="container">
			<header class="wcu-folks-hero">

				<div class="wcu-folks-hero__identity">
					<div class="wcu-folks-hero__avatar">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'wcu-avatar' ); ?>
						<?php elseif ( ! empty( $wcu_wporg_avatar ) ) : ?>
							<img src="<?php echo esc_url( $wcu_wporg_avatar ); ?>"
								alt="<?php echo esc_attr( get_the_title() ); ?>"
								loading="lazy">
						<?php else : ?>
							<span class="wcu-folks-hero__avatar-placeholder" aria-hidden="true">
								<?php echo esc_html( strtoupper( mb_substr( get_the_title(), 0, 1 ) ) ); ?>
							</span>
						<?php endif; ?>
					</div>

					<div class="wcu-folks-hero__name-row">
						<h1 class="wcu-folks-hero__name"><?php the_title(); ?></h1>
						<?php if ( $wcu_is_featured ) : ?>
							<span class="wcu-folks-hero__verified" aria-label="<?php esc_attr_e( 'Featured contributor', 'wcuganda' ); ?>" title="<?php esc_attr_e( 'Featured contributor', 'wcuganda' ); ?>">★</span>
						<?php endif; ?>
						<?php if ( ! empty( $wcu_pronouns ) ) : ?>
							<span class="wcu-folks-hero__pronouns"><?php echo esc_html( $wcu_pronouns ); ?></span>
						<?php endif; ?>
					</div>

					<p class="wcu-folks-hero__handle">@<?php echo esc_html( $wcu_handle ); ?></p>

					<?php if ( ! empty( $wcu_role_title ) ) : ?>
						<p class="wcu-folks-hero__role"><?php echo esc_html( $wcu_role_title ); ?></p>
					<?php endif; ?>

					<ul class="wcu-folks-hero__meta">
						<?php if ( $wcu_chapter_terms && ! is_wp_error( $wcu_chapter_terms ) ) : ?>
							<li>
								<?php wcu_svg_icon( 'map-pin', array( 'width' => 14, 'height' => 14 ) ); ?>
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
							</li>
						<?php endif; ?>
						<li>
							<?php wcu_svg_icon( 'calendar', array( 'width' => 14, 'height' => 14 ) ); ?>
							<?php
							printf(
								/* translators: %s: month + year, e.g. May 2026. */
								esc_html__( 'Since %s', 'wcuganda' ),
								esc_html( $wcu_join_date )
							);
							?>
						</li>
					</ul>

					<?php if ( ! empty( $wcu_socials ) ) : ?>
						<ul class="wcu-folks-hero__socials" aria-label="<?php esc_attr_e( 'Social links', 'wcuganda' ); ?>">
							<?php foreach ( $wcu_socials as $wcu_network => $wcu_url ) : ?>
								<li>
									<a href="<?php echo esc_url( $wcu_url ); ?>" rel="me noopener" target="_blank" aria-label="<?php echo esc_attr( ucfirst( $wcu_network ) ); ?>">
										<?php wcu_svg_icon( $wcu_network, array( 'width' => 16, 'height' => 16 ) ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<div class="wcu-folks-hero__about">
					<?php if ( ! empty( $wcu_short_bio ) ) : ?>
						<p class="wcu-folks-hero__bio"><?php echo esc_html( $wcu_short_bio ); ?></p>
					<?php endif; ?>

					<?php if ( $wcu_skill_terms && ! is_wp_error( $wcu_skill_terms ) ) : ?>
						<ul class="wcu-folks-hero__tags" aria-label="<?php esc_attr_e( 'Skills', 'wcuganda' ); ?>">
							<?php foreach ( $wcu_skill_terms as $wcu_term ) : ?>
								<li>
									<a class="wcu-folks-hero__tag" href="<?php echo esc_url( get_term_link( $wcu_term ) ); ?>">
										<?php echo esc_html( $wcu_term->name ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<div class="wcu-folks-hero__actions">
					<a class="wcu-btn wcu-btn--sm" href="mailto:<?php echo esc_attr( $wcu_contact_email ); ?>?subject=<?php echo esc_attr( rawurlencode( sprintf( /* translators: %s: member name. */ __( 'Hi %s', 'wcuganda' ), get_the_title( $wcu_member_id ) ) ) ); ?>">
						<?php wcu_svg_icon( 'mail', array( 'width' => 14, 'height' => 14 ) ); ?>
						<?php esc_html_e( 'Contact', 'wcuganda' ); ?>
					</a>
					<button class="wcu-btn wcu-btn--sm wcu-btn--outline wcu-folks-share"
						type="button"
						data-share-title="<?php echo esc_attr( $wcu_share_title ); ?>"
						data-share-url="<?php echo esc_url( $wcu_share_url ); ?>">
						<?php esc_html_e( 'Share', 'wcuganda' ); ?>
					</button>
					<?php if ( ! empty( $wcu_website ) ) : ?>
						<a class="wcu-btn wcu-btn--sm wcu-btn--outline" href="<?php echo esc_url( $wcu_website ); ?>" rel="me noopener" target="_blank">
							<?php esc_html_e( 'Website', 'wcuganda' ); ?>
							<?php wcu_svg_icon( 'arrow-right', array( 'width' => 14, 'height' => 14 ) ); ?>
						</a>
					<?php endif; ?>
				</div>

			</header>

			<?php if ( $wcu_is_for_hire || $wcu_open_sponsorship || $wcu_open_volunteering ) : ?>
				<div class="wcu-folks-availability">
					<span class="wcu-folks-availability__label"><?php esc_html_e( 'Availability', 'wcuganda' ); ?></span>
					<ul class="wcu-folks-availability__list" aria-label="<?php esc_attr_e( 'Availability', 'wcuganda' ); ?>">
						<?php if ( $wcu_is_for_hire ) : ?>
							<li><span class="wcu-folks-availability__pill wcu-folks-availability__pill--hire"><?php esc_html_e( 'Available for hire', 'wcuganda' ); ?></span></li>
						<?php endif; ?>
						<?php if ( $wcu_open_sponsorship ) : ?>
							<li><span class="wcu-folks-availability__pill wcu-folks-availability__pill--sponsor"><?php esc_html_e( 'Open to sponsorship', 'wcuganda' ); ?></span></li>
						<?php endif; ?>
						<?php if ( $wcu_open_volunteering ) : ?>
							<li><span class="wcu-folks-availability__pill wcu-folks-availability__pill--volunteer"><?php esc_html_e( 'Open to volunteering', 'wcuganda' ); ?></span></li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $wcu_company ) || ! empty( $wcu_hosting ) || ! empty( $wcu_first_wp ) || ! empty( $wcu_birthday ) ) : ?>
				<dl class="wcu-folks-info">
					<?php if ( ! empty( $wcu_company ) ) : ?>
						<div class="wcu-folks-info__row">
							<dt><?php esc_html_e( 'Company', 'wcuganda' ); ?></dt>
							<dd><?php echo esc_html( $wcu_company ); ?></dd>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $wcu_hosting ) ) : ?>
						<div class="wcu-folks-info__row">
							<dt><?php esc_html_e( 'Preferred hosting', 'wcuganda' ); ?></dt>
							<dd><?php echo esc_html( $wcu_hosting ); ?></dd>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $wcu_first_wp ) ) : ?>
						<div class="wcu-folks-info__row">
							<dt><?php esc_html_e( 'First WP version used', 'wcuganda' ); ?></dt>
							<dd><?php echo esc_html( $wcu_first_wp ); ?></dd>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $wcu_birthday ) ) : ?>
						<div class="wcu-folks-info__row">
							<dt><?php esc_html_e( 'Birthday', 'wcuganda' ); ?></dt>
							<dd><?php echo esc_html( $wcu_birthday ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>
			<?php endif; ?>

			<?php if ( ! empty( $wcu_specialty_list ) ) : ?>
				<div class="wcu-folks-specialties">
					<span class="wcu-folks-specialties__label"><?php esc_html_e( 'Specialties', 'wcuganda' ); ?></span>
					<ul class="wcu-folks-specialties__list">
						<?php foreach ( $wcu_specialty_list as $wcu_specialty ) : ?>
							<li><span class="wcu-folks-specialties__chip"><?php echo esc_html( $wcu_specialty ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( $wcu_role_terms && ! is_wp_error( $wcu_role_terms ) ) : ?>
				<div class="wcu-folks-roles">
					<span class="wcu-folks-roles__label"><?php esc_html_e( 'Roles', 'wcuganda' ); ?></span>
					<ul class="wcu-folks-roles__list">
						<?php foreach ( $wcu_role_terms as $wcu_term ) : ?>
							<li>
								<a class="wcu-folks-specialties__chip" href="<?php echo esc_url( get_term_link( $wcu_term ) ); ?>">
									<?php echo esc_html( $wcu_term->name ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<section class="wcu-folks-bio">
					<h2 class="wcu-folks-bio__heading"><?php esc_html_e( 'About', 'wcuganda' ); ?></h2>
					<div class="wcu-folks-bio__content">
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
				</section>
			<?php endif; ?>

			<?php
			// =========================================================
			// WordPress.org credentials — badges, plugins, themes, activity.
			// Section ALWAYS renders when a wp.org username is set (even with
			// no fetched data) so the visitor sees the connection + a clear
			// link to the upstream profile.
			// =========================================================
			if ( ! empty( $wcu_wporg_username ) ) :
				$wcu_badges   = class_exists( 'WCU_WPOrg_Profiles' ) ? WCU_WPOrg_Profiles::get_badges( $wcu_wporg_username ) : array();
				$wcu_plugins  = class_exists( 'WCU_WPOrg_Repo' ) ? WCU_WPOrg_Repo::get_plugins( $wcu_wporg_username, 12 ) : array();
				$wcu_themes   = class_exists( 'WCU_WPOrg_Repo' ) ? WCU_WPOrg_Repo::get_themes( $wcu_wporg_username, 12 ) : array();
				$wcu_activity = class_exists( 'WCU_WPOrg_Profiles' ) ? WCU_WPOrg_Profiles::get_activity( $wcu_wporg_username, 6 ) : array();

				$wcu_total_installs = 0;
				foreach ( $wcu_plugins as $wcu_p ) {
					$wcu_total_installs += isset( $wcu_p['active_installs'] ) ? (int) $wcu_p['active_installs'] : 0;
				}
				$wcu_has_any = ! empty( $wcu_badges ) || ! empty( $wcu_plugins ) || ! empty( $wcu_themes ) || ! empty( $wcu_activity );
				$wcu_profile_url = 'https://profiles.wordpress.org/' . rawurlencode( $wcu_wporg_username ) . '/';
				?>

				<section class="wcu-folks-credentials">

					<header class="wcu-folks-credentials__header">
						<div class="wcu-folks-credentials__title-wrap">
							<h2 class="wcu-folks-credentials__heading"><?php esc_html_e( 'On WordPress.org', 'wcuganda' ); ?></h2>
							<p class="wcu-folks-credentials__lead">
								<?php
								printf(
									/* translators: %s: linked wp.org handle. */
									esc_html__( 'Live data pulled from %s.', 'wcuganda' ),
									'<a href="' . esc_url( $wcu_profile_url ) . '" rel="noopener" target="_blank">@' . esc_html( $wcu_wporg_username ) . '</a>'
								);
								?>
							</p>
						</div>
						<a class="wcu-folks-credentials__profile-link" href="<?php echo esc_url( $wcu_profile_url ); ?>" rel="noopener" target="_blank">
							<?php esc_html_e( 'View full profile', 'wcuganda' ); ?>
							<?php wcu_svg_icon( 'arrow-right', array( 'width' => 14, 'height' => 14 ) ); ?>
						</a>
					</header>

					<ul class="wcu-folks-stats" aria-label="<?php esc_attr_e( 'WordPress.org summary', 'wcuganda' ); ?>">
						<li class="wcu-folks-stat">
							<span class="wcu-folks-stat__value"><?php echo esc_html( number_format_i18n( count( $wcu_badges ) ) ); ?></span>
							<span class="wcu-folks-stat__label"><?php esc_html_e( 'Badges', 'wcuganda' ); ?></span>
						</li>
						<li class="wcu-folks-stat">
							<span class="wcu-folks-stat__value"><?php echo esc_html( number_format_i18n( count( $wcu_plugins ) ) ); ?></span>
							<span class="wcu-folks-stat__label"><?php esc_html_e( 'Plugins', 'wcuganda' ); ?></span>
						</li>
						<li class="wcu-folks-stat">
							<span class="wcu-folks-stat__value"><?php echo esc_html( number_format_i18n( count( $wcu_themes ) ) ); ?></span>
							<span class="wcu-folks-stat__label"><?php esc_html_e( 'Themes', 'wcuganda' ); ?></span>
						</li>
						<li class="wcu-folks-stat">
							<span class="wcu-folks-stat__value">
								<?php echo esc_html( $wcu_total_installs >= 1000 ? number_format_i18n( $wcu_total_installs ) . '+' : number_format_i18n( $wcu_total_installs ) ); ?>
							</span>
							<span class="wcu-folks-stat__label"><?php esc_html_e( 'Active installs', 'wcuganda' ); ?></span>
						</li>
					</ul>

					<?php if ( ! empty( $wcu_badges ) ) : ?>
						<div class="wcu-folks-credentials__group">
							<h3 class="wcu-folks-credentials__group-heading wcu-folks-credentials__group-heading--lg"><?php esc_html_e( 'Contribution History', 'wcuganda' ); ?></h3>
							<ul class="wcu-folks-badges">
								<?php foreach ( $wcu_badges as $wcu_badge ) : ?>
									<li>
										<span class="wcu-folks-badge wcu-folks-badge--<?php echo esc_attr( $wcu_badge['slug'] ); ?>">
											<span class="wcu-folks-badge__icon" aria-hidden="true"><?php wcu_dashicon_svg( ! empty( $wcu_badge["icon"] ) ? $wcu_badge["icon"] : "awards", array( "width" => 24, "height" => 24 ) ); ?></span>
											<span class="wcu-folks-badge__name"><?php echo esc_html( $wcu_badge['name'] ); ?></span>
										</span>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $wcu_plugins ) ) : ?>
						<div class="wcu-folks-credentials__group">
							<h3 class="wcu-folks-credentials__group-heading">
								<?php
								printf(
									/* translators: %d: count. */
									esc_html( _n( 'Plugin authored (%d)', 'Plugins authored (%d)', count( $wcu_plugins ), 'wcuganda' ) ),
									(int) count( $wcu_plugins )
								);
								?>
							</h3>
							<ul class="wcu-folks-projects">
								<?php foreach ( $wcu_plugins as $wcu_plugin ) : ?>
									<li class="wcu-folks-project">
										<a class="wcu-folks-project__link" href="<?php echo esc_url( $wcu_plugin['url'] ); ?>" rel="noopener" target="_blank">
											<?php if ( ! empty( $wcu_plugin['icon'] ) ) : ?>
												<img class="wcu-folks-project__icon" src="<?php echo esc_url( $wcu_plugin['icon'] ); ?>" alt="" loading="lazy">
											<?php else : ?>
												<span class="wcu-folks-project__icon wcu-folks-project__icon--placeholder" aria-hidden="true">
													<?php echo esc_html( strtoupper( mb_substr( $wcu_plugin['name'], 0, 1 ) ) ); ?>
												</span>
											<?php endif; ?>
											<span class="wcu-folks-project__body">
												<span class="wcu-folks-project__name"><?php echo esc_html( $wcu_plugin['name'] ); ?></span>
												<?php if ( ! empty( $wcu_plugin['description'] ) ) : ?>
													<span class="wcu-folks-project__desc"><?php echo esc_html( wp_trim_words( $wcu_plugin['description'], 18 ) ); ?></span>
												<?php endif; ?>
												<?php if ( $wcu_plugin['active_installs'] > 0 ) : ?>
													<span class="wcu-folks-project__meta">
														<?php
														printf(
															/* translators: %s: install count, e.g. "1,000+". */
															esc_html__( '%s+ active installs', 'wcuganda' ),
															esc_html( number_format_i18n( $wcu_plugin['active_installs'] ) )
														);
														?>
													</span>
												<?php endif; ?>
											</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $wcu_themes ) ) : ?>
						<div class="wcu-folks-credentials__group">
							<h3 class="wcu-folks-credentials__group-heading">
								<?php
								printf(
									/* translators: %d: count. */
									esc_html( _n( 'Theme authored (%d)', 'Themes authored (%d)', count( $wcu_themes ), 'wcuganda' ) ),
									(int) count( $wcu_themes )
								);
								?>
							</h3>
							<ul class="wcu-folks-projects wcu-folks-projects--themes">
								<?php foreach ( $wcu_themes as $wcu_theme ) : ?>
									<li class="wcu-folks-project">
										<a class="wcu-folks-project__link" href="<?php echo esc_url( $wcu_theme['url'] ); ?>" rel="noopener" target="_blank">
											<?php if ( ! empty( $wcu_theme['icon'] ) ) : ?>
												<img class="wcu-folks-project__icon wcu-folks-project__icon--theme" src="<?php echo esc_url( $wcu_theme['icon'] ); ?>" alt="" loading="lazy">
											<?php else : ?>
												<span class="wcu-folks-project__icon wcu-folks-project__icon--placeholder" aria-hidden="true">
													<?php echo esc_html( strtoupper( mb_substr( $wcu_theme['name'], 0, 1 ) ) ); ?>
												</span>
											<?php endif; ?>
											<span class="wcu-folks-project__body">
												<span class="wcu-folks-project__name"><?php echo esc_html( $wcu_theme['name'] ); ?></span>
												<?php if ( ! empty( $wcu_theme['description'] ) ) : ?>
													<span class="wcu-folks-project__desc"><?php echo esc_html( wp_trim_words( $wcu_theme['description'], 18 ) ); ?></span>
												<?php endif; ?>
											</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $wcu_activity ) ) : ?>
							<?php
							// Build the tab list dynamically — only show tabs that have items.
							$wcu_cat_labels = array(
								'blogs'       => __( 'Posts', 'wcuganda' ),
								'plugins'     => __( 'Plugins', 'wcuganda' ),
								'themes'      => __( 'Themes', 'wcuganda' ),
								'photos'      => __( 'Photos', 'wcuganda' ),
								'learn'       => __( 'Courses', 'wcuganda' ),
								'glotpress'   => __( 'Translations', 'wcuganda' ),
								'wordcamp'    => __( 'WordCamps', 'wcuganda' ),
								'favorites'   => __( 'Favorites', 'wcuganda' ),
							);
							$wcu_cat_counts = array();
							foreach ( $wcu_activity as $wcu_act ) {
								$wcu_cat_counts[ $wcu_act['category'] ] = ( $wcu_cat_counts[ $wcu_act['category'] ] ?? 0 ) + 1;
							}
							?>
							<div class="wcu-folks-activity-wrap" data-wcu-activity-tabs>
								<aside class="wcu-folks-activity__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Filter activity', 'wcuganda' ); ?>">
									<button type="button"
										class="wcu-folks-activity__tab is-active"
										role="tab"
										aria-selected="true"
										data-wcu-tab="all">
										<?php esc_html_e( 'Activity', 'wcuganda' ); ?>
										<span class="wcu-folks-activity__tab-count"><?php echo esc_html( count( $wcu_activity ) ); ?></span>
									</button>
									<?php foreach ( $wcu_cat_labels as $wcu_cat_slug => $wcu_cat_label ) : ?>
										<?php if ( empty( $wcu_cat_counts[ $wcu_cat_slug ] ) ) { continue; } ?>
										<button type="button"
											class="wcu-folks-activity__tab"
											role="tab"
											aria-selected="false"
											data-wcu-tab="<?php echo esc_attr( $wcu_cat_slug ); ?>">
											<?php echo esc_html( $wcu_cat_label ); ?>
											<span class="wcu-folks-activity__tab-count"><?php echo esc_html( $wcu_cat_counts[ $wcu_cat_slug ] ); ?></span>
										</button>
									<?php endforeach; ?>
								</aside>

								<ul class="wcu-folks-activity" role="tabpanel">
									<?php foreach ( $wcu_activity as $wcu_act ) : ?>
										<li class="wcu-folks-activity__item wcu-folks-activity__item--<?php echo esc_attr( sanitize_html_class( $wcu_act['type'] ) ); ?>"
											data-wcu-activity-cat="<?php echo esc_attr( $wcu_act['category'] ); ?>">
											<span class="wcu-folks-activity__icon" aria-hidden="true">
												<?php
												$wcu_act_dashicon = 'admin-site';
												switch ( $wcu_act['category'] ) {
													case 'blogs':     $wcu_act_dashicon = 'edit'; break;
													case 'plugins':   $wcu_act_dashicon = 'admin-plugins'; break;
													case 'themes':    $wcu_act_dashicon = 'admin-appearance'; break;
													case 'photos':    $wcu_act_dashicon = 'camera'; break;
													case 'learn':     $wcu_act_dashicon = 'welcome-learn-more'; break;
													case 'glotpress': $wcu_act_dashicon = 'translation'; break;
													case 'wordcamp':  $wcu_act_dashicon = 'tickets-alt'; break;
													case 'favorites': $wcu_act_dashicon = 'star-filled'; break;
												}
												?>
												<?php wcu_dashicon_svg( $wcu_act_dashicon, array( "width" => 16, "height" => 16 ) ); ?>
											</span>
											<div class="wcu-folks-activity__body">
												<p class="wcu-folks-activity__action">
													<?php
													echo wp_kses(
														$wcu_act['action'],
														array(
															'a'      => array( 'href' => array(), 'title' => array() ),
															'strong' => array(),
															'em'     => array(),
															'i'      => array(),
															'span'   => array(),
														)
													);
													?>
												</p>
												<?php if ( ! empty( $wcu_act['excerpt'] ) ) : ?>
													<p class="wcu-folks-activity__excerpt"><?php echo esc_html( $wcu_act['excerpt'] ); ?></p>
												<?php endif; ?>
												<?php if ( ! empty( $wcu_act['time'] ) ) : ?>
													<span class="wcu-folks-activity__time"><?php echo esc_html( $wcu_act['time'] ); ?></span>
												<?php endif; ?>
											</div>
										</li>
									<?php endforeach; ?>
									<li class="wcu-folks-activity__empty" hidden>
										<?php esc_html_e( 'No items in this category yet.', 'wcuganda' ); ?>
									</li>
								</ul>
							</div>
						<?php endif; ?>

						<?php if ( ! $wcu_has_any ) : ?>
							<p class="wcu-folks-credentials__empty">
								<?php
								printf(
									/* translators: %s: linked wp.org profile URL. */
									esc_html__( 'No public WordPress.org contributions yet for this username. %s to see the live profile.', 'wcuganda' ),
									'<a class="text-brand-link" href="' . esc_url( $wcu_profile_url ) . '" rel="noopener" target="_blank">' . esc_html__( 'Open the profile', 'wcuganda' ) . '</a>'
								);
								?>
							</p>
						<?php endif; ?>

					</section>
					<?php
				endif;
				?>

			</div>

		</article>

		<?php
	endwhile;

	get_footer();
