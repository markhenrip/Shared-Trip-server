<?php

class TestObject {

	var $params;

	public function __construct($someParams) {
		include 'utils/parsing.php';
		$this->params = $someParams;
	}

	public function trySomething() {
		return doSomething()." ".$this->params;
	}

}

?>