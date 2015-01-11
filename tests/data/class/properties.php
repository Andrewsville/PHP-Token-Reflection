<?php

class TokenReflection_Test_ClassPropertiesParent
{

	protected static $protectedStatic = 1;

	protected $protected = 0;

}


class TokenReflection_Test_ClassProperties extends TokenReflection_Test_ClassPropertiesParent
{

	public static $publicStatic = TRUE;

	private static $privateStatic = 'something';

	public $public = FALSE;

	private $private = '';

}
