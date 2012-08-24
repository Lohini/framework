<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security\RBAC;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\Common\Collections\ArrayCollection,
	Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rbac_divisions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="_type", type="string")
 * @ORM\DiscriminatorMap({"base" = "Division"})
 */
class Division
extends \Nette\Object
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
	 * @ORM\OneToMany(targetEntity="BasePermission", mappedBy="division", cascade={"persist"})
	 * @var Collection
	 */
	private $permissions;
	/**
	 * @ORM\ManyToMany(targetEntity="Privilege", cascade={"persist"})
	 * @ORM\JoinTable(name="rbac_divisions_privileges",
	 *		joinColumns={@ORM\JoinColumn(name="privilege_id", referencedColumnName="id")},
	 *		inverseJoinColumns={@ORM\JoinColumn(name="division_id", referencedColumnName="id")}
	 *	)
	 * @var Collection
	 */
	private $privileges;


	/**
	 * @param string $name
	 * @throws \Nette\InvalidArgumentException
	 */
	public function __construct($name)
	{
		if (!is_string($name)) {
			throw new \Nette\InvalidArgumentException('Given name is not string, '.gettype($name).' given.');
			}

		$this->name=$name;
		$this->permissions=new ArrayCollection;
		$this->privileges=new ArrayCollection;
	}

	/**
	 * @return int
	 */
	final public function getId()
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
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 * @return Division (fluent)
	 */
	public function setDescription($description)
	{
		$this->description=$description;
		return $this;
	}

	/**
	 * @param BasePermission $permission
	 * @return Division (fluent)
	 * @throws \Lohini\Security\AuthorizatorException
	 * @throws \Nette\InvalidArgumentException
	 */
	public function addPermission(BasePermission $permission)
	{
		$role=$permission->getRole();
		if (!$role instanceof \Nette\Security\IRole) {
			throw \Lohini\Security\AuthorizatorException::permissionDoesNotHaveARole($permission);
			}

		if ($role instanceof Role && $role->getDivision()!==$this) {
			throw \Lohini\Security\AuthorizatorException::permissionRoleDoesNotMatchDivision($permission, $this);
			}

		$privilege=$permission->getPrivilege();
		if (!$this->hasPrivilege($privilege)) {
			throw new \Nette\InvalidArgumentException("Privilege '".$privilege->getName()."' for given permission is not registered in division '".$this->getName()."'.");
			}

		if (!$this->hasPermission($permission)) {
			$this->permissions->add($permission);
			}
		$permission->internalSetDivision($this);
		return $this;
	}

	/**
	 * @param BasePermission $permission
	 */
	public function hasPermission(BasePermission $permission)
	{
		return $this->permissions->contains($permission);
	}

	/**
	 * @param BasePermission $permission
	 * @return Division (fluent)
	 */
	public function removePermission(BasePermission $permission)
	{
		$this->permissions->removeElement($permission);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getPermissions()
	{
		return $this->permissions->toArray();
	}

	/**
	 * @param Privilege $privilege
	 * @return Division (fluent)
	 */
	public function addPrivilege(Privilege $privilege)
	{
		if (!$this->hasPrivilege($privilege)) {
			$this->privileges->add($privilege);
			}

		return $this;
	}

	/**
	 * @param Privilege $privilege
	 * @return Division (fluent)
	 */
	public function removePrivilege(Privilege $privilege)
	{
		$this->privileges->removeElement($privilege);
		return $this;
	}

	/**
	 * @param Privilege $privilege
	 * @return bool
	 */
	public function hasPrivilege(Privilege $privilege)
	{
		return $this->privileges->contains($privilege);
	}

	/**
	 * @return array
	 */
	public function getPrivileges()
	{
		return $this->privileges->toArray();
	}
}