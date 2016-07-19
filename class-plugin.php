<?php
/**
 * Main Plugin file.
 *
 * @package Customize_AMP
 */

namespace Customize_AMP;

include_once( CUSTOMIZE_AMP_DIR . '/class-admin.php' );

/**
 * Class Plugin
 *
 * @package Customize_AMP
 */
class Plugin {

	/**
	 * Config array.
	 *
	 * @var array
	 */
	public $config = array();

	/**
	 * Admin class instance.
	 *
	 * @var object
	 */
	public $admin;

	/**
	 * The class instance.
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Get the instance.
	 *
	 * @return AMP|object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Magic method to get our properties.
	 *
	 * @param string $property The property name.
	 *
	 * @return array|null
	 */
	public function __get( $property ) {
		return array_key_exists( $property, $this->config ) ? $this->config[ $property ] : null;
	}

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		//$this->config = $this->load_config();

		/*
		 * Priority 9 because WP_Customize_Widgets::register_settings()
		 * happens at after_setup_theme priority 10.
		 */
		add_action( 'after_setup_theme', array( $this, 'init' ), 9 );
	}

	/**
	 * Initialize the class.
	 *
	 * Contains actions and filters for the AMP Templates.
	 *
	 * @action after_setup_theme
	 */
	public function init() {
		$this->admin = Admin::get_instance();
	}

}
