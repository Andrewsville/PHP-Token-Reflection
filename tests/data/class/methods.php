<?php

class TokenReflection_Test_ClassMethodsParent
{
	protected static function protectedStaticFunction($one = true)
	{
	}

	protected function protectedFunction($two = false)
	{
	}
}

class TokenReflection_Test_ClassMethods extends TokenReflection_Test_ClassMethodsParent
{
	public function __construct($three)
	{
	}

	public function __destruct()
	{
	}

	public final function publicFinalFunction($four = 1)
	{
	}

	public static function publicStaticFunction($five = 1.1)
	{
	}

	private static function privateStaticFunction($six = 'string', $seven = null)
	{
	}

	public function publicFunction(array $eight = array())
	{
	}

	private function privateFunction(Foo $nine = null)
	{
	}
}