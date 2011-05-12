<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0beta1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection;

/**
 * Docblock parser.
 */
class ReflectionAnnotation
{
	/**
	 * Main description annotation identifier.
	 *
	 * White space at the beginning on purpose.
	 *
	 * @var string
	 */
	const SHORT_DESCRIPTION = ' short_description';

	/**
	 * Sub description annotation identifier.
	 *
	 * White space at the beginning on purpose.
	 *
	 * @var string
	 */
	const LONG_DESCRIPTION = ' long_description';

	/**
	 * Parses reflection object documentation.
	 *
	 * @param ReflectionBase $reflection Reflection object
	 * @return array
	 */
	public static function parse(ReflectionBase $reflection)
	{
		static $emptyResult = array();

		$docblock = $reflection->getInheritedDocComment();

		if (false === $docblock) {
			return $emptyResult;
		}

		// Parse docblock
		$result = $emptyResult;
		$name = self::SHORT_DESCRIPTION;
		$docblock = trim(preg_replace(array('~^/\\s*\\*\\*~', '~\\*/$~'), '', $docblock));
		foreach (explode("\n", $docblock) as $line) {
			$line = preg_replace('~^\\*\\s*~', '', trim($line));

			// End of short description
			if ('' === $line && self::SHORT_DESCRIPTION === $name) {
				$name = self::LONG_DESCRIPTION;
				continue;
			}

			// @annotation
			if (preg_match('~^@([\\S]+)\\s*(.*)~', $line, $matches)) {
				$name = $matches[1];
				$result[$name][] = $matches[2];
				continue;
			}

			// Continuation
			if (self::SHORT_DESCRIPTION === $name || self::LONG_DESCRIPTION === $name) {
				if (!isset($result[$name])) {
					$result[$name] = $line;
				} else {
					$result[$name] .= "\n" . $line;
				}
			} else {
				$result[$name][count($result[$name]) - 1] .= "\n" . $line;
			}
		}

		array_walk_recursive($result, function(&$value) {
			$value = trim($value);
		});

		return $result;
	}
}
