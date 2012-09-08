<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Application\UI;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Application\UI\Form;

/**
 */
class FormTest
extends \Lohini\Testing\TestCase
{
	/** @var \Lohini\Tests\Application\UI\MockForm */
	private $form;


	public function setup()
	{
		$presenter=$this->getMock('Nette\Application\UI\Presenter', array(), array($this->getContext()));

		$this->form=new MockForm;
		$this->form->setParent($presenter, 'form');
	}

	public function testCreation()
	{
		$this->assertInstanceOf('Nette\Forms\Controls\TextInput', $this->form->getComponent('name', FALSE));
	}

	public function testAttachingEvents()
	{
		$this->assertEventHasCallback(array($this->form, 'handleSuccess'), $this->form, 'onSuccess');
		$this->assertEventHasCallback(array($this->form, 'handleError'), $this->form, 'onError');
		$this->assertEventHasCallback(array($this->form, 'handleValidate'), $this->form, 'onValidate');
	}

	public function testAttachingButtonEvents()
	{
		$this->assertEventHasCallback(array($this->form, 'handleSaveClick'), $this->form['save'], 'onClick');
		$this->assertEventHasCallback(array($this->form, 'handleSaveInvalidClick'), $this->form['save'], 'onInvalidClick');
		$this->assertEventHasCallback(array($this->form, 'handleFooBarEditClick'), $this->form['foo']['bar']['edit'], 'onClick');
	}
}


class MockForm
extends Form
{
	protected function configure()
	{
		$this->addText('name', 'Jméno');

		$this->addSubmit('save', 'Odeslat');

		$bar=$this->addContainer('foo')->addContainer('bar');
		$bar->addSubmit('edit', 'Odeslat');
	}

	public function handleSuccess()
	{
		throw new \Nette\NotImplementedException;
	}

	public function handleError()
	{
		throw new \Nette\NotImplementedException;
	}

	public function handleValidate()
	{
		throw new \Nette\NotImplementedException;
	}

	public function handleSaveClick()
	{
		throw new \Nette\NotImplementedException;
	}

	public function handleSaveInvalidClick()
	{
		throw new \Nette\NotImplementedException;
	}

	public function handleFooBarEditClick()
	{
		throw new \Nette\NotImplementedException;
	}
}
