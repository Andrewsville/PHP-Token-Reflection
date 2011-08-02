<?php

interface TokenReflection_Test_ClassModifiersIface1 {

}

interface TokenReflection_Test_ClassModifiersIface2 extends Serializable {
	public function foo();
}

interface TokenReflection_Test_ClassModifiersIface3 extends TokenReflection_Test_ClassModifiersIface2, TokenReflection_Test_ClassModifiersIface1
{
	public function bar();
}

interface TokenReflection_Test_ClassModifiersIface4 extends TokenReflection_Test_ClassModifiersIface1
{

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

abstract class TokenReflection_Test_ClassModifiersClass4 implements TokenReflection_Test_ClassModifiersIface2
{

}

abstract class TokenReflection_Test_ClassModifiersClass5 implements TokenReflection_Test_ClassModifiersIface3, IteratorAggregate
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

	public function getIterator()
	{

	}

	public function serialize()
	{

	}

	public function unserialize($serialized)
	{

	}
}

class TokenReflection_Test_ClassModifiersClass8 implements TokenReflection_Test_ClassModifiersIface1
{

}
