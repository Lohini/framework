<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Utils;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class ExceptionFactory
extends \Nette\Object
{
	/**
	 * @param int $argument number of argument
	 * @param string $type required type of argument
	 * @param mixed|NULL $value the given value
	 * @return \Nette\InvalidArgumentException
	 */
	public static function invalidArgument($argument, $type, $value=NULL)
	{
		$stack=debug_backtrace(FALSE);

		return new \Nette\InvalidArgumentException(
			sprintf('Argument #%d%sof %s::%s() must be a %s',
				$argument,
				$value!==NULL ? ' ('.$value.') ' : ' ',
				$stack[1]['class'],
				$stack[1]['function'],
				$type
				)
			);
	}
}
