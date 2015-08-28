<?php
/**
 * Email notifications Response handler
 *
 * Adds Email notifications for forms
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Restrictions
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class Questions_EmailNotifications extends  Questions_ResponseHandler{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Email Notifications', 'wcsc-locale' );
		$this->slug = 'emailnotifications';

		add_action( 'admin_print_styles', array( __CLASS__, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
		add_action( 'questions_save_form', array( __CLASS__, 'save_option_content' ) );

		add_action( 'wp_ajax_get_email_notification_html', array( __CLASS__, 'ajax_get_email_notification_html' ) );

		add_filter( 'tiny_mce_before_init', 'Quesions_WPEditorBox::tiny_mce_before_init', 10, 2 );
		add_filter( 'quicktags_settings', 'Quesions_WPEditorBox::quicktags_settings', 10, 2 );
		add_action( 'media_buttons', array( __CLASS__, 'add_media_button' ), 20 );
	}

	/**
	 * Handles the data after user submitted the form
	 * @param $response_id
	 * @param $response
	 */
	public function handle( $response_id, $response ){
		global $wpdb, $questions_form_id, $questions_global;

		$sql = $wpdb->prepare( "SELECT * FROM {$questions_global->tables->email_notifications} WHERE form_id = %d", $questions_form_id );
		$notifications = $wpdb->get_results( $sql );

		if( count( $notifications ) > 0 ){

			foreach( $notifications AS $notification ){
				$message = $notification->message;
				$subject = $notification->subject;

				wp_mail( $notification->to_email, $notification->subject );
			}
		}
	}

	public function option_content(){
		global $wpdb, $post, $questions_global;

		$sql = $wpdb->prepare( "SELECT * FROM {$questions_global->tables->email_notifications} WHERE form_id = %d", $post->ID );
		$notifications = $wpdb->get_results( $sql );

		$html = '<div id="questions-email-notifications">';
			$html.= '<div class="list">';

					$html.= '<div class="notifications widget-title">';

					if( count( $notifications ) > 0 ){

						foreach( $notifications AS $notification ){
							$html.= self::get_notification_settings_html(
								$notification->id,
								$notification->notification_name,
								$notification->from_name,
								$notification->from_email,
								$notification->to_email,
								$notification->subject,
								$notification->message
							);
						}
					}
					$html.= '<p class="no-entry-found">' . esc_attr( 'No notification found.', 'questions-locale' ) . '</p>';
				$html.= '</div>';
			$html.= '</div>';
			$html.= '<div class="actions">';
				$html.= '<input id="questions_add_email_notification" type="button" value="' . esc_attr( '+', 'questions-locale' ) . '" class="button" />';
			$html.= '</div>';
		$html.= '</div>';
		$html.= '<div class="clear"></div>';

		$html.= '<script language="javascript">jQuery( document ).ready(function ($) {$.questions_templatetag_buttons();});</script>';

		$html.= '<div id="delete_email_notification_dialog">' . esc_attr__( 'Do you really want to delete this notification?', 'questions-locale' ) . '</div>';

		// Dirty hack: Running one time for fake, to get all variables
		ob_start();
		wp_editor( '', 'xxx' );
		ob_clean();

		return $html;
	}

	/**
	 * Adding media button
	 */
	public static function add_media_button(){
		echo qu_template_tag_button( 'test' );
	}

	/**
	 * Saving option content
	 */
	public static function save_option_content(){
		global $wpdb, $post, $questions_global;

		if( isset( $_POST[ 'email_notifications' ] ) && count( $_POST[ 'email_notifications' ] ) > 0 ){
			$wpdb->delete( $questions_global->tables->email_notifications, array( 'form_id' => $post->ID ), array( '%d' ) );

			foreach(  $_POST[ 'email_notifications' ] AS $id => $notification  ){
				$wpdb->insert(
					$questions_global->tables->email_notifications,
					array(
						'form_id'           => $post->ID,
						'notification_name' => $notification[ 'notification_name' ],
						'from_name'         => $notification[ 'from_name' ],
						'from_email'        => $notification[ 'from_email' ],
						'to_email'          => $notification[ 'to_email' ],
						'subject'           => $notification[ 'subject' ],
						'message'           => $_POST[ 'email_notification_message_' . $id ]
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
	 * @param $notification_name
	 * @param $notification_from_name
	 * @param $notification_from_email
	 * @param $notification_to_email
	 * @param $notification_subject
	 * @param $notification_message
	 *
	 * @return string $html
	 */
	public static function get_notification_settings_html( $id, $notification_name = '', $from_name = '', $from_email = '', $to_email = '', $subject = '', $message = '' ){

		add_filter( 'wp_default_editor', array( __CLASS__, 'std_editor_tinymce' ) ); // Dirty hack, but needed to prevent tab issues on editor
		ob_start();
		wp_editor( $message, 'email_notification_message_' . $id  );
		$editor = ob_get_clean();
		remove_filter( 'wp_default_editor', array( __CLASS__, 'std_editor_tinymce' ) ); // Dirty hack, but needed to prevent tab issues on editor

		$html = '<h4 class="widget-top notification-' . $id . '">' . $notification_name . '</h4>';
		$html.= '<div class="notification widget-inside notification-' . $id . '-content">';

			$html.= '<table class="form-table">';
				$html.= '<tr>';
					$html.= '<th><label for="email_notifications[' . $id . '][notification_name]">' . esc_attr( 'Notification Name', 'questions-locale' ) . '</label></th>';
					$html.= '<td><input type="text" name="email_notifications[' . $id . '][notification_name]" value="' . $notification_name . '"></td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<th><label for="email_notifications[' . $id . '][from_name]">' . esc_attr( 'From Name', 'questions-locale' ) . '</label></th>';
					$html.= '<td><input type="text" name="email_notifications[' . $id . '][from_name]" value="' . $from_name . '">' . qu_template_tag_button( 'email_notifications[' . $id . '][from_name]' ) . '</td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<th><label for="email_notifications[' . $id . '][from_email]">' . esc_attr( 'From Email', 'questions-locale' ) . '</label></th>';
					$html.= '<td><input type="text" name="email_notifications[' . $id . '][from_email]" value="' . $from_email . '">' . qu_template_tag_button( 'email_notifications[' . $id . '][from_email]' ) . '</td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<th><label for="email_notifications[' . $id . '][to_email]">' . esc_attr( 'To Email', 'questions-locale' ) . '</label></th>';
					$html.= '<td><input type="text" name="email_notifications[' . $id . '][to_email]" value="' . $to_email . '">' . qu_template_tag_button( 'email_notifications[' . $id . '][to_email]' ) . '</td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<th><label for="email_notifications[' . $id . '][subject]">' . esc_attr( 'Subject', 'questions-locale' ) . '</label></th>';
					$html.= '<td><input type="text" name="email_notifications[' . $id . '][subject]" value="' . $subject . '">' . qu_template_tag_button( 'email_notifications[' . $id . '][subject]' ) . '</td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<th><label for="email_notification_message_' . $id . '">' . esc_attr( 'Message', 'questions-locale' ) . '</label></th>';
					$html.= '<td>' . $editor . '</td>';
				$html.= '</tr>';
				$html.= '<tr>';
					$html.= '<td colspan="2"><input type="button" class="button questions-delete-email-notification" data-emailnotificationid="' . $id . '" value="' . esc_attr( 'Delete Notification', 'questions-locale' ) . '" /></td>';
				$html.= '</tr>';
			$html.= '</table>';
		$html.= '</div>';

		return $html;
	}

	/**
	 * Get Email notification HTML
	 */
	public static function ajax_get_email_notification_html(){
		$id = time();
		$editor_id = 'email_notification_message_' . $id;

		$html = self::get_notification_settings_html( $id, esc_attr( 'New Email Notification' ) );

		// Get settings for Editor
		$mce_init = Quesions_WPEditorBox::get_mce_init( $editor_id );
		$qt_init = Quesions_WPEditorBox::get_qt_init( $editor_id );

		// Extending editor gobals
		$html.= '<script type="text/javascript">
			tinyMCEPreInit.mceInit = jQuery.extend( tinyMCEPreInit.mceInit, ' . $mce_init . ' );
            tinyMCEPreInit.qtInit = jQuery.extend( tinyMCEPreInit.qtInit, ' . $qt_init . ' );

            tinyMCE.init( tinyMCEPreInit.mceInit[ "' . $editor_id . '" ] );
            try { quicktags( tinyMCEPreInit.qtInit[ "' . $editor_id . '" ] ); } catch(e){ console.log( "error" ); }

            QTags.instances["0"] =""; // Dirty Hack, but needed to start second instance of quicktags in editor
        </script>';

		$data = array(
			'id' => $id,
			'editor_id' => $editor_id,
			'html'      => $html
		);

		echo json_encode( $data );
		die();
	}

	/**
	 * Function to set standard editor to tinymce prevent tab issues on editor
	 * @return string
	 */
	public static function std_editor_tinymce(){
		return 'tinymce';
	}

	/**
	 * Enqueue admin scripts
	 */
	public static function enqueue_admin_scripts()
	{
		$translation = array( 'delete'                       => esc_attr__( 'Delete', 'questions-locale' ),
		                      'yes'                          => esc_attr__( 'Yes', 'questions-locale' ),
		                      'no'                           => esc_attr__( 'No', 'questions-locale' ) );

		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'questions-response-handlers-email-notification', QUESTIONS_URLPATH . '/components/response-handlers/base-response-handlers/includes/js/email-notifications.js' );
		wp_localize_script( 'questions-response-handlers-email-notification', 'translation_email_notifications', $translation );
	}

	/**
	 * Enqueue admin styles
	 */
	public static function enqueue_admin_styles()
	{
		wp_enqueue_style( 'questions-response-handlers-email-notification', QUESTIONS_URLPATH . '/components/response-handlers/base-response-handlers/includes/css/email-notifications.css' );
	}
}
qu_register_response_handler( 'Questions_EmailNotifications' );