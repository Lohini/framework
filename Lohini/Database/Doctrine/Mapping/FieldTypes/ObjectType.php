<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Mapping\FieldTypes;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

class ObjectType
extends \Nette\Object
implements \Lohini\Database\Doctrine\Mapping\IFieldType
{
	/**
	 * @param object $value
	 * @param object $current
	 * @return object
	 */
	public function load($value, $current)
	{
		return $value;
	}

	/**
	 * @param object $value
	 * @return object
	 */
	public function save($value)
	{
		return $value;
	}
}