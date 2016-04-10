<?php

require_once( '../../../../wp-load.php' );

class Torro_Superfunctions_Tests extends PHPUnit_Framework_TestCase {

	function createForm(){
		$args = array(
			'title' => 'Testing superfunctions',
			'content' => 'Testing superfunctions'
		);

		$form = torro()->forms()->create( $args );

		$this->p( $form );
		$this->assertTrue( ! is_wp_error( $form ) );

		return $form;
	}

	function updateForm( $form ){
		$args = array(
			'title' => 'Changing title',
			'content' => 'Changing content'
		);

		$form = torro()->forms()->update( $form->id, $args );

		$this->p( $form );
		$this->assertTrue( ! is_wp_error( $form ) );

		return $form;
	}

	function deleteForm( $form ){
		$this->assertTrue( torro()->forms()->delete( $form->id ) );
	}

	function testForms(){
		$form = $this->createForm();
		$form = $this->updateForm( $form );
		$this->deleteForm( $form );
	}

	function p( $value ){
		fwrite( STDERR, print_r( $value, TRUE ) );
	}
}
