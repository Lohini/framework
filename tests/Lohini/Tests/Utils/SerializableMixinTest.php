<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Utils;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Utils\SerializableMixin;

/**
 */
class SerializableMixinTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @return SexyEntity
	 */
	public function data()
	{
		$entity=new SexyEntity(1, 'sexy', 'very', 'bzzzz');
		$entity->foo='bar';
		$entity->bar='yes please';
		$entity->lorem='ipsum';
		return $entity;
	}

	public function testFunctionality()
	{
		$entity=$this->data();
		$serialized=serialize($entity);
		$this->assertInternalType('string', $serialized);
		$unserialized=unserialize($serialized);
		$this->assertEquals($entity, $unserialized);
	}
}


/**
 */
abstract class BaseEntity
extends \Nette\Object
{
	/** @var string */
	public $foo;
	/** @var int */
	private $id;
	/** @var string */
	private $name;


	/**
	 * @param int $id
	 * @param string $name
	 */
	public function __construct($id, $name)
	{
		$this->id=$id;
		$this->name=$name;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return isset($this->{$name})
			? $this->{$name}
			: parent::__get($name);
	}
}


/**
 */
class ConcreteEntity
extends BaseEntity
{
	/** @var string */
	public $bar;
	/** @var string */
	private $name;
	/** @var string */
	protected $baz;


	/**
	 * @param int $id
	 * @param string $name
	 * @param string $concreteName
	 * @param string $baz
	 */
	public function __construct($id, $name, $concreteName, $baz)
	{
		parent::__construct($id, $name);
		$this->name=$concreteName;
		$this->baz=$baz;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return isset($this->{$name})
			? $this->{$name}
			: parent::__get($name);
	}
}


/**
 */
class SexyEntity
extends ConcreteEntity
implements \Serializable
{
	/** @var string */
	public $lorem;


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return isset($this->{$name})
			? $this->{$name}
			: parent::__get($name);
	}

	/**
	 * @return string
	 */
	public function serialize()
	{
		return SerializableMixin::serialize($this);
	}

	/**
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		SerializableMixin::unserialize($this, $serialized);
	}
}
