<?php

const TOKENREFLECTION_FUNCTION_STATIC_VARIABLE_VALUE = 'constant value';

function tokenReflectionFunctionStaticVariables()
{
	static $string = 'string';
	static $integer = 1;
	static $float = 1.1;
	static $boolean = TRUE;
	static $null = NULL;
	static $array = [1 => 1];
	static $array2 = [1 => 1, 2 => 2];
	static $constant = TOKENREFLECTION_FUNCTION_STATIC_VARIABLE_VALUE;
}
