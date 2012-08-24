<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * References all Doctrine connections and entity managers in a given Container.
 */
class Registry
extends \Nette\Object
{
	/** @var \Nette\DI\Container */
	protected $container;
	/** @var array */
	protected $connections;
	/** @var array */
	protected $entityManagers;
	/** @var array */
	protected $auditManagers;
	/** @var string */
	protected $defaultConnection;
	/** @var string */
	protected $defaultEntityManager;


	/**
	 * @param \Nette\DI\Container $container
	 * @param array $connections
	 * @param array $entityManagers
	 * @param string $defaultConnection
	 * @param string $defaultEntityManager
	 * @param array $auditManagers
	 */
	public function __construct(\Nette\DI\Container $container, array $connections, array $entityManagers, $defaultConnection, $defaultEntityManager, array $auditManagers=array())
	{
		$this->container=$container;
		$this->connections=$connections;
		$this->entityManagers=$entityManagers;
		$this->auditManagers=$auditManagers;
		$this->defaultConnection=$defaultConnection;
		$this->defaultEntityManager=$defaultEntityManager;
	}

	/**
	 * Gets the default connection name.
	 *
	 * @return string The default connection name
	 */
	public function getDefaultConnectionName()
	{
		return $this->defaultConnection;
	}

	/**
	 * Gets the named connection.
	 *
	 * @param string $name The connection name (null for the default one)
	 * @return \Doctrine\DBAL\Connection
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getConnection($name=NULL)
	{
		if ($name===NULL) {
			$name=$this->defaultConnection;
			}

		if (!isset($this->connections[$name])) {
			throw new \Nette\InvalidArgumentException("Doctrine Connection named '$name' does not exist.");
			}

		return $this->container->getService($this->connections[$name]);
	}

	/**
	 * Gets an array of all registered connections
	 *
	 * @return \Doctrine\DBAL\Connection[] An array of Connection instances
	 */
	public function getConnections()
	{
		$connections=array();
		foreach ($this->connections as $name => $id) {
			$connections[$name]=$this->container->getService($id);
			}

		return $connections;
	}

	/**
	 * Gets all connection names.
	 *
	 * @return array An array of connection names
	 */
	public function getConnectionNames()
	{
		return $this->connections;
	}

	/**
	 * Gets the default entity manager name.
	 *
	 * @return string The default entity manager name
	 */
	public function getDefaultEntityManagerName()
	{
		return $this->defaultEntityManager;
	}

	/**
	 * Gets a named entity manager.
	 *
	 * @param string $name The entity manager name (null for the default one)
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

		return $this->container->getService($this->entityManagers[$name]);
	}

	/**
	 * Gets an array of all registered entity managers
	 *
	 * @return \Doctrine\ORM\EntityManager[] An array of EntityManager instances
	 */
	public function getEntityManagers()
	{
		$ems=array();
		foreach ($this->entityManagers as $name => $id) {
			$ems[$name]=$this->container->getService($id);
			}

		return $ems;
	}

	/**
	 * Resets a named entity manager.
	 *
	 * This method is useful when an entity manager has been closed
	 * because of a rollbacked transaction AND when you think that
	 * it makes sense to get a new one to replace the closed one.
	 *
	 * Be warned that you will get a brand new entity manager as
	 * the existing one is not useable anymore. This means that any
	 * other object with a dependency on this entity manager will
	 * hold an obsolete reference. You can inject the registry instead
	 * to avoid this problem.
	 *
	 * @param string $name The entity manager name (null for the default one)
	 * @throws \Nette\InvalidArgumentException
	 */
	public function resetEntityManager($name=NULL)
	{
		if ($name===NULL) {
			$name=$this->defaultEntityManager;
			}

		if (!isset($this->entityManagers[$name])) {
			throw new \Nette\InvalidArgumentException("Doctrine EntityManager named '$name' does not exist.");
			}

		// force the creation of a new entity manager
		// if the current one is closed
		$this->container->removeService($this->entityManagers[$name]);
	}

	/**
	 * Resolves a registered namespace alias to the full namespace.
	 *
	 * This method looks for the alias in all registered entity managers.
	 * @see Configuration::getEntityNamespace
	 *
	 * @param string $alias The alias
	 * @return string The full namespace
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function getEntityNamespace($alias)
	{
		foreach ($this->getEntityManagers() as $em) {
			try {
				return $em->getConfiguration()->getEntityNamespace($alias);
				}
			catch (\Doctrine\ORM\ORMException $e) { }
			}

		throw \Doctrine\ORM\ORMException::unknownEntityNamespace($alias);
	}

	/**
	 * Gets all connection names.
	 *
	 * @return array An array of connection names
	 */
	public function getEntityManagerNames()
	{
		return $this->entityManagers;
	}

	/**
	 * Gets the EntityRepository for an entity.
	 *
	 * @param string $entityName The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 * @throws \Nette\InvalidArgumentException
	 * @return \Doctrine\ORM\EntityRepository
	 */
	public function getRepository($entityName, $entityManagerName=NULL)
	{
		if (!class_exists($entityName= is_object($entityName)? get_class($entityName) : $entityName)) {
			throw new \Nette\InvalidArgumentException("Expected entity name, '$entityName' given");
			}

		return $this->getDao($entityName, $entityManagerName);
	}

	/**
	 * Gets the Dao for an entity.
	 *
	 * @param string $entityName The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 * @return \Lohini\Database\Doctrine\Dao
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getDao($entityName, $entityManagerName=NULL)
	{
		if (!class_exists($entityName= is_object($entityName)? get_class($entityName) : $entityName)) {
			throw new \Nette\InvalidArgumentException("Expected entity name, '$entityName' given");
			}

		return $this->getEntityManager($entityManagerName)->getRepository($entityName);
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

		return $this->getEntityManager($entityManagerName)->getClassMetadata($entityName);
	}

	/**
	 * @param string $name
	 * @throws \Nette\InvalidArgumentException
	 * @return \Lohini\Database\Doctrine\Audit\AuditManager
	 */
	public function getAuditManager($name=NULL)
	{
		if ($name===NULL) {
			$name=$this->defaultEntityManager;
			}

		if (!isset($this->auditManagers[$name])) {
			throw new \Nette\InvalidArgumentException("Doctrine AuditManager named '$name' does not exist.");
			}

		return $this->container->getService($this->auditManagers[$name]);
	}

	/**
	 * Returns Audit Reader for given entity.
	 *
	 * @param string $entityName The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 * @return \Lohini\Database\Doctrine\Audit\AuditReader
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getAuditReader($entityName, $entityManagerName=NULL)
	{
		if (!class_exists($entityName= is_object($entityName)? get_class($entityName) : $entityName)) {
			throw new \Nette\InvalidArgumentException("Expected entity name, '$entityName' given");
			}

		return $this->getAuditManager($entityManagerName)->getAuditReader($entityName);
	}

	/**
	 * Gets the entity manager associated with a given class.
	 *
	 * @param string $className A Doctrine Entity class name
	 * @return \Doctrine\ORM\EntityManager|NULL
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getEntityManagerForClass($className)
	{
		if (!class_exists($className= is_object($className)? get_class($className) : $className)) {
			throw new \Nette\InvalidArgumentException("Expected entity name, '$className' given");
			}

		$proxyClass=\Nette\Reflection\ClassType::from($className);
		$className=$proxyClass->getName();
		if ($proxyClass->implementsInterface('Doctrine\ORM\Proxy\Proxy')) {
			$className=$proxyClass->getParentClass()->getName();
			}

		foreach ($this->getEntityManagers() as $em) {
			if (!$em->getConfiguration()->getMetadataDriverImpl()->isTransient($className)) {
				return $em;
				}
			}

		return NULL;
	}
}
