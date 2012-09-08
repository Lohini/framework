<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Persistence;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 *
 */
interface IQueryObject
{
	/**
	 * @param IQueryable $repository
	 * @return int
	 */
	function count(IQueryable $repository);

	/**
	 * @param IQueryable $repository
	 * @return mixed|\Lohini\Database\Doctrine\ResultSet
	 */
	function fetch(IQueryable $repository);

	/**
	 * @param IQueryable $repository
	 * @return object
	 */
	function fetchOne(IQueryable $repository);
}
