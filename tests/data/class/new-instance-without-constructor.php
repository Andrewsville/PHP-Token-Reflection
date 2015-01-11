<?php

class TokenReflection_Test_NewInstanceWithoutConstructor1 extends ArrayObject
{

}


class TokenReflection_Test_NewInstanceWithoutConstructor2
{

	public $check = FALSE;


	public function __construct()
	{
		$this->check = TRUE;
	}

}
