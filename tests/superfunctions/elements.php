<?php

require_once( dirname( dirname( __FILE__  ) ) . '/phpunit.php'  );

class Torro_Superfunctions_Containers_Tests extends Torro_Superfunctions_Tests {
	function create_element( $container_id ) {
		$args = array(
			'type' => 'textfield',
			'label' => 'Test Element',
			'sort' => 0
		);

		$element = torro()->elements()->create( $container_id, $args );
		$this->assertTrue( ! is_wp_error( $element ) );
		return $element;
	}

	function update_element( $element_id ){
		$args = array(
			'type' => 'textfield',
			'label' => 'Renamed Element'
		);

		$element = torro()->elements()->update( $element_id, $args );
		$this->assertTrue( ! is_wp_error( $element ) );
		return $element;
	}

	function move_element( $container_id , $element_id ){
		$element = torro()->elements()->move( $element_id, $container_id );
		$this->assertTrue( ! is_wp_error( $element ) );
		return $element;
	}

	function copy_element( $container_id, $element_id ){
		$element = torro()->elements()->copy( $element_id, $container_id );
		$this->assertTrue( ! is_wp_error( $element ) );
		return $element;
	}

	function delete_element( $element_id ){
		$element = torro()->elements()->delete( $element_id );
		$this->assertTrue( ! is_wp_error( $element ) );
		return $element;
	}

	function test_elements() {
		$form = torro()->forms()->create( array( 'title' => 'Testing Elements' ) );
		$container = torro()->containers()->create( $form->id, array( 'label' => 'A Container', 'sort' => 0  ) );
		$container_new = torro()->containers()->create( $form->id, array( 'label' => 'Copy an Element to me', 'sort' => 0  ) );

		$element = $this->create_element( $container->id );
		$element_new = $this->copy_element( $container_new->id, $element->id );
		$element = $this->update_element( $element->id );
		$element = $this->move_element( $container_new->id, $element->id );

		$this->assertTrue( torro()->elements()->exists( $element->id ) );

		$this->delete_element( $form->id, $element->id );
		$this->delete_element( $form->id, $element_new->id );
		// torro()->forms()->delete( $form->id );
	}
}

