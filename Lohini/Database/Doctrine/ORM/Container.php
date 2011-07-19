<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\ORM;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Patrik Votoček
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Doctrine\ORM\Mapping,
	Doctrine\Common\Annotations;

/**
 * @property-read \Lohini\DI\Container $context
 * @property-read \Lohini\Database\Doctrine\ORM\Diagnostics\Panel $logger
 * @property-read \Doctrine\ORM\Configuration $configurator
 * @property-read \Doctrine\Common\Annotations\AnnotationReader $annotationReader
 * @property-read \Doctrine\ORM\Mapping\Driver\AnnotationDriver $annotationDriver
 * @property-read \Doctrine\DBAL\Event\Listeners\MysqlSessionInit $mysqlSessionInitListener
 * @property-read \Doctrine\Common\EventManager $eventManager
 * @property-read \Doctrine\ORM\EntityManager $entityManager
 */
class Container
extends \Lohini\Database\Doctrine\BaseContainer
{
	/** @var array */
	protected $services=array();
	/** @var string */
	protected $defaultServiceClass='Lohini\Database\Doctrine\ORM\BaseService';


	/** @var array */
	public $params=array(
		'host' => 'localhost',
		'charset' => 'utf8',
		'driver' => 'pdo_mysql',
		'entityDirs' => array('%appDir%', '%lohiniDir%'),
		'proxiesDir' => '%tempDir%/proxies',
		'proxyNamespace' => 'Lohini\Domain\Proxies',
		'listeners' => array(),
		);
	/** @var array */
	private static $types=array(
		'callback' => 'Lohini\Database\Doctrine\ORM\Types\Callback',
		'password' => 'Lohini\Database\Doctrine\ORM\Types\Password'
		);


	/**
	 * Registers doctrine types
	 * @param \Nette\DI\Container
	 * @param array $params
	 */
	public function __construct(\Nette\DI\Container $context, $params=array())
	{
		parent::__construct($context, $params);

		foreach (self::$types as $name => $className) {
			if (!Type::hasType($name)) {
				Type::addType($name, $className);
				}
			}
	}

	/**
	 * @return \Lohini\Database\Doctrine\ORM\Diagnostics\Panel
	 */
	protected function createServiceLogger()
	{
		return \Lohini\Database\Doctrine\ORM\Diagnostics\Panel::register();
	}

	/**
	 * @return \Doctrine\ORM\Mapping\Driver\AnnotationDriver
	 */
	protected function createServiceAnnotationReader()
	{
		// Dis Like!!!
		Annotations\AnnotationRegistry::registerFile(
			$this->context->params['libsDir'].'/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
			);

		Annotations\AnnotationReader::addGlobalIgnoredName('service');

		$reader=new Annotations\AnnotationReader;
		$reader->setDefaultAnnotationNamespace('\Doctrine\ORM\Mapping\\');
//		$reader->setAnnotationNamespaceAlias('Lohini\Database\Doctrine\ORM\Mapping\\', 'Lohini');

		$reader->setIgnoreNotImportedAnnotations(TRUE);
		$reader->setEnableParsePhpImports(FALSE);

		return new Annotations\CachedReader(
				new Annotations\IndexedReader($reader),
				$this->hasService('annotationCache')? $this->annotationCache : $this->cache
				);
	}

	 /**
	  * @return \Lohini\Database\Doctrine\ORM\Mapping\Driver\AnnotationDriver
	  */
	protected function createServiceAnnotationDriver()
	{
		return new Mapping\Driver\AnnotationDriver(
				$this->annotationReader,
				$this->params['entityDirs']
				);
	}

	/**
	 * @return \Doctrine\ORM\Configuration
	 */
	protected function createServiceConfiguration()
	{
		$config=new \Doctrine\ORM\Configuration;

		// Cache
		$config->setMetadataCacheImpl($this->hasService('metadataCache')? $this->metadataCache : $this->cache);
		$config->setQueryCacheImpl($this->hasService('queryCache')? $this->queryCache : $this->cache);

		// Metadata
		$config->setClassMetadataFactoryName('Lohini\Database\Doctrine\ORM\Mapping\ClassMetadataFactory');
		$config->setMetadataDriverImpl($this->annotationDriver);

		// Proxies
		$config->setProxyDir($this->params['proxiesDir']);
		$config->setProxyNamespace($this->getParam('proxyNamespace', 'Lohini\Domain\Proxies'));
		if ($this->context->params['productionMode']) {
			$config->setAutoGenerateProxyClasses(FALSE);
			}
		else {
			$config->setAutoGenerateProxyClasses(TRUE);
			$config->setSQLLogger($this->logger);
			}
		$config->addEntityNamespace('BIE', 'Lohini\Database\Models\Entities');
		return $config;
	}

	/**
	 * @return \Doctrine\DBAL\Event\Listeners\MysqlSessionInit
	 */
	protected function createServiceMysqlSessionInitListener()
	{
		return new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit($this->params['charset']);
	}

	/**
	 * @return \Doctrine\Common\EventManager
	 */
	protected function createServiceEventManager()
	{
		$evm=new \Doctrine\Common\EventManager;
		foreach ($this->params['listeners'] as $listener) {
			$evm->addEventSubscriber($this->getService($listener));
			}

		$evm->addEventSubscriber(new Mapping\DiscriminatorMapDiscoveryListener($this->annotationReader));
		$evm->addEventSubscriber(new Mapping\EntityDefaultsListener());

		if (isset($this->params['prefix'])) {
			$evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, new Mapping\TablePrefix($this->params['prefix']));
			}
		return $evm;
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function createServiceEntityManager()
	{
		if (key_exists('driver', $this->params) && $this->params['driver']=='pdo_mysql' && key_exists('charset', $this->params)) {
			$this->eventManager->addEventSubscriber($this->mysqlSessionInitListener);
			}

		$this->freeze();
		return \Doctrine\ORM\EntityManager::create($this->params, $this->configuration, $this->eventManager);
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}

	/**
	 * @param string $entityName
	 * @return \Lohini\Database\Doctrine\ORM\EntityRepository
	 */
	public function getRepository($entityName)
	{
		return $this->getEntityManager()->getRepository($entityName);
	}

	/**
	 * @param string $className
	 * @return bool
	 */
	public function isManaging($className)
	{
		try {
			$this->getEntityManager()->getClassMetadata($className);
			return TRUE;
			}
		catch (\Doctrine\ORM\Mapping\MappingException $e) {
			return FALSE;
			}
	}

	/**
	 * @param string
	 * @return \Lohini\Database\Doctrine\ORM\BaseService
	 * @throws \Nette\InvalidArgumentException
	 * @throws \Nette\InvalidStateException
	 */
	public function getModelService($entityClass)
	{
		if (isset($this->services[$entityClass])) {
			return $this->services[$entityClass];
			}

		if (!class_exists($entityClass)) {
			throw new \Nette\InvalidArgumentException("Class '$entityClass' does not exist'");
			}
		elseif (!\Nette\Reflection\ClassType::from($entityClass)->implementsInterface('Lohini\Database\Models\IEntity')) {
			throw new \Nette\InvalidArgumentException(
					"Entity '$entityClass' isn't valid entity (must implements Lohini\\Database\\Models\\IEntity)"
					);
			}

		$class=$this->defaultServiceClass;
		$em=$this->getEntityManager();
		$metadata=$em->getClassMetadata($entityClass);
		if (!$metadata) {
			throw new \Nette\InvalidStateException("Entity metadata '$entityClass' not found");
			}
		elseif (!class_exists($class=$metadata->serviceClassName)) {
			throw new \Nette\InvalidStateException("Service class '$class' does not exist");
			}

		return $this->services[$entityClass]=new $class($this, $entityClass);
	}
}
