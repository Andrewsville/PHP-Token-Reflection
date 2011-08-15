<?php

const TOKENREFLECTION_METHOD_STATIC_VARIABLE_VALUE = 'constant value';

class TokenReflection_Test_MethodStaticVariablesParent {
	const PARENT = 'parent constant';
}

class TokenReflection_Test_MethodStaticVariables extends TokenReflection_Test_MethodStaticVariablesParent
{
	const SELF = 'self constant';

	public function staticVariables()
	{
		static $string = 'string';
		static $integer = 1;
		static $float = 1.1;
		static $boolean = true;
		static $null = null;
		static $array = array(1 => 1);
		static $array2 = array(1 => 1, 2 => 2);
		static $constants = array(
			TokenReflection_Test_MethodStaticVariables::SELF,
			TokenReflection_Test_MethodStaticVariablesParent::PARENT
		);
	}
}