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

class PasswordType
extends \Nette\Object
implements \BailIff\Database\Doctrine\Mapping\IFieldType
{
	/**
	 * @param string $value
	 * @param \BailIff\Types\Password $current
	 * @return \BailIff\Types\Password
	 */
	public function load($value, $current)
	{
		if ($value) {
			$password=new \BailIff\Types\Password($current->getHash());
			$password->setSalt($current->getSalt());
			$password->setPassword($value);

			return $password;
			}

		return $current ?: new \BailIff\Types\Password;
	}

	/**
	 * @param string $value
	 * @return \BailIff\Types\Password
	 */
	public function save($value)
	{
		return NULL;
	}
}
