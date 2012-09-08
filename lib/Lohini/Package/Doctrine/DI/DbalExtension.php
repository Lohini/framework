<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Package\Doctrine\DI;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\DI\ContainerBuilder,
	Nette\Utils\Validators;

/**
 * DbalExtension is an extension for the Doctrine DBAL library.
 */
class DbalExtension
extends \Lohini\Config\CompilerExtension
{
	/** @var array */
	public $connectionDefaults=array(
		'dbname' => NULL,
		'host' => NULL,
		'port' => NULL,
		'user' => NULL,
		'password' => NULL,
		'charset' => NULL,
		'driver' => 'pdo_mysql',
		'driverClass' => NULL,
		'options' => NULL,
		'path' => NULL,
		'memory' => NULL,
		'unix_socket' => NULL,
		'wrapperClass' => 'Lohini\Database\Doctrine\Connection',
		'logging' => TRUE,
		'platformService' => NULL,
		'mappingTypes' => array(
			'enum' => 'enum'
			)
		);
	/** @var array */
	public $driverDefaults=array(
		'pdo_mysql' => array(
			'host' => 'localhost',
			'port' => 3306,
			'charset' => 'UTF8',
			)
		);
	/** @var array */
	protected $defaultTypes=array(
		\Lohini\Database\Doctrine\Type::CALLBACK => 'Lohini\Database\Doctrine\Types\Callback',
		\Lohini\Database\Doctrine\Type::PASSWORD => 'Lohini\Database\Doctrine\Types\Password',
		\Lohini\Database\Doctrine\Type::ENUM => 'Lohini\Database\Doctrine\Types\Enum'
		);


	/**
	 * dbal:
	 * 	dbname: database
	 * 	user: root
	 * 	password: 123
	 */
	public function loadConfiguration()
	{
		$container=parent::loadConfiguration();
		$config=$this->getConfig();

		$connections= isset($config['connections'])? $config['connections'] : array('default' => $config);

		// default connection
		if (empty($config['defaultConnection'])) {
			$keys=array_keys($connections);
			$config['defaultConnection']=reset($keys);
			}
		$container->parameters['doctrine']['defaultConnection']=$config['defaultConnection'];

		$types=$this->defaultTypes;
		if (isset($config['types'])) {
			Validators::assertField($config, 'types', 'array');
			$types=$config['types']+$types;
			}
		$container->parameters['doctrine']['dbal']['connectionFactory']['types']=$types;

		// connections list
		foreach (array_keys($connections) as $name) {
			$container->parameters['doctrine']['connections'][$name]="doctrine.dbal.{$name}Connection";
			}

		// load connections
		foreach ($connections as $name => $connection) {
			$connection['name']=$name;
			$this->loadConnection($container, $connection);
			}

		$this->addAlias('doctrine.dbal.connection', 'doctrine.dbal.'.$config['defaultConnection'].'Connection');
		$this->addAlias('doctrine.dbal.eventManager', 'doctrine.dbal.'.$config['defaultConnection'].'Connection.eventManager');
	}

	/**
	 * Loads a configured DBAL connection.
	 *
	 * @param ContainerBuilder $container
	 * @param array $config
	 */
	protected function loadConnection(ContainerBuilder $container, array $config)
	{
		$connectionName='doctrine.dbal.'.$config['name'].'Connection';

		// options
		$options=self::getOptions($config, $this->connectionDefaults);
		if (isset($this->driverDefaults[$options['driver']])) {
			$options+=$this->driverDefaults[$options['driver']];
			}

		// configuration
		$configuration=$container->addDefinition($connectionName.'.configuration')
			->setClass('Doctrine\DBAL\Configuration');

		// logging
		$container->addDefinition($connectionName.'.logger')
			->setClass('Lohini\Database\Doctrine\Diagnostics\Panel')
			->setFactory('Lohini\Database\Doctrine\Diagnostics\Panel::register');

		if ($options['logging']) {
			$configuration->addSetup('setSQLLogger', array("@$connectionName.logger"));
			}

		// event manager
		$container->addDefinition($connectionName.'.eventManager')
			->setClass('Lohini\Extension\EventDispatcher\LazyEventManager')
			->addSetup('addSubscribers', array(
				new \Nette\DI\Statement('Lohini\Config\TaggedServices', array('doctrine.eventSubscriber.'.$config['name']))
				))
			->setAutowired(FALSE);

		// charset
		$this->loadConnectionCharset($container, $options+array('name' => $config['name']), $connectionName);

		// connection factory
		$container->addDefinition($connectionName.'.factory')
			->setClass('Lohini\Package\Doctrine\ConnectionFactory', array('%doctrine.dbal.connectionFactory.types%'))
			->setInternal(TRUE);

		// connection
		Validators::assertField($options, 'mappingTypes', 'array');
		$connection=$container->addDefinition($connectionName)
			->setClass('Doctrine\DBAL\Connection')
			->setFactory("@$connectionName.factory::createConnection", array(
				$options,
				"@$connectionName.configuration",
				"@$connectionName.eventManager",
				$options['mappingTypes']
				));

		if ($options['logging']) {
			$connection->addSetup('$service->getConfiguration()->getSQLLogger()->setConnection(?)', array('@self'));
			}
	}

	/**
	 * @param ContainerBuilder $container
	 * @param array $config
	 * @param string $connectionName
	 */
	protected function loadConnectionCharset(ContainerBuilder $container, array $config, $connectionName)
	{
		if ($this->connectionUsesMysqlDriver($config)) {
			$container->addDefinition($connectionName.'.events.mysqlSessionInit')
				->setClass('Doctrine\DBAL\Event\Listeners\MysqlSessionInit', array($config['charset']))
				->addTag('doctrine.eventSubscriber.'.$config['name']);
			}
	}

	/**
	 * @param array $connection
	 * @return bool
	 */
	protected function connectionUsesMysqlDriver(array $connection)
	{
		return (isset($connection['driver']) && stripos($connection['driver'], 'mysql')!==FALSE)
			|| (isset($connection['driverClass']) && stripos($connection['driverClass'], 'mysql')!==FALSE);
	}
}
