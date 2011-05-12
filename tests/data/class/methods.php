<?php

class TokenReflection_Test_ClassMethodsParent
{
	protected static function protectedStaticFunction()
	{
	}

	protected function protectedFunction()
	{
	}
}

class TokenReflection_Test_ClassMethods extends TokenReflection_Test_ClassMethodsParent
{
	public function __construct()
	{
	}

	public function __destruct()
	{
	}

	public final function publicFinalFunction()
	{
	}

	public static function publicStaticFunction()
	{
	}

	private static function privateStaticFunction()
	{
	}

	public function publicFunction()
	{
	}

	private function privateFunction()
	{
	}
}