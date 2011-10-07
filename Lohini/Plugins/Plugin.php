<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Plugins;

use Nette\Environment,
	Doctrine\DBAL\Migrations;

/**
 * @author Lopo <lopo@lohini.net>
 */
abstract class Plugin
extends \Nette\Object
{
	const STATE_REGISTERED='registered';
	const STATE_INSTALLED='installed';
	const STATE_ENABLED='enabled';
	const STATE_DELETED='deleted';

	/** @var \Lohini\Database\Models\Entities\Plugin */
	protected $entity;
	/**
	 * @var array
	 */
	protected $validDependencies=array(
		'nette', 'lohini', 'php',
		'php_functions',
		'plugins'
		);
	protected $dependencies=array();


	/**
	 * @param \Lohini\Database\Models\Entities\Plugin $entity
	 */
	public function __construct(\Lohini\Database\Models\Entities\Plugin $entity)
	{
		$this->entity=$entity;
	}

	/**
	 * @param \Nette\Application\IRouter $router
	 */
	public function injectRouter(\Nette\Application\IRouter $router) {}

	/**
	 * @param \Lohini\Localization\ITranslator $router
	 */
	public function injectTranslation(\Lohini\Localization\ITranslator $translator) {}

	/**
	 * @throws PluginException
	 * @throws \Nette\InvalidStateException
	 */
	public function checkDependencies()
	{
		foreach ($this->dependencies as $dk => $dv) {
			if (!in_array($dk, $this->validDependencies)) {
				throw PluginException::invalidDependency($dk);
				}
			switch ($dk) {
				case 'nette':
					$av=\Nette\Framework::VERSION;
					break;
				case 'lohini':
					$av=\Lohini\Core::VERSION;
					break;
				case 'php':
					$av=phpversion();
					break;
				case 'php_functions':
					foreach ($dv as $fn) {
						if (!function_exists($fn)) {
							throw PluginException::missingDependency("PHP function $dk");
							}
						}
					continue;
				case 'plugins':
					$pl=Environment::getService('pluginManager')->getPlugins();
					foreach ($dv as $pn => $pv) {
						if (!in_array($pn, $pl)) {
							throw PluginException::missingDependency("Plugin '$pn'");
							}
						if (!$pl[$pn]->enabled) {
							throw new \Nette\InvalidStateException("Required plugin '$pn' isn't enabled");
							}
						if (version_compare($pl[$pn]->iversion, $pv, '>=')) {
							throw PluginException::outdatedDependency("plugin '$pn'", $pv, $pl[$pn]->iversion);
							}
						}
					continue;
				}
			if (!version_compare($av, $dv, '>=')) {
				throw PluginException::outdatedDependency($dk, $dv, $av);
				}
			}
		return TRUE;
	}

	/**
	 * 
	 */
	public function preInstall()
	{
		$this->checkDependencies();
	}

	/**
	 * installs plugin
	 */
	final public function install()
	{
		$cfg=$this->getMigrationsConfiguration();
		if (!$cfg->createMigrationTable()) {
			throw PluginException::installError('Versioning table exists, plugin already installed ?');
			}
		$this->getMigration($cfg)->migrate();
	}

	/**
	 * 
	 */
	public function postInstall() {}

	/**
	 * 
	 */
	public function preUpdate() {}

	/**
	 * installs plugin
	 */
	final public function update()
	{
		$this->getMigration()->migrate();
	}

	/**
	 * 
	 */
	public function postUpdate() {}

	/**
	 * 
	 */
	public function preUninstall() {}

	/**
	 * uninstalls plugin
	 */
	final public function uninstall()
	{
		$cfg=$this->getMigrationsConfiguration();
		$this->getMigration($cfg)->migrate(0);
		Environment::getService('sqldb')->entityManager->getConnection()->getSchemaManager()
				->dropTable($cfg->getMigrationsTableName());
	}

	/**
	 * 
	 */
	public function postUninstall() {}

	/**
	 * @return \Doctrine\DBAL\Migrations\Configuration\Configuration
	 * @throws PluginException
	 */
	private function getMigrationsConfiguration()
	{
		$entity=$this->entity;
		$pclass=$entity->pluginClass;
		$cfg=new Migrations\Configuration\Configuration(Environment::getService('sqldb')->entityManager->getConnection());
		$cfg->setMigrationsNamespace($entity->pluginNS.'\\Models\\Versions');
		$cfg->setMigrationsDirectory($entity->pluginPath.'/Models/Versions');
		$cfg->setMigrationsTableName($pclass::PREFIX.'__versions');
		return $cfg;
	}

	/**
	 *
	 * @param \Doctrine\DBAL\Migrations\Configuration\Configuration $cfg
	 * @return \Doctrine\DBAL\Migrations\Migration
	 */
	protected function getMigration($cfg=NULL)
	{
		if ($cfg===NULL) {
			$cfg=$this->getMigrationsConfiguration();
			}
		$cfg->registerMigrationsFromDirectory($cfg->getMigrationsDirectory());
		return new Migrations\Migration($cfg);
	}
}
