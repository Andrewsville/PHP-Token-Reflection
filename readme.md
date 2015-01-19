# Token Reflection [WIP]

[![Build Status](https://img.shields.io/travis/apigen/TokenReflection/master.svg?style=flat-square)](https://travis-ci.org/apigen/TokenReflection)
[![Quality Score](https://img.shields.io/scrutinizer/g/ApiGen/TokenReflection.svg?style=flat-square)](https://scrutinizer-ci.com/g/ApiGen/TokenReflection)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/ApiGen/TokenReflection.svg?style=flat-square)](https://scrutinizer-ci.com/g/ApiGen/TokenReflection)
[![Downloads this Month](https://img.shields.io/packagist/dm/apigen/token-reflection.svg?style=flat-square)](https://packagist.org/packages/apigen/token-reflection)
[![Latest stable](https://img.shields.io/packagist/v/apigen/token-reflection.svg?style=flat-square)](https://packagist.org/packages/apigen/token-reflection)


This library emulates the PHP reflection model using the tokenized PHP source and creates Reflection for every element available (see [Usage](#usage)).


## Installation

```sh
composer require apigen/token-reflection
```

### Nette Extension

If you use [Nette](http://nette.org), you can register extension in your `config.neon`:

```yaml
extensions:
	- ApiGen\TokenReflection\DI\TokenReflectionExtension
```


## Usage

First, you need to parse source code with [ApiGen\TokenReflection\Broker\Broker](src/Broker/Broker.php).
It walks through the given directories, tokenizes PHP sources and caches reflection objects.

```php
<?php
namespace ApiGen\TokenReflection;

$broker = new Broker(new Broker\Backend\Memory);
$broker->processDirectory('~/lib/Zend_Framework');

$class = $broker->getClass('Zend_Version'); // instance of ApiGen\TokenReflection\Reflection\ReflectionClass
$class = $broker->getClass('Exception');    // instance of ApiGen\TokenReflection\Php\ReflectionClass
$class = $broker->getClass('Nonexistent');  // instance of ApiGen\TokenReflection\Dummy\ReflectionClass

$function = $broker->getFunction(...);
$constant = $broker->getConstant(...);
```


## Particular Reflections

There are reflections namespace, class, function/method, constant, property and parameter.
For internal use also reflections for file and file-namespace. 

**ReflectionClass**, **ReflectionFunction**, **ReflectionMethod**, **ReflectionParameter** and **ReflectionProperty** work similar, but not the same way as their internal reflection namesakes.

Let's look at rest of reflections:

### ReflectionNamespace
 
It's a similar (in name) yet quite different (in meaning) structure. It is a unique structure for every namespace and it holds all constants, functions and classes from this particular namespace inside. In fact, it is a simple container. It also is not created directly by any parent reflection, but the Broker creates it.

Why do we need two separate classes? Because namespaces can be split into many files and in each file it can have individual namespace aliases. And those have to be taken into consideration when resolving parent class/interface names. It means that a ReflectionFileNamespace is created for every namespace in every file and it parses its contents, resolves fully qualified names of all classes, their parents and interfaces. Later, the Broker takes all ReflectionFileNamespace instances of the same namespace and merges them into a single ReflectionNamespace instance.

### ReflectionConstant

It's our addition to the reflection model. It can return name, value and how it was defined.


## Internal Elements - `ApiGen\TokenReflection\Php\*`

When you ask the Broker for an internal element e.g. `Zip` or `Phar`, it returns a `ApiGen\TokenReflection\Php\Reflection*` that encapsulates the internal reflection functionality and adds our features.


## Dealing with Duplicated Names - `ApiGen\TokenReflection\Invalid\*`

When the library encounters a duplicate element, it converts the previously created reflection into an `ApiGen\TokenReflection\Invalid\Reflection*` instance.
Then it throws an exception. When you catch this exception and continue to work with the Broker instance, the duplicate elements will have only one reflection.
