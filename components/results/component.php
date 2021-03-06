<?php
/**
 * Torro Forms Results Component
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0
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

final class Torro_Results_Component extends Torro_Component {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'results';
		$this->title = __( 'Results', 'torro-forms' );
		$this->description = __( 'Handling Results.', 'torro-forms' );
	}

	protected function includes() {
		$folder = torro()->get_path( 'components/results/' );

		// Models
		require_once( $folder . 'models/class-result-handler.php' );
		require_once( $folder . 'models/class-form-results.php' );
		require_once( $folder . 'models/class-charts.php' );

		// Loading base functionalities
		require_once( $folder . 'settings.php' );
		require_once( $folder . 'form-builder-extension.php' );
		require_once( $folder . 'shortcodes.php' );

		// Data handling
		require_once( $folder . 'export.php' );

		// Results base Class

		// Base Result Handlers
		require_once( $folder . 'base-result-handlers/entries.php' );
		require_once( $folder . 'base-result-handlers/charts-c3.php' );
	}
}

torro()->components()->register( 'Torro_Results_Component' );
