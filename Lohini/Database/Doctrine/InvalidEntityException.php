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
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author	Patrik Votoček
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Ivalid entity state exception
 */
class InvalidEntityException
extends Exception
{
	/** @var array */
	private $errors;


	/**
	 * @param string $message
	 * @param array $errors
	 */
	public function __construct($message, array $errors)
	{
		parent::__construct($message);
		$this->errors=$errors;
	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}
}
