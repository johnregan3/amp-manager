<?php
/**
 * Plugin Name: Customize AMP
 * Description: Admin tools to customize your AMP integration. Requires WP AMP by Automattic.
 * Author: John Regan
 * Author URI: http://johnregan3.com
 * Version: 0.1
 * Text Domain: customize-amp
 *
 * Copyright 2016  John Regan  (http://johnregan3.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Customize_AMP
 *
 * @author John Regan
 *
 * @version 0.1
 */

// Prevent direct file access
if( ! defined( 'ABSPATH' ) ) {
	die();
}

define( 'CUSTOMIZE_AMP_DIR', dirname( __FILE__ ) );
add_action( 'plugins_loaded', 'customize_amp_get_instance' );

function customize_amp_get_instance() {
	require_once( CUSTOMIZE_AMP_DIR . '/class-plugin.php' );
	Customize_AMP\Plugin::get_instance();
}


