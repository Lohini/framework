<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class SimplePermission
extends \Nette\Object
implements \Nette\Security\IAuthorizator
{
	/** @var array */
	private $rules=array();


	/**
	 * Performs a role-based authorization.
	 *
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege
	 * @return bool
	 */
	public function isAllowed($role=NULL, $resource=NULL, $privilege=NULL)
	{
		if (isset($this->rules[$role][$resource][$privilege])) {
			return $this->rules[$role][$resource][$privilege];
			}

		return FALSE;
	}

	/**
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege
	 */
	public function allow($role, $resource, $privilege)
	{
		$this->setRule($role, $resource, $privilege, TRUE);
	}

	/**
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege
	 */
	public function deny($role, $resource, $privilege)
	{
		$this->setRule($role, $resource, $privilege, FALSE);
	}

	/**
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege
	 * @param boolean $rule
	 */
	private function setRule($role, $resource, $privilege, $rule)
	{
		if ($role instanceof \Nette\Security\IRole) {
			$role=$role->getRoleId();
			}

		if ($resource instanceof \Nette\Security\IResource) {
			$resource=$resource->getResourceId();
			}

		$this->rules[$role][$resource][$privilege]=$rule;
	}
}
