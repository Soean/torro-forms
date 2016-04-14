<?php

require_once( dirname( dirname( __FILE__  ) ) . '/phpunit.php' );

class Torro_Superfunctions_Containers_Tests extends Torro_Superfunctions_Tests {
	function create_container( $form_id ){
		$args = array(
			'label' => 'Test Container',
			'sort'  => 0
		);

		$container = torro()->containers()->create( $form_id, $args );
		$this->assertTrue( ! is_wp_error( $container ) );

		return $container;
	}

	function update_container( $container_id ){
		$args = array(
			'label' => 'Renamed Container',
			'sort'  => 1
		);

		$container = torro()->containers()->update( $container_id, $args );
		$this->assertTrue( ! is_wp_error( $container ) );

		return $container;
	}

	function copy_container( $container_id, $form_id ){
		$container = torro()->containers()->copy( $container_id, $form_id );
		$this->assertTrue( ! is_wp_error( $container ) );
		return $container;
	}

	function move_container( $container_id, $to_form_id ){
		$container = torro()->containers()->move( $container_id, $to_form_id );
		$this->assertTrue( ! is_wp_error( $container ) );
		return $container;
	}

	function test_containers() {
		$form = torro()->forms()->create( array( 'title' => 'Testing containers' ) );

		$container = $this->create_container( $form->id );
		$container_new = $this->copy_container( $container->id, $form->id );
		$container = $this->update_container( $container->id );

		$this->assertTrue( torro()->containers()->exists( $container->id ) );

		$form_new = torro()->forms()->create( array( 'title' => 'Copy to me' ) );
		$container = $this->move_container( $container_new->id, $form_new->id );
	}
}
