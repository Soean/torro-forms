<?php

require_once( '../phpunit.php' );

class Torro_Superfunctions_Form_Tests extends Torro_Superfunctions_Tests {
	function create_form(){
		$form = torro()->forms()->create( array( 'title' => 'Testing forms', ) );
		$this->assertTrue( ! is_wp_error( $form ) );
		return $form;
	}

	function update_form( $form_id ){
		$form = torro()->forms()->update( $form_id, array( 'title' => 'Changed title' ) );
		$this->assertTrue( ! is_wp_error( $form ) );
		return $form;
	}

	function delete_form( $form_id ){
		$form = torro()->forms()->delete( $form_id );
		$this->assertTrue( ! is_wp_error( $form ) );
		return $form;
	}

	function copy_form( $form_id ){
		$form = torro()->forms()->copy( $form_id );
		$this->assertTrue( ! is_wp_error( $form ) );
		return $form;
	}

	function test_forms(){
		$form = $this->create_form();
		$form_new = $this->copy_form( $form->id );
		$form = $this->update_form( $form->id );

		$this->assertTrue( torro()->forms()->exists( $form->id ) );

		$this->delete_form( $form->id );
		$this->delete_form( $form_new->id );
	}
}
