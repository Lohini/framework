<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine\Mapping;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\Common\Collections\ArrayCollection,
	Doctrine\ORM\Mapping as ORM;

/**
 */
class ValuesMapperTest
extends \Lohini\Testing\OrmTestCase
{
	/** @var \Lohini\Database\Doctrine\Mapping\ValuesMapper */
	private $mapper;


	public function setUp()
	{
		$this->createOrmSandbox(array(
			__NAMESPACE__ . '\RootEntity',
			__NAMESPACE__ . '\RelatedEntity',
			));

		$this->mapper=new \Lohini\Database\Doctrine\Mapping\ValuesMapper($this->getMetadata(__NAMESPACE__ . '\RootEntity'), $this->getEntityManager());
	}

	/**
	 * @return array
	 */
	public function dataValues()
	{
		return array(
			'id' => NULL,
			'name' => 'Lipsum',
			'daddy' => array(
				'id' => NULL,
				'name' => 'Holly',
				'daddy' => NULL,
				'buddies' => array()
				),
			'children' => array(
				array('id' => NULL, 'name' => 'Rimmer', 'daddy' => NULL, 'buddies' => array()),
				array('id' => NULL, 'name' => 'Lister', 'daddy' => NULL, 'buddies' => array()),
				),
			'buddies' => array(
				array('id' => NULL, 'name' => 'Foo', 'daddy' => NULL, 'buddies' => array()),
				array('id' => NULL, 'name' => 'Bar', 'daddy' => NULL, 'buddies' => array()),
				array('id' => NULL, 'name' => 'Baz', 'daddy' => NULL, 'buddies' => array()),
				array('id' => NULL, 'name' => 'The Chosen One', 'daddy' => array(
					'id' => NULL,
					'name' => 'Whore',
					'daddy' => NULL,
					'children' => array(),
					'buddies' => array()
					), 'buddies' => array())
				)
			);
	}

	/**
	 * @return \Lohini\Tests\Doctrine\Mapping\RootEntity
	 */
	public function testLoadValues()
	{
		$input=new RootEntity;
		$entity=$this->mapper->load($input, $this->dataValues());

		$this->assertSame($input, $entity);
		$this->assertEquals('Lipsum', $entity->name);

		// daddy
		$this->assertInstanceOf(__NAMESPACE__ . '\RelatedEntity', $daddy=$entity->daddy);
		$this->assertEquals('Holly', $daddy->name);

		// children
		$this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $entity->children);
		$this->assertCount(2, $entity->children);
		$children=$entity->children->toArray();
		$this->assertEquals('Rimmer', array_shift($children)->name);
		$this->assertEquals('Lister', array_shift($children)->name);

		// buddies
		$this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $entity->buddies);
		$this->assertCount(4, $entity->buddies);
		$buddies=$entity->buddies->toArray();
		$this->assertEquals('Foo', array_shift($buddies)->name);
		$this->assertEquals('Bar', array_shift($buddies)->name);
		$this->assertEquals('Baz', array_shift($buddies)->name);

		// chosen one
		$chosen=array_shift($buddies);
		$this->assertEquals('The Chosen One', $chosen->name);
		$this->assertInstanceOf(__NAMESPACE__ . '\RootEntity', $whore=$chosen->daddy);
		$this->assertEquals('Whore', $whore->name);

		return $entity;
	}

	/**
	 * @depends testLoadValues
	 *
	 * @param object $entity
	 */
	public function testSaveValues($entity)
	{
		$this->assertEquals($this->dataValues(), $this->mapper->save($entity));
	}
}


/**
 * @ORM\MappedSuperclass()
 */
class SharedFieldsEntity
extends \Nette\Object
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	public $id;
}


/**
 * @ORM\Entity()
 */
class RootEntity
extends SharedFieldsEntity
{
	/**
	 * @ORM\Column(type="string")
	 */
	public $name;
	/**
	 * @ORM\ManyToOne(targetEntity="RelatedEntity")
	 */
	public $daddy;
	/**
	 * @ORM\OneToMany(targetEntity="RelatedEntity", mappedBy="daddy")
	 */
	public $children;
	/**
	 * @ORM\ManyToMany(targetEntity="RelatedEntity", inversedBy="buddies")
	 */
	public $buddies;


	/**
	 */
	public function __construct()
	{
		$this->children=new ArrayCollection;
		$this->buddies=new ArrayCollection;
	}
}


/**
 * @ORM\Entity()
 */
class RelatedEntity
extends SharedFieldsEntity
{
	/**
	 * @ORM\Column(type="string")
	 */
	public $name;
	/**
	 * @ORM\ManyToOne(targetEntity="RootEntity", inversedBy="children")
	 */
	public $daddy;
	/**
	 * @ORM\ManyToMany(targetEntity="RootEntity", mappedBy="buddies")
	 */
	public $buddies;


	/**
	 */
	public function __construct()
	{
		$this->buddies=new ArrayCollection;
	}
}