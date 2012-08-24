<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine\Forms;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Doctrine\Forms\Form;

/**
 */
class FormTest
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
	 * @param Form $form
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

	public function testAttached_Load()
	{
		$mapper=$this->mockMapper(array('loadControlItems', 'load'));
		$form=new Form($this->getDoctrine(), NULL, $mapper);

		$mapper->expects($this->once())
			->method('loadControlItems')
			->withAnyParameters();

		$mapper->expects($this->once())
			->method('load')
			->withAnyParameters();

		$this->attachForm($form);
	}

	public function testAttached_Save()
	{
		$mapper=$this->mockMapper(array('loadControlItems', 'save'));
		$form=new Form($this->getDoctrine(), NULL, $mapper);
		$send=$form->addSubmit('send');
		$form->setSubmittedBy($send);

		$mapper->expects($this->once())
			->method('loadControlItems')
			->withAnyParameters();

		$mapper->expects($this->once())
			->method('save')
			->withAnyParameters();

		$this->attachForm($form);
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
		$entity=new Fixtures\RootEntity('Hvězda Ordinace Sandra Nováková zrušila svatbu. Víme o tom vše');
		$form=new Form($this->getDoctrine(), $entity, $mapper=$this->mockMapper('setControlMapper'));

		$mapper->expects($this->once())
			->method('setControlMapper')
			->with($this->isInstanceOf($type), $this->equalTo('name'));

		$form->$method('children', 'Name')
			->setMapper('name');
	}
}


/**
 */
class CallbackMock
extends \Nette\Object
{
	public function __invoke()
	{
	}
}
