<?php

class TokenReflection_Test_ClassInterfacesParent implements Iterator
{
	public function key()
	{
	}

	public function current()
	{
	}

	function next()
	{
	}

	function valid()
	{
	}

	function rewind()
	{
	}
}

class TokenReflection_Test_ClassInterfaces extends TokenReflection_Test_ClassInterfacesParent implements Countable, ArrayAccess, Serializable
{
	function count()
	{
	}

	function serialize()
	{
	}

	function unserialize($serialized)
	{
	}

	function offsetExists($offset)
	{
	}

	function offsetGet($offset)
	{
	}

	function offsetSet($offset, $value)
	{
	}

	public function offsetUnset($offset)
	{
	}
}
