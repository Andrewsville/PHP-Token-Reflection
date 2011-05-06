<?php

abstract class TokenReflection_Test_MethodAbstractImplementedParent
{
	abstract public function abstractImplemented();
}

class TokenReflection_Test_MethodAbstractImplemented extends TokenReflection_Test_MethodAbstractImplementedParent
{
	public function abstractImplemented()
	{
	}
}