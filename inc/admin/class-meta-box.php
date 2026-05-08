<?php
/**
 * Reusable meta box class. Handles registration, rendering, and saving for a
 * group of meta fields on a single post type.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCU_Meta_Box
 */
class WCU_Meta_Box {

	/**
	 * Meta box id (also used as nonce action).
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Visible title shown in the editor.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Post type slug this box renders for.
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * Field config: meta_key => array of label, type, options, etc.
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * Editor context: 'normal', 'side', 'advanced'.
	 *
	 * @var string
	 */
	protected $context;

	/**
	 * Editor priority: 'high', 'core', 'default', 'low'.
	 *
	 * @var string
	 */
	protected $priority;

	/**
	 * Constructor + immediate registration via WP hooks.
	 *
	 * @param string $id        Meta box id.
	 * @param string $title     Visible title.
	 * @param string $post_type Post type slug.
	 * @param array  $fields    Field config.
	 * @param string $context   Editor context.
	 * @param string $priority  Editor priority.
	 */
	public function __construct( $id, $title, $post_type, $fields, $context = 'normal', $priority = 'high' ) {
		$this->id        = $id;
		$this->title     = $title;
		$this->post_type = $post_type;
		$this->fields    = $fields;
		$this->context   = $context;
		$this->priority  = $priority;

		add_action( 'add_meta_boxes', array( $this, 'register' ) );
		add_action( 'save_post_' . $post_type, array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Register the meta box with WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_meta_box(
			$this->id,
			$this->title,
			array( $this, 'render' ),
			$this->post_type,
			$this->context,
			$this->priority
		);
	}

	/**
	 * Render the meta box markup.
	 *
	 * @param WP_Post $post Current post.
	 * @return void
	 */
	public function render( $post ) {
		wp_nonce_field( 'wcu_save_' . $this->id, 'wcu_' . $this->id . '_nonce' );

		echo '<table class="form-table"><tbody>';

		foreach ( $this->fields as $key => $config ) {
			$value = get_post_meta( $post->ID, $key, true );
			$this->render_field( $key, $value, $config );
		}

		echo '</tbody></table>';
	}

	/**
	 * Render a single field row.
	 *
	 * @param string $key    Meta key.
	 * @param mixed  $value  Stored value.
	 * @param array  $config Field config.
	 * @return void
	 */
	protected function render_field( $key, $value, $config ) {
		$type        = isset( $config['type'] ) ? $config['type'] : 'text';
		$label       = isset( $config['label'] ) ? $config['label'] : $key;
		$placeholder = isset( $config['placeholder'] ) ? $config['placeholder'] : '';
		$description = isset( $config['description'] ) ? $config['description'] : '';
		$options     = isset( $config['options'] ) ? $config['options'] : array();
		$required    = ! empty( $config['required'] );

		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s%3$s</label></th><td>',
			esc_attr( $key ),
			esc_html( $label ),
			$required ? ' <span aria-hidden="true">*</span>' : ''
		);

		switch ( $type ) {
			case 'textarea':
				printf(
					'<textarea id="%1$s" name="%1$s" rows="4" class="large-text" placeholder="%2$s">%3$s</textarea>',
					esc_attr( $key ),
					esc_attr( $placeholder ),
					esc_textarea( (string) $value )
				);
				break;

			case 'checkbox':
				printf(
					'<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s> %3$s</label>',
					esc_attr( $key ),
					checked( $value, '1', false ),
					esc_html( isset( $config['checkbox_label'] ) ? $config['checkbox_label'] : __( 'Yes', 'wcuganda' ) )
				);
				break;

			case 'select':
				printf( '<select id="%1$s" name="%1$s" class="regular-text">', esc_attr( $key ) );
				echo '<option value="">' . esc_html__( '— Select —', 'wcuganda' ) . '</option>';
				foreach ( $options as $opt_value => $opt_label ) {
					printf(
						'<option value="%1$s" %3$s>%2$s</option>',
						esc_attr( $opt_value ),
						esc_html( $opt_label ),
						selected( $value, $opt_value, false )
					);
				}
				echo '</select>';
				break;

			case 'date':
			case 'time':
			case 'datetime-local':
			case 'number':
			case 'email':
			case 'url':
			case 'text':
			default:
				printf(
					'<input type="%1$s" id="%2$s" name="%2$s" value="%3$s" class="regular-text" placeholder="%4$s" %5$s>',
					esc_attr( $type ),
					esc_attr( $key ),
					esc_attr( (string) $value ),
					esc_attr( $placeholder ),
					$required ? 'required' : ''
				);
				break;
		}

		if ( ! empty( $description ) ) {
			printf( '<p class="description">%s</p>', esc_html( $description ) );
		}

		echo '</td></tr>';
	}

	/**
	 * Persist field values when the post is saved.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public function save( $post_id, $post ) {
		// Bail if autosave/revision/AJAX/cron.
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$nonce_key = 'wcu_' . $this->id . '_nonce';
		if ( ! isset( $_POST[ $nonce_key ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_key ] ) ), 'wcu_save_' . $this->id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $this->fields as $key => $config ) {
			$type = isset( $config['type'] ) ? $config['type'] : 'text';

			if ( 'checkbox' === $type ) {
				update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '' );
				continue;
			}

			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}

			$raw       = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below per type.
			$sanitized = $this->sanitize_field( $raw, $type );
			update_post_meta( $post_id, $key, $sanitized );
		}
	}

	/**
	 * Sanitize a raw field value based on its declared type.
	 *
	 * @param mixed  $raw  Raw input.
	 * @param string $type Declared field type.
	 * @return mixed Sanitized value.
	 */
	protected function sanitize_field( $raw, $type ) {
		switch ( $type ) {
			case 'url':
				return esc_url_raw( (string) $raw );
			case 'email':
				return sanitize_email( (string) $raw );
			case 'number':
				return is_numeric( $raw ) ? (string) (int) $raw : '';
			case 'textarea':
				return wp_kses_post( (string) $raw );
			case 'date':
			case 'time':
			case 'datetime-local':
				return sanitize_text_field( (string) $raw );
			case 'select':
			case 'text':
			default:
				return sanitize_text_field( (string) $raw );
		}
	}
}
