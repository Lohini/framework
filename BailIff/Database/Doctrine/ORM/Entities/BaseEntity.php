<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine\ORM\Entities;

use \Nette\Environment;

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
