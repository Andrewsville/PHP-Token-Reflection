<?php

class TokenReflection_Test_PropertyDocComment
{
	/**
	 * This is a property.
	 *
	 * @var String It is a string
	 * 	and this comment has multiple
	 * 	lines.
	 */
	public $docComment = 'doc-comment';

	public
		/** Holds current length */
		$length = 0,

		/** Remembers current index */
		$index;
}