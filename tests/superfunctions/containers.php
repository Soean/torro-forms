<?php

require_once( '../phpunit.php' );

class Torro_Superfunctions_Containers_Tests extends Torro_Superfunctions_Tests {
	function test_containers() {
		$form = torro()->forms()->create( array( 'title' => 'Testing superfunctions' ) );
		$this->debug( $form );

		$this->create_container( $form->id );
	}

	function create_container( $form_id ){
		$args = array(
			'label' => 'Test Container',
			'sort'  => 0
		);

		$container = torro()->containers()->create( $form_id, $args );
		$this->debug( $container );
	}
}
