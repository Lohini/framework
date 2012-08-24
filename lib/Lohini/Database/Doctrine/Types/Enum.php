<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Types;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class Enum
extends \Doctrine\DBAL\Types\Type
{
	/**
	 * @param array $fieldDeclaration
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 * @return mixed
	 * @throws \Nette\InvalidStateException
	 */
    public function getSQLDeclaration(array $fieldDeclaration, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        throw new \Nette\InvalidStateException("Please, use the 'columnDefinition' property of @Column() annotation.");
    }

	/**
	 * @return string
	 */
    public function getName()
    {
        return 'enum';
    }
}
