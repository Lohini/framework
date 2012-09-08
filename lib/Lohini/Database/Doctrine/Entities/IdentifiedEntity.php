<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Entities;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 *
 * @property-read int $id
 */
abstract class IdentifiedEntity
extends BaseEntity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	private $id;


	/**
	 * @return int
	 */
	final public function getId()
	{
		if ($this instanceof \Doctrine\ORM\Proxy\Proxy && !$this->__isInitialized__ && !$this->id) {
			$identifier=$this->getReflection()->getProperty('_identifier');
			$identifier->setAccessible(TRUE);
			$id=$identifier->getValue($this);
			$this->id=reset($id);
			}

		return $this->id;
	}
}
