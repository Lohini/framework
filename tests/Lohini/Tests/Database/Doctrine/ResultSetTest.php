<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM,
	Lohini\Database\Doctrine\ResultSet;

/**
 */
class ResultSetTest
extends \Lohini\Testing\OrmTestCase
{
	/**
	 * @expectedException \Nette\InvalidArgumentException
	 */
	public function testRequiresQueryObjectException()
	{
		new ResultSet((object)NULL);
	}

	public function testApplyPaging()
	{
		$query=new ORM\Query($this->getEntityManager());

		$result=new ResultSet($query);
		$result->applyPaging(20, 10);

		$this->assertEquals(20, $query->getFirstResult());
		$this->assertEquals(10, $query->getMaxResults());
	}

	public function testApplyPaginator()
	{
		$query=new ORM\Query($this->getEntityManager());
		$paginator=new \Nette\Utils\Paginator;
		$paginator->itemsPerPage=10;
		$paginator->itemCount=1000;
		$paginator->page=4;

		$result=new ResultSet($query);
		$result->applyPaginator($paginator);

		$this->assertEquals(30, $query->getFirstResult());
		$this->assertEquals(10, $query->getMaxResults());
	}

	public function testSorting()
	{
		$query=new ORM\Query($this->getEntityManager());
		$query->setDQL('SELECT * FROM Article');

		$result=new ResultSet($query);
		$result->applySorting(array('created DESC', 'title'));
		$result->applySorting('created', 'title ASC');

		$this->assertEquals('SELECT * FROM Article ORDER BY created DESC, title ASC, created ASC, title ASC', $query->getDQL());
	}

	/**
	 * @return \Doctrine\ORM\Query
	 */
	public function dataDummiesQuery()
	{
		$this->createOrmSandbox(array($entity=__NAMESPACE__.'\Dummy'));

		$dummies=array(
			new Dummy('Martucci, o tohle jsi přišel!'),
			new Dummy('Hříšná sběratelka'),
			new Dummy('mužského sémě'),
			new Dummy('Adéla Taş nahoře bez'),
			);

		$dao=$this->getDao($entity);
		$dao->save($dummies);
		$this->getEntityManager()->clear();

		return $dao->createQuery("SELECT d FROM $entity d ORDER BY d.id ASC");
	}

	public function testFetching()
	{
		$result=new ResultSet($this->dataDummiesQuery());
		$result->applyPaging(2, 2);

		$dummies=iterator_to_array($result->getIterator());
		$this->assertCount(2, $dummies);

		$dao=$this->getDao($dummies[0]);
		$this->assertSame($dao->find(3), $dummies[0]);
		$this->assertSame($dao->find(4), $dummies[1]);
	}

	public function testCounting()
	{
		$result=new ResultSet($this->dataDummiesQuery());
		$result->applyPaging(2, 2);

		$this->assertFalse($result->isEmpty());
		$this->assertEquals(4, $result->getTotalCount());
	}

	public function testIsEmpty()
	{
		$result=new ResultSet($this->dataDummiesQuery());
		$result->applyPaging(4, 2);

		$this->assertTrue($result->isEmpty());
	}
}


/**
 * @\Doctrine\ORM\Mapping\Entity()
 */
class Dummy
extends \Lohini\Database\Doctrine\Entities\IdentifiedEntity
{
	/**
	 * @\Doctrine\ORM\Mapping\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	public $name;


	/**
	 * @param string $name
	 */
	public function __construct($name=NULL)
	{
		$this->name=$name;
	}
}
