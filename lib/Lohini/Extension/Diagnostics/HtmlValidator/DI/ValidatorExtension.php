<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Diagnostics\HtmlValidator\DI;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Config\Configurator;

/**
 */
class ValidatorExtension
extends \Nette\Config\CompilerExtension
{
	public function loadConfiguration()
	{
		$builder=$this->getContainerBuilder();
		if (!$builder->parameters['debugMode']) {
			return;
			}

		$builder->addDefinition($this->prefix('panel'))
			->setClass('Lohini\Extension\Diagnostics\HtmlValidator\ValidatorPanel')
			->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array('@self'));

		$builder->getDefinition('application')
			->addSetup('$service->onStartup[] = ?', array(array($this->prefix('@panel'), 'startBuffering')))
			->addSetup('$service->onShutdown[] = ?', array(array($this->prefix('@panel'), 'validate')))
			->addSetup('$service->onError[] = ?', array(array($this->prefix('@panel'), 'stopBuffering')));
	}

	/**
	 * @param Configurator $config
	 */
	public static function register(Configurator $config)
	{
		$config->onCompile[]=function(Configurator $config, \Nette\Config\Compiler $compiler) {
			$compiler->addExtension('htmlValidator', new ValidatorExtension);
			};
	}
}
