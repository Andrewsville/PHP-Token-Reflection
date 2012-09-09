<?php

namespace ns {
	class nonexistent
	{
		private $constructorCalled = false;

		public function __construct()
		{
			$this->constructorCalled = true;
		}

		public function wasConstrustorCalled()
		{
			return $this->constructorCalled;
		}
	}
}

namespace {
	class nonexistent
	{
		private $constructorCalled = false;

		public function __construct()
		{
			$this->constructorCalled = true;
		}

		public function wasConstrustorCalled()
		{
			return $this->constructorCalled;
		}
	}
}
