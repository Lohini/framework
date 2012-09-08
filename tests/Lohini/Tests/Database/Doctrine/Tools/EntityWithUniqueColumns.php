<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine\Tools;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="names_table")
 */
class EntityWithUniqueColumns
extends \Lohini\Database\Doctrine\Entities\IdentifiedEntity
{
	/** @ORM\Column(type="string", unique=TRUE) */
	public $email;
	/** @ORM\Column(type="string") */
	public $name;
	/** @ORM\Column(type="string", nullable=TRUE) */
	public $address;


	/**
	 * @param array $values
	 */
	public function __construct($values = array())
	{
		foreach ($values as $field => $value) {
			$this->{$field}=$value;
			}
	}
}
