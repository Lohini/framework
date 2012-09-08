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

/**
 */
class UIFormStub
extends \Nette\Application\UI\Form
{
	/** @var array */
	private $fakeHttpValues;


	/**
	 * @param array $values
	 */
	public function __construct($values=array())
	{
		parent::__construct();
		$this->fakeHttpValues=$values;
	}

	/**
	 * @return bool
	 */
	public function isAnchored()
	{
		return TRUE;
	}

	/**
	 * @return array
	 */
	protected function receiveHttpData()
	{
		return $this->fakeHttpValues;
	}
}
