<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 * @author	Patrik Votoček
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Empty value exception
 */
class EmptyValueException
extends Exception
{
	/** @var string */
	private $column;


	/**
	 * @param string $message
	 * @param string $column
	 * @param \Exception $parent
	 */
	public function __construct($message, $column=NULL, \Exception $parent=NULL)
	{
		parent::__construct($message, 0, $parent);
		$this->column=$column;
	}

	/**
	 * @return column
	 */
	public function getColumn()
	{
		return $this->column;
	}
}
