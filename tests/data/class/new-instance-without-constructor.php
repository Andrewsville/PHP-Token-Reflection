<?php

class TokenReflection_Test_NewInstanceWithoutConstructor1 extends ArrayObject
{
}

class TokenReflection_Test_NewInstanceWithoutConstructor2
{

	public $check = false;

	public function __construct()
	{
		$this->check = true;
	}

}
