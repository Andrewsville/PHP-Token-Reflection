<?php

class TokenReflection_Test_MethodNoPrototypeParent
{
	private function noPrototype()
	{
	}
}

class TokenReflection_Test_MethodNoPrototype extends TokenReflection_Test_MethodNoPrototypeParent
{
	private function noPrototype()
	{
	}
}