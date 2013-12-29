<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
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
