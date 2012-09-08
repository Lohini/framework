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

use Doctrine\Common\Collections\ArrayCollection,
	Lohini\Database\Doctrine\Forms\CollectionContainer,
	Lohini\Database\Doctrine\Forms\Form;

/**
 */
class CollectionContainerTest
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
	 * @param \Lohini\Database\Doctrine\Forms\CollectionContainer $container
	 * @param \Lohini\Database\Doctrine\Forms\EntityMapper $mapper
	 * @return \Lohini\Database\Doctrine\Forms\Form|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function attachContainer(CollectionContainer $container, \Lohini\Database\Doctrine\Forms\EntityMapper $mapper=NULL)
	{
		$form=$this->getMock('Lohini\Database\Doctrine\Forms\Form', array('getMapper'), array($this->getDoctrine()));
		$form->expects($this->any())
			->method('getMapper')
			->will($this->returnValue($mapper ? : $this->mockMapper()));

		$container->setParent($form, 'form');
		return $form;
	}

	/**
	 * @param \Lohini\Database\Doctrine\Forms\Form $form
	 * @return \Nette\Application\UI\Presenter|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function attachForm(Form $form)
	{
		$presenter=$this->getMock('Nette\Application\UI\Presenter', array(), array($this->getContext()));
		$form->setParent($presenter, 'form');
		return $presenter;
	}

	/**
	 * @param array $methods
	 * @return \Lohini\Database\Doctrine\Forms\EntityMapper|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockMapper($methods=array())
	{
		return $this->getMock('Lohini\Database\Doctrine\Forms\EntityMapper', (array)$methods, array($this->getDoctrine()));
	}

	public function testContainerProvidesCollection()
	{
		$coll=new ArrayCollection;
		$container=new CollectionContainer($coll, function() {});

		$this->assertSame($coll, $container->getCollection());
	}

	public function testContainerCreatesChildrenAndAttachesEntity()
	{
		$entity=new Fixtures\RootEntity;
		$entity->children[]= $rel= new Fixtures\RelatedEntity;

		$mapper=$this->mockMapper('assign');
		$form=new Form($this->getDoctrine(), NULL, $mapper);
		$form['coll']=$container=new CollectionContainer($entity->children, function() { }, $mapper);

		$mapper->expects($this->once())
			->method('assign')
			->with($this->equalTo($rel), $this->isInstanceOf('Lohini\Database\Doctrine\Forms\EntityContainer'));

		$this->attachForm($form);
	}

	/**
	 * @expectedException \Nette\InvalidStateException
	 */
	public function testContainerAttaching_InvalidParentException()
	{
		$container=new \Nette\Forms\Container;
		$container['name']=new CollectionContainer(new ArrayCollection, function() { });
	}
}
