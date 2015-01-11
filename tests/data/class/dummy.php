<?php

namespace ns {
	class nonexistent
	{

		private $constructorCalled = FALSE;


		public function __construct()
		{
			$this->constructorCalled = TRUE;
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

		private $constructorCalled = FALSE;


		public function __construct()
		{
			$this->constructorCalled = TRUE;
		}


		public function wasConstrustorCalled()
		{
			return $this->constructorCalled;
		}
	}

}
