<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing\Tools;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Application\UI;

/**
 */
class UIFormTestingPresenter
extends UI\Presenter
{
	/** @var UI\Form */
	private $form;


	/**
	 * @param UI\Form $form
	 */
	public function __construct(UI\Form $form)
	{
		parent::__construct();
		$this->form=$form;
	}

	/**
	 * Just terminate the rendering
	 */
	public function renderDefault()
	{
		$this->terminate();
	}

	/**
	 * @return UI\Form
	 */
	protected function createComponentForm()
	{
		return $this->form;
	}
}
