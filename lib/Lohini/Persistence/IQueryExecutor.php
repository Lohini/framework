<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Persistence;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 *
 */
interface IQueryExecutor
{
	/**
	 * @param IQueryObject $queryObject
	 * @return int
	 */
	function count(IQueryObject $queryObject);

	/**
	 * @param IQueryObject $queryObject
	 * @return array
	 */
	function fetch(IQueryObject $queryObject);

	/**
	 * @param IQueryObject $queryObject
	 * @return object
	 */
	function fetchOne(IQueryObject $queryObject);
}