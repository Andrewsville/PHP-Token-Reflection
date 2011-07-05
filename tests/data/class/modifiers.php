<?php

class TokenReflection_Test_ClassModifiers
{

}

interface TokenReflection_Test_ClassModifiersIface1 {
	public function foo();
}

interface TokenReflection_Test_ClassModifiersiFace2 extends TokenReflection_Test_ClassModifiersIface1
{
	public function bar();
}

class TokenReflection_Test_ClassModifiersClass1
{

}

abstract class TokenReflection_Test_ClassModifiersClass2
{

}

abstract class TokenReflection_Test_ClassModifiersClass3
{
	abstract protected function bar();
}

abstract class TokenReflection_Test_ClassModifiersClass4 implements TokenReflection_Test_ClassModifiersIface1
{

}

abstract class TokenReflection_Test_ClassModifiersClass5 implements TokenReflection_Test_ClassModifiersiFace2
{
	abstract protected function tmp();
}

class TokenReflection_Test_ClassModifiersClass6 implements TokenReflection_Test_ClassModifiersIface1
{
	public function foo()
	{

	}
}

class TokenReflection_Test_ClassModifiersClass7 extends TokenReflection_Test_ClassModifiersClass5
{
	public function foo()
	{

	}

	public function bar()
	{

	}

	protected function tmp()
	{

	}
}
