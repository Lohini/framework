<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\ORM\Entities;

/**
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 *
 * @property-read int $id
 */
abstract class BaseEntity
extends \Nette\Object
{
	public function __construct() {}

	/**
	 * @param string
	 * @return string
	 */
	protected function sanitizeString($s)
	{
		$s=trim($s);
		return $s===''? NULL : $s;
	}
}
