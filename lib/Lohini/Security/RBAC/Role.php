<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security\RBAC;
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
 * @ORM\Table(name="rbac_roles")
 */
class Role
extends \Nette\Object
implements \Nette\Security\IRole
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	private $id;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $name;
	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	private $description;
	/**
	 * @ORM\ManyToOne(targetEntity="Division", cascade={"persist"})
	 * @ORM\JoinColumn(name="division_id", referencedColumnName="id")
	 * @var Division
	 */
	private $division;


	/**
	 * @param string $name
	 * @param Division $division
	 * @throws \Nette\InvalidArgumentException
	 */
	public function __construct($name, Division $division)
	{
		if (!is_string($name)) {
			throw new \Nette\InvalidArgumentException('Given name is not string, '.gettype($name).' given.');
			}

		$this->name=$name;
		$this->division=$division;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getRoleId()
	{
		return (string)$this->id;
	}

	/**
	 * @return Division
	 */
	public function getDivision()
	{
		return $this->division;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 * @return Role (fluent)
	 */
	public function setDescription($description)
	{
		$this->description=$description;
		return $this;
	}

	/**
	 * @param Privilege $privilege
	 * @return RolePermission
	 */
	public function createPermission(Privilege $privilege)
	{
		$permission=new RolePermission($privilege, $this);
		$this->division->addPermission($permission);
		return $permission;
	}
}
