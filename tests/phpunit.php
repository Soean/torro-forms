<?php

require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );

class Torro_Superfunctions_Tests extends PHPUnit_Framework_TestCase {
	function debug( $value ){
		fwrite( STDERR, print_r( $value, TRUE ) );
	}
}
