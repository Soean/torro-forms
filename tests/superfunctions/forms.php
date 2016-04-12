<?php

require_once( '../phpunit.php' );

class Torro_Superfunctions_Form_Tests extends Torro_Superfunctions_Tests {
	function create_form(){
		$args = array(
			'title' => 'Testing superfunctions',
		);

		$form = torro()->forms()->create( $args );

		// $this->debug( $form );
		$this->assertTrue( ! is_wp_error( $form ) );

		return $form;
	}

	function update_form( $form ){
		$args = array(
			'title' => 'Changing title',
			'content' => 'Changing content'
		);

		$form = torro()->forms()->update( $form->id, $args );

		// $this->debug( $form );
		$this->assertTrue( ! is_wp_error( $form ) );

		return $form;
	}

	function delete_form( $form ){
		$this->assertTrue( torro()->forms()->delete( $form->id ) );
	}

	function testForms(){
		$form = $this->create_form();
		$form = $this->update_form( $form );
		$this->delete_form( $form );
	}
}
