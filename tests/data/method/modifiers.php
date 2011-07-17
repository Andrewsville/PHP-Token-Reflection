<?php

interface TokenReflection_Test_MethodModifiersIface
{
	public function publicFromIface();
}

abstract class TokenReflection_Test_MethodModifiersParent implements TokenReflection_Test_MethodModifiersIface
{
	abstract public function publicAbstract();

	abstract protected function protectedAbstract();
}

class TokenReflection_Test_MethodModifiers extends TokenReflection_Test_MethodModifiersParent
{
	public function publicFromIface()
	{
	}

	public function publicAbstract()
	{
	}

	protected function protectedAbstract()
	{
	}

	public final function publicFinal()
	{
	}

	protected final function protectedFinal()
	{
	}

	private final function privateFinal()
	{
	}

	public static function publicStatic()
	{
	}

	protected static function protectedStatic()
	{
	}

	private static function privateStatic()
	{
	}

	public final static function publicFinalStatic()
	{
	}

	protected final static function protectedFinalStatic()
	{
	}

	private final static function privateFinalStatic()
	{
	}

	public function publicNoStatic()
	{
	}

	protected function protectedNoStatic()
	{
	}

	private function privateNoStatic()
	{
	}
}

class TokenReflection_Test_MethodModifiersChild extends TokenReflection_Test_MethodModifiers
{
	public function publicFromIface()
	{
	}

	public function publicAbstract()
	{
	}

	protected function protectedAbstract()
	{
	}

	public static function publicStatic()
	{
	}

	protected static function protectedStatic()
	{
	}

	private static function privateStatic()
	{
	}

	public function publicNoStatic()
	{
	}

	protected function protectedNoStatic()
	{
	}

	private function privateNoStatic()
	{
	}
}

class TokenReflection_Test_MethodModifiersChild2 extends TokenReflection_Test_MethodModifiersChild
{
	public function protectedAbstract()
	{
	}

	public static function protectedStatic()
	{
	}

	protected static function privateStatic()
	{
	}

	public function protectedNoStatic()
	{
	}

	protected function privateNoStatic()
	{
	}
}

class TokenReflection_Test_MethodModifiersChild3 extends TokenReflection_Test_MethodModifiersChild2
{
	public static function privateStatic()
	{
	}

	public function privateNoStatic()
	{
	}
}

class TokenReflection_Test_MethodModifiersChild4 extends TokenReflection_Test_MethodModifiers
{
	public function publicFromIface()
	{
	}

	public function publicAbstract()
	{
	}

	protected function protectedAbstract()
	{
	}

	public static function privateStatic()
	{
	}

	public function privateNoStatic()
	{
	}
}