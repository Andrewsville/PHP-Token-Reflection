<?php

trait TokenReflection_Test_ClassTraitsTrait1 {
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

trait TokenReflection_Test_ClassTraitsTrait2 {
	use TokenReflection_Test_ClassTraitsTrait1 {privatef as t2privatef;}

	private function privatef() {}
}

trait TokenReflection_Test_ClassTraitsTrait3 {
	public $t1Public = 't3';

	private function privatef() {}
}

trait TokenReflection_Test_ClassTraitsTrait4 {
	protected $t1Protected = 't4';

	private function privatef() {}
}

class TokenReflection_Test_ClassTraits {
	use TokenReflection_Test_ClassTraitsTrait1 {publicf as private privatef2; protectedf as public publicf3; publicf as publicfOriginal;}

	private $classPrivate = 'classPrivate';
}

class TokenReflection_Test_ClassTraits2 {
	use TokenReflection_Test_ClassTraitsTrait2;

	public function publicf() {}
}

class TokenReflection_Test_ClassTraits3 {
	use TokenReflection_Test_ClassTraitsTrait1;

	private function privatef() {}
}

class TokenReflection_Test_ClassTraits4 {
	use TokenReflection_Test_ClassTraitsTrait3;
	use TokenReflection_Test_ClassTraitsTrait4 {TokenReflection_Test_ClassTraitsTrait4::privatef insteadof TokenReflection_Test_ClassTraitsTrait3;}
}

