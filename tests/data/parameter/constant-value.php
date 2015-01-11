<?php

const TOKEN_REFLECTION_PARAMETER_CONSTANT_VALUE = 'foo';

class TokenReflection_Test_ParameterConstantValue
{

	const VALUE = 'bar';


	public function constantValue($one = 'foo', $two = 'bar', $three = self::VALUE, $four = TokenReflection_Test_ParameterConstantValue::VALUE, $five = TOKEN_REFLECTION_PARAMETER_CONSTANT_VALUE)
	{

	}
}
