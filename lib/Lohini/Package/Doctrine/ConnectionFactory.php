<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Package\Doctrine;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Doctrine\Type;

/**
 * Connection
 */
class ConnectionFactory
extends \Nette\Object
{
	/** @var array */
	private $typesConfig=array();
	/** @var bool */
	private $initialized=FALSE;


	/**
	 * @param array $typesConfig
	 */
	public function __construct(array $typesConfig=NULL)
	{
		$this->typesConfig=(array)$typesConfig;
	}

	/**
	 * Create a connection by name.
	 *
	 * @param array $params
	 * @param \Doctrine\DBAL\Configuration $config
	 * @param \Doctrine\Common\EventManager $eventManager
	 * @param array $mappingTypes
	 * @return \Doctrine\DBAL\Connection
	 */
	public function createConnection(array $params, \Doctrine\DBAL\Configuration $config=NULL, \Doctrine\Common\EventManager $eventManager=NULL, array $mappingTypes=array())
	{
		if (!$this->initialized) {
			$this->initializeTypes();
			$this->initialized=TRUE;
			}

		/** @var \Doctrine\DBAL\Connection $connection */
		$connection=\Doctrine\DBAL\DriverManager::getConnection($params, $config, $eventManager);
		$platform=$connection->getDatabasePlatform();

		if (!empty($mappingTypes)) {
			foreach ($mappingTypes as $dbType => $doctrineType) {
				$platform->registerDoctrineTypeMapping($dbType, $doctrineType);
				}
			}

		if (!empty($this->typesConfig)) {
			foreach ($this->typesConfig as $type => $className) {
				$platform->markDoctrineTypeCommented(Type::getType($type));
				}
			}

		return $connection;
	}

	/**
	 * Registers Doctrine DBAL types
	 */
	private function initializeTypes()
	{
		foreach ($this->typesConfig as $type => $className) {
			if (Type::hasType($type)) {
				Type::overrideType($type, $className);
				}
			else {
				Type::addType($type, $className);
				}
			}
	}
}
