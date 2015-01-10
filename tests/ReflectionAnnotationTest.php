<?php

namespace ApiGen\TokenReflection\Tests;

use ApiGen;
use ApiGen\TokenReflection\ReflectionAnnotation;


class ReflectionAnnotationTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'annotation';

	/**
	 * Tests an exception thrown on an invalid docblock template.
	 *
	 * This exception is never thrown when using TR the "standard way".
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
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
