<?php

require_once( '../phpunit.php' );

class Torro_Superfunctions_Form_Tests extends Torro_Superfunctions_Tests {
	function testElements() {
		$form = torro()->forms()->create( array( 'title' => 'Testing superfunctions' ) );

		$this->debug( $form );

		$element = $this->create_element( $form->id );
		$element = $this->update_element( $form->id, $element );
		$element = $this->copy_element( $form->id, $element );
		$element = $this->move_element( $form->id, $element );
		// $this->delete_element( $form->id, $element );

		// torro()->forms()->delete( $form->id );
	}

	function create_element( $form_id ) {
		$args = array(
			'type' => 'textfield',
			'label' => 'Element'
		);

		$element = torro()->elements()->create( $form_id, $args );

		// $this->debug( $element );
		$this->assertTrue( ! is_wp_error( $element ) );

		return $element;
	}

	function update_element( $form_id, $element ){
		$args = array(
			'type' => 'textfield',
			'label' => 'Updated Element'
		);

		$element = torro()->elements()->update( $element->id, $args );

		// $this->debug( $element );
		$this->assertTrue( ! is_wp_error( $element ) );

		return $element;
	}

	function move_element( $form_id, $element ){
		$form = torro()->forms()->create( array( 'title' => 'Copy an element to me' ) );
		$element = $this->create_element( $form_id );

		$element = torro()->elements()->move( $element->id, $form->id );

		// $this->debug( $element );
		$this->assertTrue( ! is_wp_error( $element ) );

		$element = torro()->elements()->move( $element->id, $form_id );

		torro()->forms()->delete( $form_id );

		return $element;
	}

	function copy_element( $form_id, $element ){
		$form = torro()->forms()->create( array( 'title' => 'Copy an element to me' ) );
		$element = torro()->elements()->copy( $element->id, $form->id );

		// $this->debug( $element );
		$this->assertTrue( ! is_wp_error( $element ) );

		torro()->forms()->delete( $form_id );

		return $element;
	}

	function delete_element( $element ){
		$this->debug( $element );

		$result = torro()->elements()->delete( $element->id );

		// $this->debug( $result );
		$this->assertTrue( ! is_wp_error( $result ) );
	}
}

