<?php

// take care of autoloading
require_once __DIR__.'/../autoload.php';

// create container
\Lohini\Testing\Configurator::testsInit(__DIR__)
	->getContainer();
