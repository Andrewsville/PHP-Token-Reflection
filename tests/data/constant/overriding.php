<?php

class TokenReflection_Test_ConstantOverridingBase
{
	const FOO = 'bar';
}

class TokenReflection_Test_ConstantOverriding extends TokenReflection_Test_ConstantOverridingBase
{
	const FOO = 'notbar';
}