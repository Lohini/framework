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
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * DbalExtension is an extension for the Doctrine DBAL library.
 */
class AuditExtension
extends \Lohini\Config\CompilerExtension
{
	/** @var array */
	public $auditDefaults=array(
		'prefix' => '',
		'suffix' => '_audit',
		'tableName' => 'revisions',
		);
	/** @var array */
	private $managers=array();


	/**
	 */
	public function loadConfiguration()
	{
		$builder=parent::loadConfiguration();
		$config=$this->getConfig($this->auditDefaults);

		$this->managers=array();
		foreach ($builder->parameters['doctrine']['entityManagers'] as $name => $id) {
			$this->loadAuditManager($name, $id, $config);
			}

		$builder->parameters['doctrine']['auditManagers']=$this->managers;
	}

	/**
	 * @param string $name
	 * @param string $emId
	 * @param array $config
	 */
	private function loadAuditManager($name, $emId, array $config)
	{
		$builder=$this->getContainerBuilder();

		$configurator=$this->prefix($name.'.configuration');
		$builder->addDefinition($configurator)
			->setClass('Lohini\Database\Doctrine\Audit\AuditConfiguration')
			->addSetup('$prefix', array($config['prefix']))
			->addSetup('$suffix', array($config['suffix']))
			->addSetup('$tableName', array($config['tableName']));

		$this->managers[$name]= $manager= $this->prefix($name.'.manager');
		$builder->addDefinition($manager)
			->setClass('Lohini\Database\Doctrine\Audit\AuditManager', array('@'.$configurator, '@'.$emId));

		$builder->addDefinition($this->prefix($name.'.listener.createSchema'))
			->setClass('Lohini\Database\Doctrine\Audit\Listener\CreateSchemaListener', array('@'.$manager))
			->addTag('doctrine.eventSubscriber');

		$builder->addDefinition($this->prefix($name.'.listener.currentUser'))
			->setClass('Lohini\Database\Doctrine\Audit\Listener\CurrentUserListener', array('@'.$configurator))
			->addTag('doctrine.eventSubscriber');
	}
}
