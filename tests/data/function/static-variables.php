<?php

function tokenReflectionFunctionStaticVariables()
{
	static $string = 'string';
	static $integer = 1;
	static $float = 1.1;
	static $boolean = true;
	static $null = null;
	static $array = array(1 => 1);
}
