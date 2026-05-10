<?php
/**
 * Reusable application form (volunteer or speaker variant).
 *
 * Caller passes $args['type'] = 'volunteer' | 'speaker'.
 *
 * Includes:
 * - wp_nonce_field for CSRF
 * - hidden time-stamp + honeypot for anti-spam
 * - shared fields: name, email, chapter, bio, message, social
 * - speaker-only fields: talk title, talk abstract
 * - Submission state inferred from ?wcu_app=success|invalid in URL
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu_form_type = ( isset( $args['type'] ) && in_array( $args['type'], array( 'volunteer', 'speaker' ), true ) )
	? $args['type']
	: 'volunteer';

$wcu_state_status = isset( $_GET['wcu_app'] ) ? sanitize_key( wp_unslash( $_GET['wcu_app'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$wcu_state_type   = isset( $_GET['wcu_app_type'] ) ? sanitize_key( wp_unslash( $_GET['wcu_app_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$wcu_show_success = ( 'success' === $wcu_state_status && $wcu_state_type === $wcu_form_type );
$wcu_show_invalid = ( 'invalid' === $wcu_state_status && $wcu_state_type === $wcu_form_type );

$wcu_chapters = get_terms(
	array(
		'taxonomy'   => 'wcu_chapter_tax',
		'hide_empty' => false,
	)
);
?>

<div id="wcu-app-form" class="wcu-app-form">

	<?php if ( $wcu_show_success ) : ?>
		<div class="wcu-app-form__notice wcu-app-form__notice--success" role="status">
			<p class="wcu-empty__title">
				<?php
				if ( 'speaker' === $wcu_form_type ) {
					esc_html_e( 'Talk submitted', 'wcuganda' );
				} else {
					esc_html_e( 'Application received', 'wcuganda' );
				}
				?>
			</p>
			<p class="wcu-empty__desc">
				<?php esc_html_e( 'Thanks — an organizer will be in touch within a few days. Meanwhile, follow us for updates.', 'wcuganda' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( $wcu_show_invalid ) : ?>
		<div class="wcu-app-form__notice wcu-app-form__notice--error" role="alert">
			<p class="wcu-empty__title"><?php esc_html_e( 'Submission failed', 'wcuganda' ); ?></p>
			<p class="wcu-empty__desc">
				<?php esc_html_e( 'A required field was missing or the form expired. Refresh and try again.', 'wcuganda' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<form class="wcu-form wcu-app-form__form"
		method="post"
		action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		novalidate>

		<input type="hidden" name="action" value="wcu_application_submit">
		<input type="hidden" name="wcu_app_type" value="<?php echo esc_attr( $wcu_form_type ); ?>">
		<input type="hidden" name="wcu_app_ts" value="<?php echo esc_attr( time() ); ?>">

		<?php wp_nonce_field( 'wcu_app_' . $wcu_form_type, 'wcu_app_nonce' ); ?>

		<div class="wcu-app-form__honeypot" aria-hidden="true">
			<label>
				<?php esc_html_e( 'If you are human, leave this field blank.', 'wcuganda' ); ?>
				<input type="text" name="wcu_app_website" autocomplete="off" tabindex="-1">
			</label>
		</div>

		<div class="wcu-form__row">
			<div class="wcu-form__group">
				<label for="wcu-app-name"><?php esc_html_e( 'Your name', 'wcuganda' ); ?> <span aria-hidden="true">*</span></label>
				<input type="text" id="wcu-app-name" name="wcu_app_name" autocomplete="name" required>
			</div>

			<div class="wcu-form__group">
				<label for="wcu-app-email"><?php esc_html_e( 'Email', 'wcuganda' ); ?> <span aria-hidden="true">*</span></label>
				<input type="email" id="wcu-app-email" name="wcu_app_email" autocomplete="email" required>
			</div>
		</div>

		<div class="wcu-form__row">
			<div class="wcu-form__group">
				<label for="wcu-app-chapter"><?php esc_html_e( 'Closest chapter', 'wcuganda' ); ?></label>
				<select id="wcu-app-chapter" name="wcu_app_chapter">
					<option value=""><?php esc_html_e( '— Select —', 'wcuganda' ); ?></option>
					<?php if ( ! is_wp_error( $wcu_chapters ) && ! empty( $wcu_chapters ) ) : ?>
						<?php foreach ( $wcu_chapters as $wcu_chapter ) : ?>
							<option value="<?php echo esc_attr( $wcu_chapter->name ); ?>"><?php echo esc_html( $wcu_chapter->name ); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
					<option value="other"><?php esc_html_e( 'Other / not listed', 'wcuganda' ); ?></option>
				</select>
			</div>

			<div class="wcu-form__group">
				<label for="wcu-app-skills">
					<?php
					if ( 'speaker' === $wcu_form_type ) {
						esc_html_e( 'Topics you cover', 'wcuganda' );
					} else {
						esc_html_e( 'Skills / interests', 'wcuganda' );
					}
					?>
				</label>
				<input type="text" id="wcu-app-skills" name="wcu_app_skills"
					placeholder="<?php
					if ( 'speaker' === $wcu_form_type ) {
						esc_attr_e( 'e.g. Gutenberg, Performance, Accessibility', 'wcuganda' );
					} else {
						esc_attr_e( 'e.g. Frontend, Photography, Logistics', 'wcuganda' );
					}
					?>">
			</div>
		</div>

		<?php if ( 'speaker' === $wcu_form_type ) : ?>
			<div class="wcu-form__group">
				<label for="wcu-app-talk-title"><?php esc_html_e( 'Talk title', 'wcuganda' ); ?></label>
				<input type="text" id="wcu-app-talk-title" name="wcu_app_talk_title" placeholder="<?php esc_attr_e( 'A working title is fine', 'wcuganda' ); ?>">
			</div>

			<div class="wcu-form__group">
				<label for="wcu-app-talk-abstract"><?php esc_html_e( 'Talk abstract', 'wcuganda' ); ?></label>
				<textarea id="wcu-app-talk-abstract" name="wcu_app_talk_abstract" rows="4"
					placeholder="<?php esc_attr_e( '2–3 paragraphs on what you would cover and who it is for.', 'wcuganda' ); ?>"></textarea>
			</div>
		<?php endif; ?>

		<div class="wcu-form__group">
			<label for="wcu-app-bio"><?php esc_html_e( 'Short bio', 'wcuganda' ); ?></label>
			<textarea id="wcu-app-bio" name="wcu_app_bio" rows="3"
				placeholder="<?php esc_attr_e( 'A line or two about you and what you work on.', 'wcuganda' ); ?>"></textarea>
		</div>

		<div class="wcu-form__group">
			<label for="wcu-app-message">
				<?php
				if ( 'speaker' === $wcu_form_type ) {
					esc_html_e( 'Anything else?', 'wcuganda' );
				} else {
					esc_html_e( 'Why would you like to volunteer?', 'wcuganda' );
				}
				?>
			</label>
			<textarea id="wcu-app-message" name="wcu_app_message" rows="4"></textarea>
		</div>

		<div class="wcu-form__row">
			<div class="wcu-form__group">
				<label for="wcu-app-twitter"><?php esc_html_e( 'Twitter / X (optional)', 'wcuganda' ); ?></label>
				<input type="url" id="wcu-app-twitter" name="wcu_app_twitter" placeholder="https://x.com/...">
			</div>

			<div class="wcu-form__group">
				<label for="wcu-app-github"><?php esc_html_e( 'GitHub (optional)', 'wcuganda' ); ?></label>
				<input type="url" id="wcu-app-github" name="wcu_app_github" placeholder="https://github.com/...">
			</div>
		</div>

		<div class="wcu-app-form__actions">
			<button type="submit" class="wcu-btn wcu-btn--lg">
				<?php
				if ( 'speaker' === $wcu_form_type ) {
					esc_html_e( 'Submit talk', 'wcuganda' );
				} else {
					esc_html_e( 'Submit application', 'wcuganda' );
				}
				?>
				<?php wcu_svg_icon( 'arrow-right', array( 'width' => 18, 'height' => 18 ) ); ?>
			</button>

			<p class="wcu-app-form__hint">
				<?php esc_html_e( 'We will reply within a few days.', 'wcuganda' ); ?>
			</p>
		</div>

	</form>

</div>
