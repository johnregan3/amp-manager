<?php
/**
 * Admin view handler.
 *
 * @package Customize_AMP
 */

namespace Customize_AMP;

/**
 * Class Admin
 *
 * @package Customize_AMP
 */
class Admin {
	const OPTION_GROUP = 'customize_amp';
	const OPTION_NAME = 'customize_amp_settings';
	const NONCE_NAME = 'customize_amp_nonce_name';
	const NONCE_ACTION = 'customize_amp_nonce_action';

	/**
	 * Config array.
	 *
	 * @var array
	 */
	public $config = array();

	/**
	 * Our settings array.
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * The class instance.
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Get the instance.
	 *
	 * @return Admin|object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->options = get_option( self::OPTION_NAME );

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'admin_notices', array( $this, 'amp_not_found' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}

	public function register_settings() {
		register_setting( self::OPTION_GROUP, self::OPTION_NAME );

		/*
		 * CPT Section
		 */
		add_settings_section(
			'custom_post_types',
			__( 'Custom Post Types', 'customize-amp' ),
			array( $this, 'empty_content' ),
			self::OPTION_GROUP
		);
		add_settings_field(
			'support_cpts',
			__( 'Custom Post Type Support', 'customize-amp' ),
			array( $this, 'render_field_support_cpts' ),
			self::OPTION_GROUP,
			'custom_post_types'
		);

		/*
		 * Clean Markup Section
		 */
		add_settings_section(
			'clean_content',
			__( 'Clean Up Content', 'customize-amp' ),
			array( $this, 'render_section_sanitize_content' ),
			self::OPTION_GROUP
		);

		add_settings_field(
			'clean_content',
			__( 'Enable Cleanup', 'customize-amp' ),
			array( $this, 'render_field_strip_invalid_markup' ),
			self::OPTION_GROUP,
			'clean_content'
		);

		add_settings_field(
			'strip_shortcodes',
			__( 'Strip Shortcodes', 'customize-amp' ),
			array( $this, 'render_field_strip_shortcodes' ),
			self::OPTION_GROUP,
			'clean_content'
		);
	}

	/**
	 * @return bool
	 */
	public function amp_not_found() {
		if ( is_plugin_active( 'amp/amp.php' ) ) {
			return false;
		}
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<a href="https://wordpress.org/plugins/amp/" target="_blank"><?php esc_html_e( 'The AMP plugin by Automattic', 'customize-amp' ); ?></a>&nbsp;<?php esc_html_e( 'is not installed or active.  These customizations will not be applied to the site.', 'customize-amp' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 *
	 */
	public function add_options_page() {
		add_options_page( __( 'Customize AMP', 'customize-amp' ), __( 'Customize AMP', 'customize-amp' ), 'manage_options', 'customize_amp', array(
			$this,
			'render_options_page'
		) );
	}

	/**
	 *
	 */
	public function render_options_page() {
		?>
		<div class="wrap">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<div class="constrain-width" style="max-width: 700px; width: 90%;">
				<form method="post" action="options.php" enctype="multipart/form-data">
					<?php settings_fields( self::OPTION_GROUP ); ?>
					<?php do_settings_sections( self::OPTION_GROUP ); ?>
					<?php submit_button(); ?>
				</form>
			</div>
		</div>
		<?php
	}

	public function empty_content() {}


	/**
	 * @return bool
	 */
	public function render_field_support_cpts() {
		?>
		<p class="description">
			<?php esc_html_e( 'In addition to the default content supported by the WP AMP plugin, apply AMP functionality to these Custom Post Types:', 'customize-amp' ); ?>
		</p>
		<?php
		$column_length = 3;
		$cpts = $this->get_cpts();

		if ( empty( $cpts ) || ! is_array( $cpts ) ) {
			echo '<p>' . esc_html__( 'No registered Custom Post Types found.', 'customize-amp' ) . '</p>';
			return false;
		}

		echo '<ul class="item-list">';
		$i     = 1;
		$count = count( $cpts );
		foreach ( $cpts as $cpt ) :
			$value = ! empty( $this->options['register_cpts'][ $cpt->name ] ) ? 1 : 0;
			?>
			<li>
				<input type="checkbox" name="<?php echo self::OPTION_NAME . '[register_cpts][' . esc_attr( $cpt->name ) . ']'; ?>"<?php checked( $value, 1 )?>><?php echo esc_html( $cpt->label ); ?>
			</li>
			<?php if ( ( 0 === $i % $column_length ) && $i !== $count ) : ?>
				</ul>
				<ul class="item-list">
			<?php endif;
			$i++;
		endforeach; ?>
		</ul>
		<?php
	}

	public function render_section_sanitize_content() {
		?>
		<p>
			<?php esc_html_e( 'AMP is very specific about what types of HTML tags it allows.  Having the wrong types of markup in your content will invalidate the entire document in AMP.  In this section, you will be able to enable filters to clean up your content as much as possible.', 'customize-amp' ); ?>&nbsp;&nbsp;
			<a href="https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags" target="_blank"><?php echo esc_html( 'Read the AMP Documentation', 'customize-amp' ); ?></a>
		</p>
		<p class="description">
			<?php esc_html_e( 'Note that this will only affect your post\'s content, not the entire document.', 'customize-amp' ); ?>
		</p>
		<?php
	}

	/**
	 *
	 */
	public function render_field_strip_invalid_markup() {
		$value = ! empty( $this->options['clean_content'] ) ? 1 : 0;
		?>
		<p class="description">
			<?php esc_html_e( 'By choosing this option, prohibited HTML will be removed from the post\'s content.', 'customize-amp' ); ?>
		</p>
		<p>
			<input type="checkbox" name="<?php echo self::OPTION_NAME . '[clean_content]'; ?>"<?php checked( $value, 1 )?>><?php esc_html_e( 'Clean up Content', 'customimze-amp' ); ?>
		</p>
		<?php

	}

	/**
	 * @return bool
	 */
	public function render_field_strip_shortcodes() {
		?>
		<p class="description">
			<?php esc_html_e( 'Choose shortcodes to strip from the content.', 'customize-amp' ); ?>
		</p>
		<?php
		$column_length = 4;
		$shortcodes = $this->get_shortcodes();

		if ( empty( $shortcodes ) || ! is_array( $shortcodes ) ) {
			echo '<p>' . esc_html__( 'No shortcodes found.', 'customize-amp' ) . '</p>';
			return false;
		}

		echo '<ul class="item-list">';
		$i = 1;
		$count = count( $shortcodes );
		foreach ( $shortcodes as $shortcode => $callback ) :
			$value = ! empty( $this->options['strip_shortcodes'][ $shortcode ] ) ? 1 : 0;
			?>
			<li>
				<input type="checkbox" name="<?php echo self::OPTION_NAME . '[strip_shortcodes][' . esc_attr( $shortcode ) . ']'; ?>"<?php checked( $value, 1 )?>>[<?php echo esc_html( $shortcode ); ?>]
			</li>
			<?php if ( ( 0 === $i % $column_length ) && $i !== $count ) : ?>
				</ul>
				<ul class="item-list">
			<?php endif;
			$i++;
		endforeach; ?>
		</ul>
		<div class="clear"></div>
		<p class="description">
			<?php esc_html_e( 'Shortcodes can introduce invalid markup into the AMP template, so it may be beneficial to remove them from the content.', 'customize-amp' ); ?>
		</p>
		<?php
	}

	/**
	 * @return mixed
	 */
	public function get_cpts() {
		$post_types = get_post_types( array(
			'public'   => true,
			'_builtin' => false, // Only Custom Post Types
		), 'objects' );
		return $post_types;
	}

	/**
	 * @return mixed
	 */
	public function get_shortcodes() {
		global $shortcode_tags;
		return $shortcode_tags;
	}

	public function enqueue_scripts( $page ) {
		if ( 'settings_page_customize_amp' !== $page ) {
			return false;
		}
		wp_register_style( 'customize-amp-admin-css', plugins_url( 'css/admin.css', __FILE__ ) );
		wp_enqueue_style( 'customize-amp-admin-css' );
	}

}
