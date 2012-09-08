<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine\Entities;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\Common\Collections\ArrayCollection;

/**
 */
class BaseEntityTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @expectedException Nette\MemberAccessException
	 * @expectedExceptionMessage Cannot unset the property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$one.
	 */
	public function testUnsetPrivateException()
	{
		$entity=new ConcreteEntity;
		unset($entity->one);
	}

	/**
	 * @expectedException Nette\MemberAccessException
	 * @expectedExceptionMessage Cannot unset the property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$two.
	 */
	public function testUnsetProtectedException()
	{
		$entity=new ConcreteEntity;
		unset($entity->two);
	}

	public function testIsset()
	{
		$entity=new ConcreteEntity;
		$this->assertFalse(isset($entity->one));
		$this->assertTrue(isset($entity->two));
		$this->assertTrue(isset($entity->three));
		$this->assertFalse(isset($entity->ones));
		$this->assertTrue(isset($entity->twos));
		$this->assertTrue(isset($entity->proxies));
		$this->assertTrue(isset($entity->threes));
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Cannot read an undeclared property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$one.
	 */
	public function testGetPrivateException()
	{
		$entity=new ConcreteEntity;
		$entity->one;
	}

	public function testGetProtected()
	{
		$entity=new ConcreteEntity;
		$this->assertEquals(2, $entity->two->id);
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Cannot read an undeclared property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$ones.
	 */
	public function testGetPrivateCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->ones;
	}

	public function testGetProtectedCollection()
	{
		$entity=new ConcreteEntity;
		$this->assertEquals($entity->twos, $entity->getTwos());
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Cannot write to an undeclared property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$one.
	 */
	public function testSetPrivateException()
	{
		$entity=new ConcreteEntity;
		$entity->one=1;
	}

	public function testSetProtected()
	{
		$entity=new ConcreteEntity;
		$entity->two=2;
		$this->assertEquals(2, $entity->two);
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Cannot write to an undeclared property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$ones.
	 */
	public function testSetPrivateCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->ones=1;
	}

	/**
	 * @expectedException \Lohini\UnexpectedValueException
	 * @expectedExceptionMessage Class property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$twos is an instance of Doctrine\Common\Collections\Collection. Use add<property>() and remove<property>() methods to manipulate it or declare your own.
	 */
	public function testSetProtectedCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->twos=1;
	}

	/**
	 * @expectedException \Lohini\UnexpectedValueException
	 * @expectedExceptionMessage Class property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$proxies is an instance of Doctrine\Common\Collections\Collection. Use add<property>() and remove<property>() methods to manipulate it or declare your own.
	 */
	public function testSetProtectedCollection2Exception()
	{
		$entity=new ConcreteEntity;
		$entity->proxies=1;
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::setOne().
	 */
	public function testCallSetterOnPrivateException()
	{
		$entity=new ConcreteEntity;
		$entity->setOne(1);
	}

	public function testCallSetterOnProtected()
	{
		$entity=new ConcreteEntity;
		$entity->setTwo(2);
		$this->assertEquals(2, $entity->two);
	}

	public function testValidSetterProvidesFluentInterface()
	{
		$entity=new ConcreteEntity;
		$this->assertSame($entity, $entity->setTwo(2));
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::setOnes().
	 */
	public function testCallSetterOnPrivateCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->setOnes(1);
	}

	/**
	 * @expectedException \Lohini\UnexpectedValueException
	 * @expectedExceptionMessage Class property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$twos is an instance of Doctrine\Common\Collections\Collection. Use add<property>() and remove<property>() methods to manipulate it or declare your own.
	 */
	public function testCallSetterOnProtectedCollection()
	{
		$entity=new ConcreteEntity;
		$entity->setTwos(2);
	}

	/**
	 * @expectedException \Lohini\UnexpectedValueException
	 * @expectedExceptionMessage Class property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$proxies is an instance of Doctrine\Common\Collections\Collection. Use add<property>() and remove<property>() methods to manipulate it or declare your own.
	 */
	public function testCallSetterOnProtected2Collection()
	{
		$entity=new ConcreteEntity;
		$entity->setProxies(3);
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::getOne().
	 */
	public function testCallGetterOnPrivateException()
	{
		$entity=new ConcreteEntity;
		$entity->getOne();
	}

	public function testCallGetterOnProtected()
	{
		$entity=new ConcreteEntity;
		$this->assertEquals(2, $entity->getTwo()->id);
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::getOnes().
	 */
	public function testCallGetterOnPrivateCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->getOnes();
	}

	public function testCallGetterOnProtectedCollection()
	{
		$entity=new ConcreteEntity;
		$this->assertEquals(array((object)array('id' => 2)), $entity->getTwos());
		$this->assertEquals(array((object)array('id' => 3)), $entity->getProxies());
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::thousand().
	 */
	public function testCallNonExistingMethodException()
	{
		$entity=new ConcreteEntity;
		$entity->thousand(1000);
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::addOne().
	 */
	public function testCallAddOnPrivateCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->addOne((object)array('id' => 1));
	}

	/**
	 * @expectedException \Lohini\UnexpectedValueException
	 * @expectedExceptionMessage Class property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$four is not an instance of Doctrine\Common\Collections\Collection.
	 */
	public function testCallAddOnNonCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->addFour((object)array('id' => 4));
	}

	public function testCallAddOnProtectedCollection()
	{
		$entity=new ConcreteEntity;
		$entity->addTwo($a=(object)array('id' => 2));
		$this->assertContains($a, $entity->getTwos());

		$entity->addProxy($b=(object)array('id' => 3));
		$this->assertContains($b, $entity->getProxies());
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::hasOne().
	 */
	public function testCallHasOnPrivateCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->hasOne((object)array('id' => 1));
	}

	/**
	 * @expectedException \Lohini\UnexpectedValueException
	 * @expectedExceptionMessage Class property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$four is not an instance of Doctrine\Common\Collections\Collection.
	 */
	public function testCallHasOnNonCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->hasFour((object)array('id' => 4));
	}

	public function testCallHasOnProtectedCollection()
	{
		$entity=new ConcreteEntity;
		$this->assertFalse($entity->hasTwo((object)array('id' => 2)));
		$this->assertFalse($entity->hasProxy((object)array('id' => 3)));

		$this->assertNotEmpty($twos=$entity->getTwos());
		$this->assertTrue($entity->hasTwo(reset($twos)));

		$this->assertNotEmpty($proxies=$entity->getProxies());
		$this->assertTrue($entity->hasProxy(reset($proxies)));
	}

	/**
	 * @expectedException \Lohini\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::removeOne().
	 */
	public function testCallRemoveOnPrivateCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->removeOne((object)array('id' => 1));
	}

	/**
	 * @expectedException \Lohini\UnexpectedValueException
	 * @expectedExceptionMessage Class property Lohini\Tests\Database\Doctrine\Entities\ConcreteEntity::$four is not an instance of Doctrine\Common\Collections\Collection.
	 */
	public function testCallRemoveOnNonCollectionException()
	{
		$entity=new ConcreteEntity;
		$entity->removeFour((object)array('id' => 4));
	}

	public function testCallRemoveOnProtectedCollection()
	{
		$entity=new ConcreteEntity;
		$this->assertNotEmpty($twos=$entity->getTwos());
		$entity->removeTwo(reset($twos));
		$this->assertEmpty($entity->getTwos());

		$this->assertNotEmpty($proxies=$entity->getProxies());
		$entity->removeProxy(reset($proxies));
		$this->assertEmpty($entity->getProxies());
	}

	public function testGetterHaveHigherPriority()
	{
		$entity=new ConcreteEntity;
		$this->assertEquals(4, $entity->something);
	}

	public function testSetterHaveHigherPriority()
	{
		$entity=new ConcreteEntity;
		$entity->something=4;
		$this->assertAttributeEquals(2, 'something', $entity);
	}
}



/**
 * @method setTwo()
 * @method addTwo()
 * @method getTwo()
 * @method removeTwo()
 * @method hasTwo()
 * @method getTwos()
 * @method addProxy()
 * @method hasProxy()
 * @method removeProxy()
 * @method getProxies()
 */
class ConcreteEntity
extends \Lohini\Database\Doctrine\Entities\BaseEntity
{
	/** @var array events */
	private $onSomething=array();
	/** @var object */
	private $one;
	/** @var object */
	protected $two;
	/** @var object */
	protected $four;
	/** @var object */
	public $three;
	/** @var ArrayCollection */
	private $ones;
	/** @var ArrayCollection */
	protected $twos;
	/** @var ArrayCollection */
	protected $proxies;
	/** @var ArrayCollection */
	public $threes;
	/** @var int */
	protected $something=2;


	/**
	 */
	public function __construct()
	{
		$this->one=(object)array('id' => 1);
		$this->two=(object)array('id' => 2);
		$this->three=(object)array('id' => 3);

		$this->ones=new ArrayCollection(array((object)array('id' => 1)));
		$this->twos=new ArrayCollection(array((object)array('id' => 2)));
		$this->proxies=new ArrayCollection(array((object)array('id' => 3)));
		$this->threes=new ArrayCollection(array((object)array('id' => 4)));
	}

	/**
	 * @param int $something
	 */
	public function setSomething($something)
	{
		$this->something=(int)ceil($something/2);
	}

	/**
	 * @return int
	 */
	public function getSomething()
	{
		return $this->something*2;
	}
}
