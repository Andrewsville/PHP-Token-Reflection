<?php

class TokenReflection_Test_PropertyModifiers
{
	public static $publicStatic = true;
	protected static $protectedStatic = true;
	private static $privateStatic = true;

	public $public = true;
	private $noPublic = true;
	protected $protected = true;
	private $noProtected = true;
	private $private = true;
	public $noPrivate = true;
}