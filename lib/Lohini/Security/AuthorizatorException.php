<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class AuthorizatorException
extends \Exception
{
	/**
	 * @param RBAC\BasePermission $permission
	 * @return AuthorizatorException
	 */
	public static function permissionDoesNotHaveARole(RBAC\BasePermission $permission)
	{
		return new self('Given '.$permission->getAsMessage()." doesn't have assigned a role.");
	}

	/**
	 * @param RBAC\BasePermission $permission
	 * @param RBAC\Division $division
	 * @return AuthorizatorException
	 */
	public static function permissionRoleDoesNotMatchDivision(RBAC\BasePermission $permission, RBAC\Division $division)
	{
		return new self('Role of given '.$permission->getAsMessage()." doesn't come under division ".$division->getName().'.');
	}

	/**
	 * @param object $division
	 * @return AuthorizatorException
	 */
	public static function notARole($role)
	{
		return new self("Given object is not a instanceof Nette\\Security\\IRole. '".\Lohini\Utils\Tools::getType($role)."' given.");
	}

	/**
	 * @param object $division
	 * @return AuthorizatorException
	 */
	public static function roleDoNotBelongToDivision(RBAC\Role $role, RBAC\Division $division)
	{
		return new self("Given role '".$role->getName()."' is not owned by division '".$division->getName()."'");
	}

	/**
	 * @param RBAC\Role $role
	 * @return AuthorizatorException
	 */
	public static function roleAlreadyExists(RBAC\Role $role)
	{
		return new self('Role with name '.$role->getName().' already exists.');
	}

	/**
	 * @param string $role
	 * @return AuthorizatorException
	 */
	public static function roleDoNotExists($role)
	{
		return new self('Role with name '.$role.' does not exists.');
	}

	/**
	 * @param RBAC\Resource $resource
	 * @return AuthorizatorException
	 */
	public static function resourceAlreadyExists(RBAC\Resource $resource)
	{
		return new self('Resource with name '.$resource->getName().' already exists.');
	}

	/**
	 * @param string $resource
	 * @return AuthorizatorException
	 */
	public static function resourceDoNotExists($resource)
	{
		return new self('Resource with name '.$resource.' does not exists.');
	}

	/**
	 * @param RBAC\Action $action
	 * @return AuthorizatorException
	 */
	public static function actionAlreadyExists(RBAC\Action $action)
	{
		return new self('Action with name '.$action->getName().' already exists.');
	}

	/**
	 * @param string $action
	 * @return AuthorizatorException
	 */
	public static function actionDoNotExists($action)
	{
		return new self('Action with name '.$action.' does not exists.');
	}

	/**
	 * @param RBAC\Division $division
	 * @return AuthorizatorException
	 */
	public static function divisionAlreadyExists(RBAC\Division $division)
	{
		return new self('Division with name '.$division->getName().' already exists.');
	}

	/**
	 * @param string $division
	 * @return AuthorizatorException
	 */
	public static function divisionDoNotExists($division)
	{
		return new self('Division with name '.$division.' does not exists.');
	}

	/**
	 * @param RBAC\Division $division
	 * @param RBAC\BasePermission $permission
	 * @return AuthorizatorException
	 */
	public static function divisionDoNotContainPermission(RBAC\Division $division, RBAC\BasePermission $permission)
	{
		return new self('Cannot assign division '.$division->getName().' to a '.$permission->getAsMessage());
	}

	/**
	 * @return AuthorizatorException
	 */
	public static function identityRequiresDivision()
	{
		return new self('You have provided an Identity, but a Division is also required and missing.');
	}
}
