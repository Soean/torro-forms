<?php
/**
 * Torro Forms extensions manager class
 *
 * This class holds and manages all extension class instances.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 2015-04-16
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
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
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Extensions_Manager extends Torro_Manager {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function allowed_modules(){
		$allowed = array(
			'extensions' => 'Torro_Extension'
		);
		return $allowed;
	}

	protected function init() {
		$this->base_class = 'Torro_Extension';
	}

	protected function after_instance_added( $instance ) {
		$instance->check_and_start();

		add_action( 'init', array( $instance, 'init_settings' ), 15 );

		return $instance;
	}

	public function register( $class_name ){
		return $this->register_module( 'extensions', $class_name );
	}

	public function get_registered( $name ){
		return $this->get_module( 'extensions', $name );
	}

	public function get_all_registered(){
		return $this->get_all_modules( 'extensions' );
	}
}
