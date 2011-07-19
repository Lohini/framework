<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine\ORM\Entities;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * @MappedSuperClass
 *
 * @property int $id
 * @property string $name
 */
abstract class NamedEntity
extends BaseEntity
{
	/**
	 * @Id @Column(type="integer")
	 * @var int
	 */
	private $id;
	/**
	 * @Column(type="string")
	 * @var string
	 */
	private $name;


	public function getId() { return $this->id; }
	public function setId($id) { $this->id=$id; }

	public function getName() { return $this->name; }
	public function setName($name) { $this->name=$name; }
}
