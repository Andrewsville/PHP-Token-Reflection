<?php

class TokenReflection_Test_PropertyValueDefinitions
{
	private $property1 = true;
	private $property2 = true ;
	private $property3 = true /** foo */ ;
	private $property4 = /** foo */ true;
	private $property5 /** foo */ = true;
	private $property6 /** foo */ = true /** foo */ ;
	private $property7 /** foo */ = /** foo */ true /** foo */ ;
	private /** foo */ $property8 = true;
}