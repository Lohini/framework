<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine\Schema;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Doctrine\Schema;

/**
 */
class SchemaToolTest
extends \Lohini\Testing\OrmTestCase
{
	/** @var \Lohini\Tests\Database\Doctrine\Schema\TestSubscriber */
	private $subscriber;
	/** @var \Lohini\Database\Doctrine\Schema\SchemaTool */
	private $schemaTool;


	public function setUp()
	{
		$this->createOrmSandbox(array(
			'Lohini\Database\Migrations\MigrationLog' // not important
			));

		$em=$this->getEntityManager();
		$evm=$em->getEventManager();

		// hack to platform, that supports table alters (Fuck you sqlite! Fuck you!)
		$conn=$em->getConnection();
		$platformRefl=\Nette\Reflection\ClassType::from($conn)->getProperty('_platform');
		$platformRefl->setAccessible(TRUE);

		$mysqlPlatform=new \Doctrine\DBAL\Platforms\MySqlPlatform;
		$mysqlPlatform->setEventManager($evm);
		$platformRefl->setValue($conn, $mysqlPlatform);

		// create schema tool & subscriber
		$this->schemaTool=new Schema\SchemaTool($em);
		$this->subscriber=new TestSubscriber;
		$evm->addEventSubscriber($this->subscriber);
	}

	/**
	 * @return array
	 */
	public function dataListeners()
	{
		return array(
			array(Schema\SchemaTool::onCreateSchemaSql, 'Lohini\Database\Doctrine\Schema\CreateSchemaSqlEventArgs', 'getCreateSchemaSql'),
			array(Schema\SchemaTool::onDropDatabaseSql, 'Lohini\Database\Doctrine\Schema\DropDatabaseSqlEventArgs', 'getDropDatabaseSql'),
			array(Schema\SchemaTool::onDropSchemaSql, 'Lohini\Database\Doctrine\Schema\DropSchemaSqlEventArgs', 'getDropSchemaSql'),
			array(Schema\SchemaTool::onUpdateSchemaSql, 'Lohini\Database\Doctrine\Schema\UpdateSchemaSqlEventArgs', 'getUpdateSchemaSql'),
		);
	}

	/**
	 * @dataProvider dataListeners
	 *
	 * @param string $eventType
	 * @param string $eventClass
	 * @param string $method
	 */
	public function testListener($eventType, $eventClass, $method)
	{
		$test=$this;
		$invoker= & $this->subscriber->invokers[$eventType];
		$invoker=function($eventArgs) use ($test, $eventClass) {
			/** @var \Lohini\Testing\OrmTestCase $test */
			$test->assertInstanceOf($eventClass, $eventArgs);
			/** @var \Lohini\Database\Doctrine\Schema\UpdateSchemaSqlEventArgs $eventArgs */

			// modify sqls
			$eventArgs->addSqls(array('I WAS HERE, FANTOMAS;'));
			};

		$classes=$this->getEntityManager()->getMetadataFactory()->getAllMetadata();
		$sqls=$this->schemaTool->$method($classes);
		$this->assertContains('I WAS HERE, FANTOMAS;', $sqls);
	}
}


/**
 */
class TestSubscriber
extends \Nette\Object
implements \Doctrine\Common\EventSubscriber
{
	/**
	 * @var array|\Nette\Callback[]
	 */
	public $invokers=array();


	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Schema\SchemaTool::onCreateSchemaSql,
			Schema\SchemaTool::onDropDatabaseSql,
			Schema\SchemaTool::onDropSchemaSql,
			Schema\SchemaTool::onUpdateSchemaSql,
			);
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 * @throws \PHPUnit_Framework_AssertionFailedError
	 */
	public function __call($name, $args)
	{
		if (!isset($this->invokers[$name])) {
			throw new \PHPUnit_Framework_AssertionFailedError('Unexpected invocation '.get_called_class()."::$name().");
			}

		$invoker=callback($this->invokers[$name]);
		return $invoker->invokeArgs($args);
	}

	public function onCreateSchemaSql()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function onDropDatabaseSql()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function onDropSchemaSql()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function onUpdateSchemaSql()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}
}
