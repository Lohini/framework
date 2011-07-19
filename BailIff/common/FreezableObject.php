<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 * @author Patrik Votoček
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * Freezable object
 */
abstract class FreezableObject
extends \Nette\FreezableObject
{
	/** @var array */
	public $onFreeze=array();

	/**
	 * Freezes an array
	 */
	public function freeze()
	{
		if (!$this->isFrozen()) {
			$this->onFreeze($this);
			parent::freeze();
			}
	}
}