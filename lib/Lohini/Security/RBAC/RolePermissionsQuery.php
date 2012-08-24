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

/**
 */
class RolePermissionsQuery
extends \Lohini\Database\Doctrine\QueryObjectBase
{
	/** @var Role */
	private $role;


	/**
	 * @param Role $role
	 * @param \Nette\Utils\Paginator $paginator
	 */
	public function __construct(Role $role, \Nette\Utils\Paginator $paginator=NULL)
	{
		parent::__construct($paginator);
		$this->role=$role;
	}

	/**
	 * @param \Lohini\Persistence\IQueryable $repository
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(\Lohini\Persistence\IQueryable $repository)
	{
		return $repository->createQueryBuilder('perm')->select('perm', 'priv', 'act', 'res')
			->innerJoin('perm.privilege', 'priv')
			->innerJoin('perm.role', 'role')
			->innerJoin('priv.action', 'act')
			->innerJoin('priv.resource', 'res')
			->where('role = :role')
				->setParameter('role', $this->role);
	}
}
