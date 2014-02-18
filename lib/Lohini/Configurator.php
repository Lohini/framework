<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
namespace Lohini;

if (PHP_VERSION_ID<50400) {
	throw new \Exception('Lohini Framework requires PHP 5.4 or newer.');
	}

/**
 * Initial system DI container generator
 *
 * @author Lopo <lopo@lohini.net>
 */
class Configurator
extends \Nette\Configurator
{
	protected function createCompiler()
	{
		$compiler=parent::createCompiler();
		$compiler
				->addExtension(DI\Extensions\LohiniExtension::DEFAULT_NAME, new DI\Extensions\LohiniExtension);
		return $compiler;
	}
}
