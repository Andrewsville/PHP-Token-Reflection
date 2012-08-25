<?php
/**
 * PHP Token Reflection
 *
 * Version 1.3.1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Annotations test.
 */
class ReflectionAnnotationTest extends Test
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'annotation';

	/**
	 * Tests an exception thrown on an invalid docblock template.
	 *
	 * This exception is never thrown when using TR the "standard way".
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testAnnotationInvalidTemplate()
	{
		$broker = $this->getBroker();
		$broker->processString('<?php class AnnotationInvalidTemplate {}', 'annotationInvalidTemplate.php');

		$this->assertTrue($broker->hasClass('AnnotationInvalidTemplate'));
		$class = $broker->getClass('AnnotationInvalidTemplate');

		$a = new ReflectionAnnotation($class);
		$a->setTemplates(array(new \Exception()));
	}
}
