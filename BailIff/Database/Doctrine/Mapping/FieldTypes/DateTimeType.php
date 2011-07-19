<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine\Mapping\FieldTypes;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use Nette\DateTime;

class DateTimeType
extends \Nette\Object
implements \BailIff\Database\Doctrine\Mapping\IFieldType
{
	/**
	 * @param string $value
	 * @param mxied $current
	 * @return \Nette\DateTime
	 */
	public function load($value, $current)
	{
		return DateTime::from($value);
	}

	/**
	 * @param string $value
	 * @return \Nette\DateTime
	 */
	public function save($value)
	{
		return DateTime::from($value);
	}
}
