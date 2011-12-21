<?php

class TokenReflection_Test_PropertyDocComment
{
	/**
	 * This is a property.
	 */
	public $docComment = 'doc-comment';

	public
		/** Holds current length */
		$length = 0,

		/** Remembers current index */
		$index;
}