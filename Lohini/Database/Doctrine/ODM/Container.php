<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\ODM;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ODM\CouchDB\DocumentManager,
	Doctrine\Common\Annotations;

/**
 *
 * @property-read Lohini\DI\Container $context
 * @property-read DocumentManager $documentManager
 * @property-read Doctrine\CouchDB\HTTP\SocketClient $httpClient
 * @property-read Doctrine\CouchDB\CouchDBClient $couchClient
 * @property-read Lohini\Database\Doctrine\Annotations\CachedReader $annotationReader
 * @property-read Doctrine\ODM\CouchDB\Mapping\Driver\AnnotationDriver $annotationDriver
 * @property-read Doctrine\ODM\CouchDB\Configuration $configuration
 */
class Container
extends \Lohini\Database\Doctrine\BaseContainer
{
	/** @var array */
	public $params=array(
			'documentDirs' => array('%appDir%', '%lohiniDir%'),
			'listeners' => array(),
		);


	/**
	 * @return \Doctrine\CouchDB\HTTP\SocketClient
	 */
	protected function createServiceHttpClient()
	{
		return new \Doctrine\CouchDB\HTTP\SocketClient();
	}

	/**
	 * @return octrine\Common\Annotations\AnnotationReader
	 */
	protected function createServiceAnnotationReader()
	{
		Annotations\AnnotationRegistry::registerFile(LIBS_DIR.'/Doctrine/ODM/CouchDB/Mapping/Driver/DoctrineAnnotations.php');

		$reader=new Annotations\AnnotationReader();
		$reader->setDefaultAnnotationNamespace('Doctrine\ODM\CouchDB\Mapping\\');

		return new \Lohini\Database\Doctrine\Annotations\CachedReader(
				new Annotations\IndexedReader($reader),
				$this->hasService('annotationCache')? $this->annotationCache : $this->cache
				);
	}

	/**
	 * @return \Mapping\Driver\AnnotationDriver
	 */
	protected function createServiceAnnotationDriver()
	{
		return new \Lohini\Database\Doctrine\ODM\Mapping\Driver\AnnotationDriver(
				$this->annotationReader,
				$this->params['documentDirs']
				);
	}

	/**
	 * @return \Doctrine\CouchDB\CouchDBClient
	 */
	protected function createServiceCouchClient()
	{
		return new \Doctrine\CouchDB\CouchDBClient(
				$this->httpClient,
				$this->params['database']
				);
	}

	/**
	 * @return \Doctrine\ODM\CouchDB\Configuration
	 */
	protected function createServiceConfiguration()
	{
		$config=new \Doctrine\ODM\CouchDB\Configuration();

		$config->setMetadataDriverImpl($this->annotationDriver);
		$config->setLuceneHandlerName('_fti');

		$config->setProxyDir($this->params['proxiesDir']);
		$config->setProxyNamespace($this->getParam('proxyNamespace', 'Lohini\Domain\Proxies'));

		return $config;
	}

	/**
	 * @return \Doctrine\ODM\CouchDB\DocumentManager
	 */
	protected function createServiceDocumentManager()
	{
		return DocumentManager::create($this->couchClient, $this->configuration);
	}

	/**
	 * @return \Doctrine\ODM\CouchDB\DocumentManager
	 */
	public function getDocumentManager()
	{
		return $this->documentManager;
	}

	/**
	 * @param string $documentName
	 * @return \Doctrine\ODM\CouchDB\DocumentRepository
	 */
	public function getRepository($documentName)
	{
		return $this->getDocumentManager()->getRepository($documentName);
	}

	/**
	 * @param string $className
	 * @return bool
	 */
	public function isManaging($className)
	{
		return $this->getDocumentManager()->getMetadataFactory()->hasMetadataFor($className);
	}
}
