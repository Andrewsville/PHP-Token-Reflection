<?php

interface TokenReflection_Test_ConstantInterface
{
	const FIRST = 1;
}

interface TokenReflection_Test_ConstantInterface2 extends TokenReflection_Test_ConstantInterface
{
	const SECOND = 2;
}

class TokenReflection_Test_ConstantInterfaceClass implements TokenReflection_Test_ConstantInterface
{

}

class TokenReflection_Test_ConstantInterfaceClass2 implements TokenReflection_Test_ConstantInterface2
{

}
