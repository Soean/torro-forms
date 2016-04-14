<?php

require_once( '../phpunit.php' );

class Torro_Superfunctions_Containers_Tests extends Torro_Superfunctions_Tests {
	function create_element( $container_id ) {
		$args = array(
			'type' => 'textfield',
			'label' => 'Test Element',
			'sort' => 0
		);

		$element = torro()->elements()->create( $container_id, $args );
		$this->debug( $element );
		$this->assertTrue( ! is_wp_error( $element ) );
		return $element;
	}

	function update_element( $element ){
		$args = array(
			'type' => 'textfield',
			'label' => 'Renamed Element'
		);

		$element = torro()->elements()->update( $element->id, $args );
		$this->assertTrue( ! is_wp_error( $element ) );
		return $element;
	}

	function move_element( $container_id , $element ){
		$container = torro()->containers()->create( array( 'label' => 'Move an element to me' ) );
		$element = $this->create_element( $container_id );

		$element = torro()->elements()->move( $element->id, $container->id );

		// $this->debug( $element );
		$this->assertTrue( ! is_wp_error( $element ) );

		$element = torro()->elements()->move( $element->id, $container_id );

		torro()->containers()->delete( $container->id );

		return $element;
	}

	function copy_element( $container_id, $element ){
		$container = torro()->containers()->create( array( 'label' => 'Copy an element to me' ) );
		$element = torro()->elements()->copy( $element->id, $container->id );

		// $this->debug( $element );
		$this->assertTrue( ! is_wp_error( $element ) );

		torro()->containers()->delete( $container->id );

		return $element;
	}

	function delete_element( $element ){
		// $this->debug( $element );

		$result = torro()->elements()->delete( $element->id );

		// $this->debug( $result );
		$this->assertTrue( ! is_wp_error( $result ) );
	}

	function test_elements() {
		$form = torro()->forms()->create( array( 'title' => 'Testing Elements' ) );
		$container = torro()->containers()->create( $form->id, array( 'label' => 'A Container', 'sort' => 0  ) );

		$element = $this->create_element( $container->id );
		//$element = $this->update_element( $element );
		// $element = $this->copy_element( $container->id, $element );
		// $element = $this->move_element( $container->id, $element );

		// $this->assertTrue( torro()->elements()->exists( $element->id ) );
		// $this->delete_element( $form->id, $element );
		// torro()->forms()->delete( $form->id );
	}
}

