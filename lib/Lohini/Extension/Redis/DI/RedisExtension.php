<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Redis\DI;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Config\Configurator;

/**
 */
class RedisExtension
extends \Nette\Config\CompilerExtension
{
	/** @var array */
	public $defaults = array(
		'journal' => FALSE,
		'storage' => FALSE,
		'session' => FALSE,
		'host' => 'localhost',
		'port' => 6379,
		'timeout' => 10,
		'database' => 0
		);


	public function loadConfiguration()
	{
		$builder=$this->getContainerBuilder();
		$config=$this->getConfig($this->defaults);

		$client=$builder->addDefinition($this->prefix('client'))
			->setClass('Lohini\Extension\Redis\RedisClient', array(
				'host' => $config['host'],
				'port' => $config['port'],
				'database' => $config['database'],
				'timeout' => $config['timeout']
				));

		if ($builder->parameters['debugMode']) {
			$client->addSetup('setPanel');
			}

		if ($config['journal']) {
			$builder->removeDefinition('nette.cacheJournal');
			$builder->addDefinition('nette.cacheJournal')
				->setClass('Lohini\Extension\Redis\RedisJournal');
			}

		if ($config['storage']) {
			$builder->removeDefinition('cacheStorage');
			$builder->addDefinition('cacheStorage')
				->setClass('Lohini\Extension\Redis\RedisStorage');
			}

		if ($config['session']) {
			$builder->getDefinition('session')
					->addSetup('setStorage', array(new Statement('Lohini\Extension\Redis\RedisSessionHandler')));
			}

		$builder->addDefinition($this->prefix('panel'))
			->setFactory('Lohini\Extension\Redis\Diagnostics\Panel::register');
	}

	/**
	 * @param \Nette\Config\Configurator $config
	 */
	public static function register(Configurator $config)
	{
		$config->onCompile[]=function(Configurator $config, \Nette\Config\Compiler $compiler) {
			$compiler->addExtension('redis', new RedisExtension);
			};
	}
}
