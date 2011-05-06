<?php

class TokenReflection_Test_MethodAccessLevelParent
{
	private function privateNoExtended()
	{
	}

	protected function protectedNoExtended()
	{
	}

	private function privateExtended()
	{
	}

	protected function protectedExtended()
	{
	}
}

class TokenReflection_Test_MethodAccessLevel extends TokenReflection_Test_MethodAccessLevelParent
{
	private function privateNoExtended()
	{
	}

	protected function protectedNoExtended()
	{
	}

	public function privateExtended()
	{
	}

	public function protectedExtended()
	{
	}
}