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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @Lohini\Database\Doctrine\Mapping\DiscriminatorEntry(name="role")
 */
class RolePermission
extends BasePermission
{
	/**
	 * @ORM\ManyToOne(targetEntity="Role", cascade={"persist"}, fetch="EAGER")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
	 * @var Role
	 */
	private $role;


	/**
	 * @param Privilege $privilege
	 * @param Role $role
	 * @throws \Nette\InvalidArgumentException
	 * @throws \Nette\InvalidStateException
	 */
	public function __construct(Privilege $privilege, \Nette\Security\IRole $role)
	{
		if (!$role instanceof Role) {
			throw new \Nette\InvalidArgumentException("Given role is not instanceof Lohini\\Security\\RBAC\\Role, '".get_class($role)."' given");
			}

		if ($this->role!==NULL) {
			throw new \Nette\InvalidStateException('Association with role is immutable.');
			}

		$this->role=$role;
		parent::__construct($privilege);
	}

	/**
	 * @return Role
	 */
	public function getRole()
	{
		return $this->role;
	}
}
