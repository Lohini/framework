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
interface IQueryable
{
	/**
	 * Create a new QueryBuilder instance that is prepopulated for this entity name
	 *
	 * @param string|NULL $alias
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	function createQueryBuilder($alias=NULL);

	/**
	 * @param string|NULL $dql
	 * @return \Doctrine\ORM\Query
	 */
	function createQuery($dql=NULL);
}
