<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Environment;

/**
 * Translates the given string.
 *
 * @param string|array message or messages
 * @return int|array count or variables
 */
function __($message, $count=NULL)
{
	return Environment::getService('translator')
		->translate($message, $count);
}

/**
 * Translates the given string with plural.
 *
 * @deprecated
 * @param string
 * @param string
 * @param int plural form (positive number)
 * @return string
 */
function _n($single, $plural, $number)
{
	trigger_error(__FUNCTION__.'() is deprecated; use __(array(\$single, \$plural), \$number) instead.', E_USER_DEPRECATED);
	return Environment::getService('translator')
		->translate(array($single, $plural), $number);
}

/**
 * Translates the given string with vsprintf.
 *
 * @deprecated
 * @param string
 * @param array for vsprintf
 * @return string
 */
function _x($message, array $args)
{
	trigger_error(__FUNCTION__.'() is deprecated; use __(\$message, $args) instead.', E_USER_DEPRECATED);
	return Environment::getService('translator')
		->translate($message, $args);
}

/**
 * Translates the given string with plural and vsprintf.
 *
 * @deprecated
 * @param string
 * @param string
 * @param int plural form (positive number)
 * @param array for vsprintf
 * @return string
 */
function _nx($single, $plural, $number, array $args)
{
	trigger_error(__FUNCTION__.'() is deprecated; use __(array(\$single, \$plural), array(\$number, $args[0], $args[1], ...) instead.', E_USER_DEPRECATED);
	return Environment::getService('translator')
		->translate(array($single, $plural), array_merge(array($number), $args));
}
