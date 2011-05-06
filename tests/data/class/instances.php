<?php

class TokenReflection_Test_ClassInstances
{
	protected $private = 0;

	public function __construct($private = 0)
	{
		$this->private = $private;
	}
}

class TokenReflection_Test_ClassInstancesChild extends TokenReflection_Test_ClassInstances
{
}
