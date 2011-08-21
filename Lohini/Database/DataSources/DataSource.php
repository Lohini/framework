<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\DataSources;
/**
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Base class for all data sources
 */
abstract class DataSource
extends \Nette\Object
implements IDataSource
{
	/**
	 * Validates filter operation
	 * @param string $operation
	 * @throws \Nette\InvalidStateException if operation is not valid
	 */
	protected function validateFilterOperation($operation)
	{
		static $types=array(
			self::EQUAL,
			self::NOT_EQUAL,
			self::GREATER,
			self::GREATER_OR_EQUAL,
			self::LESS,
			self::LESS_OR_EQUAL,
			self::LIKE,
			self::NOT_LIKE,
			self::IS_NULL,
			self::IS_NOT_NULL,
			);

		if (!in_array($operation, $types, TRUE)) {
			throw new \Nette\InvalidStateException('Invalid filter operation type.');
			}
	}
}
