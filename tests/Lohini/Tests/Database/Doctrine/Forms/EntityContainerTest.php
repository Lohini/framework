<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine\Forms;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Doctrine\Forms\EntityContainer;

/**
 */
class EntityContainerTest
extends \Lohini\Testing\OrmTestCase
{
	public function setUp()
	{
		$this->createOrmSandbox(array(
			__NAMESPACE__.'\Fixtures\RootEntity',
			__NAMESPACE__.'\Fixtures\RelatedEntity',
			));
	}

	/**
	 * @param EntityContainer $container
	 * @param \Lohini\Database\Doctrine\Forms\EntityMapper $mapper
	 * @return \Lohini\Database\Doctrine\Forms\Form|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function attachContainer(EntityContainer $container, \Lohini\Database\Doctrine\Forms\EntityMapper $mapper=NULL)
	{
		$form=$this->getMock('Lohini\Database\Doctrine\Forms\Form', array('getMapper'), array($this->getDoctrine()));
		$form->expects($this->any())
			->method('getMapper')
			->will($this->returnValue($mapper ? : $this->mockMapper()));

		$container->setParent($form, 'form');
		return $form;
	}

	/**
	 * @param array $methods
	 * @return \Lohini\Database\Doctrine\Forms\EntityMapper|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockMapper($methods=array())
	{
		return $this->getMock('Lohini\Database\Doctrine\Forms\EntityMapper', (array)$methods, array($this->getDoctrine()));
	}

	public function testContainerProvidesEntity()
	{
		$entity=new Fixtures\RootEntity('Podívejte se na neskutečně vyvinutou Australanku, které ženy nevěří, že má pravá prsa');
		$container=new EntityContainer($entity);

		$this->assertSame($entity, $container->getEntity());
	}

	public function testContainerAttachesEntity()
	{
		$entity=new Fixtures\RootEntity('Víme, čím Pavlína Němcová okouzluje filmové producenty');
		$container=new EntityContainer($entity);

		$mapper=$this->mockMapper('assign');
		$mapper->expects($this->once())
			->method('assign')
			->with($this->equalTo($entity), $this->equalTo($container));

		$this->attachContainer($container, $mapper);
	}

	/**
	 * @expectedException \Nette\InvalidStateException
	 */
	public function testContainerAttaching_InvalidParentException()
	{
		$container=new \Nette\Forms\Container;
		$container['name']=new EntityContainer(new \stdClass);
	}

	/**
	 * @return array
	 */
	public function dataItemControls()
	{
		return array(
			array('addSelect', 'Nette\Forms\Controls\SelectBox'),
			array('addCheckboxList', 'Lohini\Forms\Controls\CheckboxList'),
			array('addRadioList', 'Nette\Forms\Controls\RadioList'),
		);
	}

	/**
	 * @dataProvider dataItemControls
	 *
	 * @param string $method
	 * @param string $type
	 */
	public function testSelectBoxHasMapper($method, $type)
	{
		$entity=new Fixtures\RootEntity('Kevin Bacon (53) a jeho žena Kyra (46) se pochlubili neuvěřitelně vypracovanými těly');
		$container=new EntityContainer($entity);

		$this->attachContainer($container, $mapper=$this->mockMapper('setControlMapper'));
		$mapper->expects($this->once())
			->method('setControlMapper')
			->with($this->isInstanceOf($type), $this->equalTo('name'));

		$container->$method('children', 'Name')
			->setMapper('name');
	}
}
