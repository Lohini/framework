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
interface IDao
{
	const FLUSH=FALSE;
	const NO_FLUSH=TRUE;


	/**
	 * Persists given entities, but does not flush.
	 *
	 * @param object|array|Collection
	 */
	function add($entity);

	/**
	 * Persists given entities and flushes them down to the storage.
	 *
	 * @param object|array|Collection|NULL
	 */
	function save($entity=NULL);

	/**
	 * @param object|array|Collection
	 * @param bool $withoutFlush
	 */
	function delete($entity, $withoutFlush=self::FLUSH);
}
