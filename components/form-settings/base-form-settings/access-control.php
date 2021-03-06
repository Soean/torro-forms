<?php
/**
 * Restrictioms form settings
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
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

final class Torro_Form_Setting_Access_Control extends Torro_Form_Setting {
	private static $instance = null;

	/**
	 * Settings fields array
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = 'visitors';

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

	protected function init() {
		$this->option_name = $this->title = __( 'Access Control', 'torro-forms' );
		$this->name = 'access_control';

		add_action( 'torro_formbuilder_save', array( __CLASS__, 'save' ), 10, 1 );
	}

	/**
	 * Adds content to the option
	 */
	public function option_content() {
		global $post;

		$form_id = $post->ID;
		$access_controls = torro()->access_controls()->get_all_registered();

		if ( ! is_array( $access_controls ) || 0 === count( $access_controls ) ) {
			return;
		}

		/**
		 * Select field for Restriction
		 */
		$access_controls_option = get_post_meta( $form_id, 'access_controls_option', true );

		if ( empty( $access_controls_option ) ) {
			$access_controls_option = 'allvisitors';
		}

		ob_start();
		do_action( 'torro_form_setting_visitors_content_top' );
		$html = ob_get_clean();

		$html .= '<div id="form-access-controls-options">';
		$html .= '<label for="form_access_controls_option">' . esc_html__( 'Who has access to this form?', 'torro-forms' ) . ' ';
		$html .= '<select name="form_access_controls_option" id="form-access-controls-option">';
		foreach ( $access_controls as $name => $access_control ) {
			if ( ! $access_control->has_option() ) {
				continue;
			}
			$selected = '';
			if ( $name === $access_controls_option ) {
				$selected = ' selected="selected"';
			}
			$html .= '<option value="' . $name . '"' . $selected . '>' . $access_control->option_name . '</option>';
		}
		$html .= '</select></label>';

		/**
		 * Option content
		 */
		foreach ( $access_controls as $name => $access_control ) {
			$html .= '<div id="form-access-controls-content-' . $access_control->name . '" class="form-access-controls-content form-access-controls-content-' . $access_control->name . '">' . $access_control->option_content() . '</div>';
		}

		$html.= '</div>';

		ob_start();
		do_action( 'torro_form_setting_access_controls_content_bottom' );
		$html .= ob_get_clean();

		return $html;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $form_id ) {
		/**
		 * Saving access-control options
		 */
		$access_controls_option = $_POST[ 'form_access_controls_option' ];
		update_post_meta( $form_id, 'access_controls_option', $access_controls_option );
	}
}

torro()->form_settings()->register( 'Torro_Form_Setting_Access_Control' );