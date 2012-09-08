<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Iterators;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class SelectIterator
extends \FilterIterator
{
	/** @var \Nette\Callback[] */
	private $filters;


	/**
	 * @param callable $callback
	 * @return SelectIterator
	 */
	public function select($callback)
	{
		$iterator=new static($this->getInnerIterator());
		$iterator->filters=$this->filters;
		$iterator->filters[]=callback($callback);
		return $iterator;
	}

	/**
	 * @return bool
	 */
	public function accept()
	{
		foreach ($this->filters as $filter) {
			if (!$filter($this)) {
				return FALSE;
				}
			}
		return TRUE;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return iterator_to_array($this);
	}
}
