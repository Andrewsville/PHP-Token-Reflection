<?php

abstract class TokenReflection_Test_MethodModifiers
{
	abstract public function publicAbstract();

	abstract protected function protectedAbstract();

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