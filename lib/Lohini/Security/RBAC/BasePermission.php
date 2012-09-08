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
 * @ORM\Table(name="rbac_permissions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="_type", type="string")
 * @ORM\DiscriminatorMap({"base" = "BasePermission"})
 */
abstract class BasePermission
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
	 * @ORM\ManyToOne(targetEntity="Division", inversedBy="permissions", cascade={"persist"})
	 * @ORM\JoinColumn(name="division_id", referencedColumnName="id")
	 * @var Division
	 */
	private $division;
	/**
	 * @ORM\ManyToOne(targetEntity="Privilege", cascade={"persist"})
	 * @ORM\JoinColumn(name="privilege_id", referencedColumnName="id")
	 * @var Privilege
	 */
	private $privilege;
	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	private $isAllowed=TRUE;


	/**
	 * @param Privilege $privilege
	 */
	public function __construct(Privilege $privilege)
	{
		$this->privilege=$privilege;
	}

	/**
	 * @internal
	 * @param Division $division
	 * @throws \Nette\InvalidArgumentException
	 */
	public function internalSetDivision(Division $division)
	{
		if (!$division->hasPrivilege($this->getPrivilege())) {
			throw new \Nette\InvalidArgumentException("Privilege '".$this->getPrivilege()->getName()."' in permission is not allowed within given division ".$division->getName());
			}

		$this->division = $division;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @internal
	 * @return string
	 */
	public function getAsMessage()
	{
		$actionName=$this->getPrivilege()->getAction()->getName();
		$resourceName=$this->getPrivilege()->getResource()->getName();

		return "permission to '$actionName' the '$resourceName'";
	}

	/**
	 * @return Division
	 */
	public function getDivision()
	{
		return $this->division;
	}

	/**
	 * @return Privilege
	 */
	public function getPrivilege()
	{
		return $this->privilege;
	}

	/**
	 * @return bool
	 */
	public function isAllowed()
	{
		return $this->isAllowed;
	}

	/**
	 * @param bool $allowed
	 * @return BasePermission (fluent)
	 */
	public function setAllowed($allowed=TRUE)
	{
		$this->isAllowed=(bool)$allowed;
		return $this;
	}

	/**
	 * @return \Nette\Security\IRole
	 */
	abstract public function getRole();

	/**
	 * @return string
	 */
	protected function getRoleId()
	{
		return $this->getRole()->getRoleId();
	}

	/**
	 * @todo callback assertion
	 *
	 * @param \Nette\Security\Permission $permission
	 */
	public function applyTo(\Nette\Security\Permission $permission)
	{
		$resourceId=$this->getPrivilege()->getResource()->getResourceId();
		$actionName=$this->getPrivilege()->getAction()->getName();

		if ($this->isAllowed) {
			$permission->allow($this->getRoleId(), $resourceId, $actionName);
			}
		else {
			$permission->deny($this->getRoleId(), $resourceId, $actionName);
			}
	}
}
