<?php

class TokenReflection_Test_ClassConstantsParent
{
	const PARENT = 'parent';
}

class TokenReflection_Test_ClassConstants extends TokenReflection_Test_ClassConstantsParent
{
	const STRING = 'string';
	const INTEGER = 1;
	const FLOAT = 1.1;
	const BOOLEAN = true;
}