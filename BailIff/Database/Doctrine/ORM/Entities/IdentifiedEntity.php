<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine\ORM\Entities;

/**
 * Basic entity with ID
 *
 * @MappedSuperclass
 *
 * @property-read int $id
 */
abstract class IdentifiedEntity
extends BaseEntity
{
	/**
	 * @Id @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	private $id;


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
}
