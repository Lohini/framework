<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine\ORM\Types;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */

use Doctrine\DBAL\Platforms\AbstractPlatform,
	Doctrine\DBAL\Types\Type;

class Callback
extends Type
{
	/**
	 * @param \Nette\Callback $value
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 * @return string
	 */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string)$value;
    }

	/**
	 * @param string $value
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 * @return \Nette\Callback
	 */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value? new \Nette\Callback($value) : NULL;
    }

	/**
	 * @param array $fieldDeclaration
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 * @return mixed
	 */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

	/**
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 * @return int
	 */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return $platform->getVarcharDefaultLength();
    }

	/**
	 * @return string
	 */
    public function getName()
    {
        return 'callback';
    }
}
