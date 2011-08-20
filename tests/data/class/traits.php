<?php

trait t1 {
	protected $t1Protected = 't1pro';

	private $t1Private = 't1pri';

	public $t1Public = 't1pub';

	protected static $t1ProtectedS;

	private static $t1PrivateS;

	public static $t1PublixS;

	private function privatef() {}

	protected function protectedf() {}

	public function publicf() {}
}

trait t2 {
	use t1 {privatef as t2privatef;}

	private function privatef() {}
}

trait t3 {
	public $t1Public = 't3';

	private function privatef() {}
}

trait t4 {
	protected $t1Protected = 't4';

	private function privatef() {}
}

class c1 {
	use t1 {publicf as private privatef2; protectedf as public publicf3; publicf as publicfOriginal;}
}

class c2 {
	use t2;
}

class c3 {
	use t1;
}


