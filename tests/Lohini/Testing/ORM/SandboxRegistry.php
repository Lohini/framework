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

use Lohini\Database\Doctrine\Dao;

/**
 */
class SandboxRegistry
extends \Lohini\Database\Doctrine\Registry
{
	/** @var \Lohini\Testing\TestCase|\Lohini\Testing\OrmTestCase */
	private $currentTest;
	/** @var SandboxConfigurator */
	private $configurator;
	/** @var Dao[] */
	private $daoMocks=array();
	/** @var \Lohini\Database\Doctrine\Mapping\ClassMetadata[] */
	private $metaMocks=array();


	/**
	 * @param \Lohini\Testing\TestCase $test
	 */
	public function setCurrentTest(\Lohini\Testing\TestCase $test)
	{
		$this->currentTest=$test;
	}

	/**
	 * @param SandboxConfigurator $configurator
	 */
	public function setConfigurator(SandboxConfigurator $configurator)
	{
		$this->configurator=$configurator;
	}

	/**
	 * @param string $name
	 * @throws \PHPUnit_Framework_SkippedTestError
	 * @throws \Nette\InvalidStateException
	 */
	public function requireConfiguredManager($name='default')
	{
		if (!$this->currentTest) {
			$method=get_called_class().'::setCurrentTest()';
			throw new \Nette\InvalidStateException("Please provide a TestCase instance using method $method.");
			}

		try {
			$this->getEntityManager($name);
			}
		catch (\Lohini\Database\Doctrine\PDOException $e) {
			throw $e;
			}
		catch (\Doctrine\ORM\ORMException $e) {
			throw $e;
			}
		catch (\Doctrine\DBAL\Schema\SchemaException $e) {
			throw $e;
			}
		catch (\Doctrine\Common\Annotations\AnnotationException $e) {
			throw $e;
			}
		catch (\Exception $e) {
			\Nette\Diagnostics\Debugger::log($e);
			$configFile=$this->currentTest->getContext()->expand('%appDir%/config.orm.neon');
			$this->currentTest->markTestSkipped(
				"TestCase requires configured EntityManager named $name. "
				."To run test properly, you have to provide valid database storage credentials in config file $configFile, "
				."in section 'orm: entityManagers: $name:'. \nDoctrine cries: ".$e->getMessage()
				);
			}
	}

	/**
	 * @param string $emName
	 * @return \Doctrine\ORM\EntityManager
	 * @throws \Nette\InvalidStateException
	 */
	protected function createEntityManager($emName)
	{
		if (!$this->currentTest instanceof \Lohini\Testing\OrmTestCase) {
			throw new \Nette\InvalidStateException("Your test case must be descendant of Lohini\\Testing\\OrmTestCase to be able to use Doctrine.");
			}

		// create manager
		$service=$this->entityManagers[$emName];
		$em=$this->container->getService($service);

		// configure entities, schema, proxies
		$this->configurator->configureManager($em);

		// load fixtures
		$fixtureLoader=new DataFixturesLoader(
			$this->container->getService($service.'.dataFixtures.loader'),
			$this->container->getService($service.'.dataFixtures.executor')
			);
		$fixtureLoader->loadFixtures($this->currentTest);

		// return
		return $em;
	}

	/**
	 * @return \Doctrine\ORM\EntityManager[]
	 */
	public function getEntityManagers()
	{
		$ems=array();
		foreach ($this->entityManagers as $name => $service) {
			if (!$this->container->isCreated($service)) {
				// handle all necessary stuff
				$ems[$name]=$this->createEntityManager($name);
				continue;
				}

			$ems[$name]=$this->container->getService($service);
			}

		return $ems;
	}

	/**
	 * @param string $name
	 * @return \Doctrine\ORM\EntityManager
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getEntityManager($name=NULL)
	{
		if ($name===NULL) {
			$name=$this->defaultEntityManager;
			}

		if (!isset($this->entityManagers[$name])) {
			throw new \Nette\InvalidArgumentException("Doctrine EntityManager named '$name' does not exist.");
			}

		$service=$this->entityManagers[$name];
		if (!$this->container->isCreated($service)) {
			// handle all necessary stuff
			return $this->createEntityManager($name);
			}

		return $this->container->getService($service);
	}

	/**
	 * Gets the EntityRepository for an entity.
	 *
	 * @param string $entityName The name of the entity.
	 * @param string $entityManagerName The entity manager name (NULL for the default one)
	 * @return \Doctrine\ORM\EntityRepository
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getRepository($entityName, $entityManagerName=NULL)
	{
		if (!class_exists($entityName= is_object($entityName)? get_class($entityName) : $entityName)) {
			throw new \Nette\InvalidArgumentException("Expected entity name, '$entityName' given");
			}

		if (isset($this->daoMocks[$entityManagerName][$lEntityName=strtolower($entityName)])) {
			return $this->daoMocks[$entityManagerName][$lEntityName];
			}

		return parent::getRepository($entityName, $entityManagerName);
	}

	/**
	 * @param string $entityName
	 * @param Dao $dao
	 * @param string $entityManagerName
	 * @return Dao
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setRepository($entityName, Dao $dao, $entityManagerName=NULL)
	{
		if (!class_exists($entityName= is_object($entityName)? get_class($entityName) : $entityName)) {
			throw new \Nette\InvalidArgumentException("Expected entity name, '$entityName' given");
			}

		return $this->daoMocks[$entityManagerName][strtolower($entityName)]=$dao;
	}

	/**
	 * Gets the Dao for an entity.
	 *
	 * @param string $entityName The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 * @return Dao
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getDao($entityName, $entityManagerName=NULL)
	{
		if (!class_exists($entityName= is_object($entityName)? get_class($entityName) : $entityName)) {
			throw new \Nette\InvalidArgumentException("Expected entity name, '$entityName' given");
			}

		if (isset($this->daoMocks[$entityManagerName][$lEntityName=strtolower($entityName)])) {
			return $this->daoMocks[$entityManagerName][$lEntityName];
			}

		return parent::getDao($entityName, $entityManagerName);
	}

	/**
	 * @param string $entityName
	 * @param Dao $dao
	 * @param string $entityManagerName
	 * @return Dao
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setDao($entityName, Dao $dao, $entityManagerName=NULL)
	{
		if (!class_exists($entityName= is_object($entityName)? get_class($entityName) : $entityName)) {
			throw new \Nette\InvalidArgumentException("Expected entity name, '$entityName' given");
			}

		return $this->daoMocks[$entityManagerName][strtolower($entityName)]=$dao;
	}

	/**
	 * Gets the Dao for an entity.
	 *
	 * @param string $entityName The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 * @return \Lohini\Database\Doctrine\Mapping\ClassMetadata
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getClassMetadata($entityName, $entityManagerName=NULL)
	{
		if (!class_exists($entityName= is_object($entityName)? get_class($entityName) : $entityName)) {
			throw new \Nette\InvalidArgumentException("Expected entity name, '$entityName' given");
			}

		if (isset($this->metaMocks[$entityManagerName][$lEntityName=strtolower($entityName)])) {
			return $this->metaMocks[$entityManagerName][$lEntityName];
			}

		return $this->getEntityManager($entityManagerName)->getClassMetadata($entityName);
	}

	/**
	 * @param string $entityName
	 * @param \Lohini\Database\Doctrine\Mapping\ClassMetadata $meta
	 * @param string $entityManagerName
	 * @return \Lohini\Database\Doctrine\Mapping\ClassMetadata
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setClassMetadata($entityName, \Lohini\Database\Doctrine\Mapping\ClassMetadata $meta, $entityManagerName=NULL)
	{
		if (!class_exists($entityName= is_object($entityName)? get_class($entityName) : $entityName)) {
			throw new \Nette\InvalidArgumentException("Expected entity name, '$entityName' given");
			}

		return $this->metaMocks[$entityManagerName][strtolower($entityName)]=$meta;
	}
}
