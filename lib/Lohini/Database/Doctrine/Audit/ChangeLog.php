<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Audit;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * This class represents complete log of all audited entities.
 * Here should be shortcuts for finding changes and listing them
 * - filter by date range
 * - filter by author
 * - filter by entity
 *
 * @todo: something-like-repository of Audit\Revision entity
 * @todo: return extended DAO or wrap it?
 */
class ChangeLog
extends \Nette\Object
{
}
