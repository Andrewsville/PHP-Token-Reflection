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
	 * @var string
	 */
	const SHORT_DESCRIPTION = 'short_description';

	/**
	 * Sub description annotation identifier.
	 *
	 * @var string
	 */
	const LONG_DESCRIPTION = 'long_description';

	/**
	 * Parses reflection object documentation.
	 *
	 * @param ReflectionBase $reflection Reflection object
	 * @return array
	 */
	public static function parse(ReflectionBase $reflection)
	{
		$docblock = $reflection->getInheritedDocComment();

		if (false === $docblock) {
			return array();
		}

		// Preprocess docblock
		$docblock = trim(preg_replace(array('~^/\s*\*\*~', '~\*/$~'), '', $docblock));
		$docblock = array_map(function($line) {
			return preg_replace('~^\\s*\\*\\s*~', '', trim($line), 1);
		}, explode("\n", $docblock));

		// Parse docblock
		$result = array();
		$name = self::SHORT_DESCRIPTION;
		foreach ($docblock as $line) {
			if (preg_match('~^@\\s*([\\S]+)\\s*(.*)~', $line, $matches)) {
				if (!isset($result['PARAMS'])) {
					$result['PARAMS'] = array();
				}

				$name = strtolower($matches[1]);

				if (!isset($result['PARAMS'][$name])) {
					$result['PARAMS'][$name] = array();
				}
				$result['PARAMS'][$name][] = $matches[2];
			} else {
				if (empty($line)) {
					if (self::SHORT_DESCRIPTION === $name) {
						// End of main description
						$name = self::LONG_DESCRIPTION;
						continue;
					} else {
						$line = "\n";
					}
				}

				if (self::SHORT_DESCRIPTION === $name || self::LONG_DESCRIPTION === $name) {
					if (!isset($result[$name])) {
						$result[$name] = $line;
					} else {
						$result[$name] .= "\n" . $line;
					}
				} else {
					if (is_array($result['PARAMS'][$name])) {
						$index = count($result['PARAMS'][$name]) - 1;
						$result['PARAMS'][$name][$index] .= ' ' . trim($line);
					} else {
						$result['PARAMS'][$name] .= ' ' . trim($line);
					}
				}
			}
		}

		array_walk_recursive($result, function(&$value) {
			$value = trim($value);
		});

		return $result;
	}
}
