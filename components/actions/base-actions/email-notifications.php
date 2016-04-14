<?php
/**
 * Email notifications Action
 *
 * Adds Email notifications for forms
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
 * @version 1.0.0alpha1
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

final class Torro_Email_Notifications extends Torro_Action {
	/**
	 * Instance
	 *
	 * @var null|Torro_Email_Notifications
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * From Email Name
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $from_name;

	/**
	 * From Email Address
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $from_email;

	/**
	 * Singleton
	 *
	 * @return Torro_Email_Notifications
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initializing
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->title = __( 'Email Notifications', 'torro-forms' );
		$this->name = 'emailnotifications';

		add_action( 'media_buttons', array( $this, 'add_media_button' ), 20 );

		torro()->ajax()->register_action( 'get_email_notification_html', array(
			'callback'		=> array( $this, 'ajax_get_email_notification_html' ),
		) );
	}

	/**
	 * Handles the data after user submitted the form
	 *
	 * @param int $form_id
	 * @param int $response_id
	 * @param array $response
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public function handle( $form_id, $response_id, $response ) {
		global $wpdb, $torro_response_id, $torro_response;

		$torro_form_id = $form_id;
		$torro_response_id = $response_id;
		$torro_response = $response;

		$sql = $wpdb->prepare( "SELECT * FROM $wpdb->torro_email_notifications WHERE form_id = %d", $torro_form_id );
		$notifications = $wpdb->get_results( $sql );

		if ( 0 < count( $notifications ) ) {
			// Adding elements templatetags
			$form = new Torro_Form( $torro_form_id );
			foreach ( $form->elements as $element ) {
				torro()->templatetags()->get_registered( 'formtags' )->add_element( $element->id, $element->label );
			}

			foreach ( $notifications as $notification ) {
				$from_name = torro_filter_templatetags( $notification->from_name );
				$from_email = torro_filter_templatetags( $notification->from_email );
				$to_email = torro_filter_templatetags( $notification->to_email );
				$subject = torro_filter_templatetags( $notification->subject );
				$message = apply_filters( 'the_content', torro_filter_templatetags( $notification->message ) );

				$this->from_name = $from_name;
				$this->from_email = $from_email;

				add_filter( 'wp_mail_content_type', array( $this, 'set_email_html_content_type' ) );
				add_filter( 'wp_mail_from', array( $this, 'set_email_from' ) );
				add_filter( 'wp_mail_from_name', array( $this, 'set_email_from_name' ) );

				wp_mail( $to_email, $subject, $message );

				remove_filter( 'wp_mail_content_type', array( $this, 'set_email_html_content_type' ) );
				remove_filter( 'wp_mail_from', array( $this, 'set_email_from' ) );
				remove_filter( 'wp_mail_from_name', array( $this, 'set_email_from_name' ) );
			}
		}
	}

	/**
	 * Setting HTML Content-Type
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function set_email_html_content_type() {
		return 'text/html';
	}

	/**
	 * Setting From Email
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function set_email_from() {
		return $this->from_email;
	}

	/**
	 * Setting From Email Name
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function set_email_from_name() {
		return $this->from_name;
	}

	/**
	 * Option content HTML
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function option_content() {
		global $wpdb, $post;

		$sql = $wpdb->prepare( "SELECT * FROM $wpdb->torro_email_notifications WHERE form_id = %d", $post->ID );
		$notifications = $wpdb->get_results( $sql );

		$html = '<div id="form-email-notifications">';

		$html .= '<input type="hidden" name="email_notifications_nonce" value="' . wp_create_nonce( 'torro_email_notifications' ) . '" />';

		$html .= '<div class="actions">';
		$html .= '<input id="form-add-email-notification" type="button" value="' . esc_attr__( 'Add Notification', 'torro-forms' ) . '" class="button" />';
		$html .= '<p class="intro-text">' . esc_attr__( 'Send out Email-Notifications form was submitted successful.', 'torro-forms' ) . '</p>';
		$html .= '</div>';

		$html .= '<div class="list">';

		$html .= '<div class="notifications widget-title">';
		if ( 0 < count( $notifications ) ) {
			foreach ( $notifications as $notification ) {
				$html .= $this->get_notification_settings_html( $notification->id, $notification->notification_name, $notification->from_name, $notification->from_email, $notification->to_email, $notification->subject, $notification->message );
			}
		}
		$html .= '<p class="no-entry-found not-found-area">' . esc_html__( 'No Notifications found.', 'torro-forms' ) . '</p>';
		$html .= '</div>';

		$html .= '</div>';

		$html .= '</div>';
		$html .= '<div class="clear"></div>';

		$html .= '<div id="delete-email-notification-dialog">' . esc_html__( 'Do you really want to delete this Email-Notification?', 'torro-forms' ) . '</div>';

		// Dirty hack: Running one time for fake, to get all variables
		ob_start();
		wp_editor( '', 'xxx' );
		ob_clean();

		return $html;
	}

	/**
	 * Adding media button
	 *
	 * @param int $editor_id
	 *
	 * @since 1.0.0
	 */
	public function add_media_button( $editor_id ) {
		$editor_id_arr = explode( '-', $editor_id );

		if ( 'email_notification_message' !== $editor_id_arr[0] ) {
			return;
		}

		echo torro_template_tag_button( $editor_id );
	}

	/**
	 * Saving option content
	 *
	 * @since 1.0.0
	 */
	public function save_option_content() {
		global $wpdb, $post;

		if ( ! isset( $_POST['email_notifications_nonce'] ) || ! wp_verify_nonce( $_POST['email_notifications_nonce'], 'torro_email_notifications' ) ) {
			return;
		}

		$wpdb->delete( $wpdb->torro_email_notifications, array( 'form_id' => $post->ID ), array( '%d' ) );

		if ( isset( $_POST['email_notifications'] ) ) {
			foreach ( $_POST['email_notifications'] as $id => $notification ) {
				$wpdb->insert(
					$wpdb->torro_email_notifications,
					array(
						'form_id'			=> $post->ID,
						'notification_name'	=> $notification[ 'notification_name' ],
						'from_name'			=> $notification[ 'from_name' ],
						'from_email'		=> $notification[ 'from_email' ],
						'to_email'			=> $notification[ 'to_email' ],
						'subject'			=> $notification[ 'subject' ],
						'message'			=> $_POST[ 'email_notification_message-' . $id ]
					),
					array(
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}
		}
	}

	/**
	 * Getting HTML for notification
	 *
	 * @param string $notification_name
	 * @param string $notification_from_name
	 * @param string $from_email
	 * @param string $to_email
	 * @param string $subject
	 * @param string $message
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_notification_settings_html( $id, $notification_name = '', $from_name = '', $from_email = '', $to_email = '', $subject = '', $message = '' ) {
		$ajax = false;
		if ( '_AJAX_' === substr( $id, 0, 6 ) ) {
			$ajax = true;
			$id = substr( $id, 6 );
		}

		$editor_id = 'email_notification_message-' . $id;

		if ( $ajax ) {
			$editor = '<% wp_editor %>';
		} else {
			ob_start();
			wp_editor( $message, $editor_id );
			$editor = ob_get_clean();
		}

		$icon_url = torro()->get_asset_url( 'mail', 'svg' );

		$html = '<h4 class="widget-top notification-' . $id . '"><a class="widget-action hide-if-no-js"></a><img src="' . $icon_url . '" class="icon" />' . $notification_name . '</h4>';
		$html .= '<div class="notification widget-inside notification-' . $id . '-content">';

		$html .= '<table class="form-table">';
		$html .= '<tr>';
		$html .= '<th><label for="email_notifications[' . $id . '][notification_name]">' . esc_html__( 'Notification Name', 'torro-forms' ) . '</label></th>';
		$html .= '<td><input type="text" name="email_notifications[' . $id . '][notification_name]" value="' . $notification_name . '"></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th><label for="email_notifications[' . $id . '][from_name]">' . esc_html__( 'From Name', 'torro-forms' ) . '</label></th>';
		$html .= '<td><input type="text" name="email_notifications[' . $id . '][from_name]" value="' . $from_name . '">' . torro_template_tag_button( 'email_notifications[' . $id . '][from_name]' ) . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th><label for="email_notifications[' . $id . '][from_email]">' . esc_html__( 'From Email', 'torro-forms' ) . '</label></th>';
		$html .= '<td><input type="text" name="email_notifications[' . $id . '][from_email]" value="' . $from_email . '">' . torro_template_tag_button( 'email_notifications[' . $id . '][from_email]' ) . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th><label for="email_notifications[' . $id . '][to_email]">' . esc_html__( 'To Email', 'torro-forms' ) . '</label></th>';
		$html .= '<td><input type="text" name="email_notifications[' . $id . '][to_email]" value="' . $to_email . '">' . torro_template_tag_button( 'email_notifications[' . $id . '][to_email]' ) . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th><label for="email_notifications[' . $id . '][subject]">' . esc_html__( 'Subject', 'torro-forms' ) . '</label></th>';
		$html .= '<td><input type="text" name="email_notifications[' . $id . '][subject]" value="' . $subject . '">' . torro_template_tag_button( 'email_notifications[' . $id . '][subject]' ) . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th><label for="email_notification_message-' . $id . '">' . esc_html__( 'Message', 'torro-forms' ) . '</label></th>';
		$html .= '<td>' . $editor . '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td colspan="2"><input type="button" class="button form-delete-email-notification" data-emailnotificationid="' . $id . '" value="' . esc_attr__( 'Delete Notification', 'torro-forms' ) . '" /></td>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		if ( ! torro_is_formbuilder() ) {
			return;
		}

		$translation = array(
			'delete'		=> esc_attr__( 'Delete', 'torro-forms' ),
			'yes'			=> esc_attr__( 'Yes', 'torro-forms' ),
			'no'			=> esc_attr__( 'No', 'torro-forms' ),
			'nonce_get_email_notification_html' => torro()->ajax()->get_nonce( 'get_email_notification_html' ),
		);

		wp_enqueue_script( 'torro-templatetags', torro()->get_asset_url( 'templatetags', 'js' ), array(
			'torro-form-edit',
		)  );

		wp_enqueue_script( 'torro-actions-email-notifications', torro()->get_asset_url( 'actions-email-notifications', 'js' ), array( 'torro-form-edit', 'torro-templatetags', 'jquery-ui-accordion' ) );
		wp_localize_script( 'torro-actions-email-notifications', 'translation_email_notifications', $translation );
	}

	/**
	 * Enqueue admin styles
	 *
	 * @since 1.0.0
	 */
	public function admin_styles() {
		wp_enqueue_style( 'torro-actions-email-notifications', torro()->get_asset_url( 'actions-email-notifications', 'css' ), array( 'torro-form-edit' ) );
	}

	/**
	 * Get email notification html
	 *
	 * @param $data
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function ajax_get_email_notification_html( $data ) {
		$id = time();
		$editor_id = 'email_notification_message-' . $id;

		$html = torro()->actions()->get_registered( 'emailnotifications' )->get_notification_settings_html( '_AJAX_' . $id, __( 'New Email Notification' ) );

		$response = Torro_AJAX_WP_Editor::get( '', $editor_id );

		$response['id'] = $id;
		$response['html'] = str_replace( '<% wp_editor %>', $response['html'], $html );

		return $response;
	}
}

torro()->actions()->register( 'Torro_Email_Notifications' );
