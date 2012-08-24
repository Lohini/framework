<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */


/**
 */
interface Exception
{
}


/**
 */
class AssetNotFoundException extends \OutOfRangeException implements Exception
{
}

/**
 */
class InvalidDefinitionFileException extends \RuntimeException implements Exception
{
}

/**
 */
class LatteCompileException
extends \Nette\Latte\CompileException
implements Exception
{
	/**
	 * @param string $message
	 * @param \Exception|NULL $previous
	 */
	public function __construct($message=NULL, \Exception $previous=NULL)
	{
		\Exception::__construct($previous? $previous->getMessage() : $message, 0, $previous);
	}
}
