<?php
/**
 * Dropdown Form Element
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Elements
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

final class Torro_Form_Element_Dropdown extends Torro_Form_Element {
	private static $instances = array();

	public static function instance( $id = null ) {
		$slug = $id;
		if ( null === $slug ) {
			$slug = 'CLASS';
		}
		if ( ! isset( self::$instances[ $slug ] ) ) {
			self::$instances[ $slug ] = new self( $id );
		}
		return self::$instances[ $slug ];
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $id = null ) {
		parent::__construct( $id );
	}

	protected function init() {
		$this->type = $this->name = 'dropdown';
		$this->title = __( 'Dropdown', 'torro-forms' );
		$this->description = __( 'Add an Element which can be answered within a dropdown field.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-dropdown', 'png' );

		$this->input_answers = true;
		$this->answer_array = false;
		$this->input_answers = true;
	}

	public function input_html() {
		$html  = '<label for="' . $this->get_input_name() . '">' . esc_html( $this->label ) . '</label>';

		$html .= '<select name="' . $this->get_input_name() . '">';
		$html .= '<option value="please-select"> - ' . esc_html__( 'Please select', 'torro-forms' ) . ' -</option>';

		foreach ( $this->answers AS $answer ) {
			$checked = '';

			if ( $this->response === $answer->label ) {
				$checked = ' selected="selected"';
			}

			$html .= '<option value="' . esc_attr( $answer->label ) . '" ' . $checked . '/> ' . esc_html( $answer->label ) . '</option>';
		}

		$html .= '</select>';

		if ( ! empty( $this->settings['description']->value ) ) {
			$html .= '<small>';
			$html .= esc_html( $this->settings['description']->value );
			$html .= '</small>';
		}

		return $html;
	}

	public function settings_fields() {
		$this->settings_fields = array(
			'description'	=> array(
				'title'			=> __( 'Description', 'torro-forms' ),
				'type'			=> 'textarea',
				'description'	=> __( 'The description will be shown after the field.', 'torro-forms' ),
				'default'		=> ''
			),
		);
	}

	public function validate( $input ) {
		$error = false;

		if ( 'please-select' === $input ) {
			$this->validation_errors[] = __( 'Please select a value.', 'torro-forms' );
			$error = true;
		}

		return ! $error;
	}

}

torro()->elements()->register( 'Torro_Form_Element_Dropdown' );
