<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing\ORM;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\EntityManager;

/**
 * Inception!
 */
class SandboxConfigurator
extends \Lohini\Config\Configurator
{
	/** @var array */
	private $entities=array();


	/**
	 * @param array $params
	 */
	public function __construct($params=NULL)
	{
		parent::__construct($params, \Lohini\Core::createPackagesList());
		$this->setEnvironment('test');
		$this->setDebugMode(FALSE);
	}

	/**
	 * @return SandboxRegistry
	 * @throws \Lohini\UnexpectedValueException
	 */
	final public function getRegistry()
	{
		/** @var SandboxRegistry $registry */
		$registry=$this->getContainer()->doctrine->registry;
		if (!$registry instanceof SandboxRegistry) {
			throw new \Lohini\UnexpectedValueException("Service 'doctrine' must be instance of 'Lohini\\Testing\\ORM\\SandboxRegistry', instance of '".get_class($registry)."' given.");
			}
		$registry->setConfigurator($this);
		return $registry;
	}

	/**
	 * @return string
	 */
	public function getConfigFile()
	{
		return $this->parameters['appDir'].'/config.orm.neon';
	}

	/**
	 * @param EntityManager $manager
	 */
	public function configureManager(EntityManager $manager)
	{
		$this->configureEntities($manager);
		$this->refreshSchema($manager);
		$this->generateProxyClasses($manager);
	}

	/**
	 * @param EntityManager $manager
	 */
	private function configureEntities(EntityManager $manager)
	{
		if (!$this->entities) {
			return;
			}

		$entities=$this->entities;
		foreach ($entities as $child) {
			$this->mergeParents($child, $entities, $manager);
			}

		$this->setClassNames($manager, $entities);

		$allClasses=array();
		do {
			$this->mergeParents(reset($entities), $entities, $manager, $allClasses);

			$allClasses[]= $entity= array_shift($entities);
			$this->setClassNames($manager, $allClasses, $entities);

			$class=$manager->getClassMetadata($entity);
			/** @var \Lohini\Database\Doctrine\Mapping\ClassMetadata $class */
			foreach ($class->getAssociationNames() as $assoc) {
				$entities=array_merge($entities, array($class->getAssociationTargetClass($assoc)));
				}

			if ($root=$class->rootEntityName) {
				$class=$manager->getClassMetadata($root);
				$entities=array_merge($entities, array_values($class->discriminatorMap));
				}
			} while ($entities=array_diff(array_unique($entities), $allClasses));
	}

	/**
	 * @param EntityManager $manager
	 * @param array $allClasses
	 * @param array $additional
	 */
	private function setClassNames(EntityManager $manager, array $allClasses, array $additional=array())
	{
		if ($additional) {
			$allClasses=array_unique(array_merge($allClasses, $additional));
			}

		foreach ($this->getAnnotationDrivers($manager) as $driver) {
			$driver->setClassNames($allClasses);
			}
	}

	/**
	 * @param object $child
	 * @param array $entities
	 * @param EntityManager $manager
	 * @param array $allClasses
	 */
	private function mergeParents($child, array &$entities, EntityManager $manager, array $allClasses=NULL)
	{
		foreach (class_parents($child) as $entity) {
			if ($this->getDriver($manager)->isTransient($entity)) {
				continue;
				}

			if (in_array($entity, $allClasses ?: $entities)) {
				continue;
				}

			array_unshift($entities, $entity);
			}
	}

	/**
	 * Crawls all the entities associations, to avoid requiring of listing of all classes, required by test, by hand.
	 * Associations are gonna be discovered automatically.
	 * Lazily.
	 *
	 * @param array $entities
	 */
	public function setEntities(array $entities=NULL)
	{
		$this->entities= $entities ?: array();
	}

	/**
	 * @param EntityManager $em
	 * @return \Doctrine\ORM\Mapping\Driver\Driver
	 */
	private function getDriver(EntityManager $em)
	{
		return $em->getConfiguration()->getMetadataDriverImpl();
	}

	/**
	 * @param EntityManager $em
	 * @return \Lohini\Database\Doctrine\Mapping\Driver\AnnotationDriver[]
	 */
	private function getAnnotationDrivers(EntityManager $em)
	{
		$drivers=array();

		$drivers[]= $driver= $em->getConfiguration()->getMetadataDriverImpl();
		if ($driver instanceof \Doctrine\ORM\Mapping\Driver\DriverChain) {
			/** @var \Doctrine\ORM\Mapping\Driver\DriverChain $driver */
			$drivers=array_merge($drivers, $driver->getDrivers());
			}

		return array_filter(
			$drivers,
			function($driver) { return $driver instanceof \Lohini\Database\Doctrine\Mapping\Driver\AnnotationDriver; }
			);
	}

	/**
	 * Prepare schema
	 *
	 * @param EntityManager $em
	 */
	private function refreshSchema(EntityManager $em)
	{
		$schemaTool=new \Lohini\Database\Doctrine\Schema\SchemaTool($em);
		$classes=$em->getMetadataFactory()->getAllMetadata();
		$schemaTool->createSchema($classes);
	}

	/**
	 * @param EntityManager $em
	 * @throws \Nette\IOException
	 */
	private function generateProxyClasses(EntityManager $em)
	{
		$proxyDir=$em->getConfiguration()->getProxyDir();
		@mkdir($proxyDir, 0777);

		// deleting classes
		foreach (\Nette\Utils\Finder::findFiles('*Proxy.php')->in($proxyDir) as $proxy) {
			/** @var \SplFileInfo $proxy */
			if (!@unlink($proxy->getRealpath())) {
				throw new \Nette\IOException('Proxy class '.$proxy->getBaseName().' cannot be deleted.');
				}
			}

		// rebuild proxies
		$classes=$em->getMetadataFactory()->getAllMetadata();
		$em->getProxyFactory()->generateProxyClasses($classes);
	}

	/**
	 * Setups the Debugger defaults
	 *
	 * @param array $params
	 */
	protected function setupDebugger($params=array())
	{
		// pass
	}
}
