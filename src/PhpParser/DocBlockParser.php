<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\PhpParser;


class DocBlockParser
{

	/**
	 * @var string
	 */
	const SHORT_DESCRIPTION = 'short_description';

	/**
	 * @var string
	 */
	const LONG_DESCRIPTION = 'long_description';


	/**
	 * @param string $docComment
	 * @return array
	 */
	public function parseToAnnotations($docComment)
	{
		$docBlock = $this->removeStartAndEndSlashes($docComment);
		$annotations = $this->parseDocBlockByLine($docBlock);
		return $this->trimValues($annotations);
	}


	/**
	 * @param string $docBlock
	 * @return array
	 */
	private function parseDocBlockByLine($docBlock)
	{
		$name = self::SHORT_DESCRIPTION;
		$annotations = [];
		foreach (explode("\n", $docBlock) as $line) {
			$line = preg_replace('~^\\*\\s?~', '', trim($line));

			// End of short description
			if ($line === '' && $name === self::SHORT_DESCRIPTION) {
				$name = self::LONG_DESCRIPTION;
				continue;
			}

			// @annotation
			if (preg_match('~^\\s*@([\\S]+)\\s*(.*)~', $line, $matches)) {
				$name = $matches[1];
				$annotations[$name][] = $matches[2];
				continue;
			}

			// Continuation
			if ($name === self::SHORT_DESCRIPTION || $name === self::LONG_DESCRIPTION) {
				if ( ! isset($annotations[$name])) {
					$annotations[$name] = $line;

				} else {
					$annotations[$name] .= "\n" . $line;
				}

			} else {
				$annotations[$name][count($annotations[$name]) - 1] .= "\n" . $line;
			}
		}
		return $annotations;
	}


	/**
	 * @param string $docComment
	 * @return string
	 */
	private function removeStartAndEndSlashes($docComment)
	{
		return trim(preg_replace(['~^/\\*\\*~', '~\\*/$~'], '', $docComment));
	}


	/**
	 * @return array
	 */
	private function trimValues(array $annotations)
	{
		array_walk_recursive($annotations, function (&$value) {
			$value = trim($value);
		});
		return $annotations;
	}

}
