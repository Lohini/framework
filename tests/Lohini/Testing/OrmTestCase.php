<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
abstract class OrmTestCase
extends TestCase
{
	/** @var ORM\SandboxRegistry */
	private $ormSandbox;


	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	final protected function getEntityManager()
	{
		return $this->getDoctrine()->getEntityManager();
	}

	/**
	 * @return ORM\SandboxRegistry
	 */
	final protected function getDoctrine()
	{
		if ($this->ormSandbox===NULL) {
			$this->createOrmSandbox();
			}

		return $this->ormSandbox;
	}

	/**
	 * @param array $entities
	 * @throws \Nette\InvalidStateException
	 */
	final protected function createOrmSandbox(array $entities=NULL)
	{
		if ($this->ormSandbox!==NULL) {
			throw new \Nette\InvalidStateException('ORM Sandbox is already created for this test instance.');
			}

		$params=array(
			'wwwDir' => $this->getContext()->expand('%wwwDir%'),
			'appDir' => $this->getContext()->expand('%appDir%'),
			'tempDir' => $this->getContext()->expand('%tempDir%'),
			'container' => array('class' => 'ConsoleOrmContainer')
			);

		$config=new ORM\SandboxConfigurator($params);
		if (is_array($entities)) {
			$config->setEntities($entities);
			}

		$this->ormSandbox=$config->getRegistry();
		$this->ormSandbox->setCurrentTest($this);
		$this->ormSandbox->requireConfiguredManager();
	}

	/********************* EntityManager shortcuts *********************/
	/**
	 * @param string $entityName
	 * @return \Lohini\Database\Doctrine\Dao
	 */
	protected function getDao($entityName)
	{
		if (is_object($entityName)) {
			$entityName=get_class($entityName);
			}

		return $this->getEntityManager()->getRepository($entityName);
	}

	/**
	 * @param string $className
	 * @return \Lohini\Database\Doctrine\Mapping\ClassMetadata
	 */
	protected function getMetadata($className)
	{
		if (is_object($className)) {
			$className=get_class($className);
			}

		return $this->getEntityManager()->getClassMetadata($className);
	}

	/********************* Helpers *********************/
	/**
	 * @param object $entity
	 * @param array|int $identity
	 * @return object
	 */
	protected function forceEntityIdentity($entity, $identity)
	{
		if ($entity instanceof \PHPUnit_Framework_MockObject_MockObject) {
			$classParents=class_parents($entity);
			$class=$this->getMetadata(reset($classParents));
			}
		else {
			$class=$this->getMetadata($entity);
			}


		if (!is_array($identity)) {
			$identity=array(
				$class->getSingleIdentifierFieldName() => $identity
				);
			}

		$class->setIdentifierValues($entity, $identity);

		return $entity;
	}

	/********************* Asserts *********************/
	/**
	 * @param int $expectedCount
	 * @param string|object $entityName
	 * @param string $message
	 */
	public function assertEntityCount($expectedCount, $entityName, $message='')
	{
		$haystack=$this->getDao($entityName)
			->createQueryBuilder('e')
			->select('COUNT(e.id)')
			->getQuery()->getSingleScalarResult();

		$this->assertEquals($expectedCount, $haystack);
	}

	/**
	 * @param int $values
	 * @param string|object $entityName
	 * @param string $message
	 */
	public function assertEntityValues($entityName, array $values, $id=NULL, $message="")
	{
		$entityName= is_object($entityName)? get_class($entityName) : $entityName;

		if ($id===NULL) {
			$result=$this->getDao($entityName)->findBy($values);

			$this->assertCount(1, $result);
			$entity=current($result);
			}
		else {
			$entity=$this->getDao($entityName)->find($id);
			$this->assertInstanceOf($entityName, $entity);
			}

		$meta=$this->getMetadata($entityName);
		foreach ($values as $property => $value) {
			$actualValue=$meta->getFieldValue($entity, $property);
			if ($actualValue instanceof \Doctrine\Common\Collections\Collection) {
				$actualValue=$actualValue->toArray();
				}
			elseif (is_object($actualValue)) {
				try {
					$relationMeta=$this->getMetadata($actualValue);
					$actualValue=$relationMeta->getIdentifierValues($actualValue);
					if (count($actualValue)==1) {
						$actualValue=current($actualValue);
						}
					}
				catch (\Exception $e) {
					}
				}

			$this->assertSame($value, $actualValue, "Property '$property' of '$entityName' equals given value.");
			}
	}

	/********************* Database DataSets *********************/
	/**
	 * @param string $file
	 * @return \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
	 * @throws \Nette\NotImplementedException
	 */
	protected function createDataSet($file=NULL)
	{
		$extension= $file? pathinfo($file, PATHINFO_EXTENSION) : NULL;
		if ($extension==='neon') {
			return $this->createNeonDataSet($file);
			}
		if ($file!==NULL) {
			throw new \Nette\NotImplementedException("Handling of file type $extension is not implemented yet.");
			}

		$resolver=new Tools\DataSetFilenameResolver($this);
		return $this->createDataSet($resolver->resolve());
	}

	/**
	 * @param string $neonFile
	 * @return array
	 */
	protected function createNeonDataSet($neonFile)
	{
		return \Nette\Utils\Neon::decode(file_get_contents($neonFile));
	}
}
