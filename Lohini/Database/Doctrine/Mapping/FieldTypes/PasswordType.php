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

class PasswordType
extends \Nette\Object
implements \Lohini\Database\Doctrine\Mapping\IFieldType
{
	/**
	 * @param string $value
	 * @param \Lohini\Types\Password $current
	 * @return \Lohini\Types\Password
	 */
	public function load($value, $current)
	{
		if ($value) {
			$password=new \Lohini\Types\Password($current->getHash());
			$password->setSalt($current->getSalt());
			$password->setPassword($value);

			return $password;
			}

		return $current ?: new \Lohini\Types\Password;
	}

	/**
	 * @param string $value
	 * @return \Lohini\Types\Password
	 */
	public function save($value)
	{
		return NULL;
	}
}
