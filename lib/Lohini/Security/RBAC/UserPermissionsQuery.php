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
class UserPermissionsQuery
extends \Lohini\Database\Doctrine\QueryObjectBase
{
	/** @var \Lohini\Security\Identity */
	private $identity;
	/** @var Division */
	private $division;


	/**
	 * @param \Lohini\Security\Identity $identity
	 * @param Division $division
	 * @param \Nette\Utils\Paginator $paginator
	 */
	public function __construct(\Lohini\Security\Identity $identity, Division $division, \Nette\Utils\Paginator $paginator=NULL)
	{
		parent::__construct($paginator);
		$this->identity=$identity;
		$this->division=$division;
	}

	/**
	 * @param \Lohini\Persistence\IQueryable $repository
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(\Lohini\Persistence\IQueryable $repository)
	{
		return $repository->createQueryBuilder('perm')->select('perm', 'priv', 'act', 'res')
			->innerJoin('perm.privilege', 'priv')
			->innerJoin('perm.division', 'div')
			->innerJoin('perm.identity', 'ident')
			->innerJoin('priv.action', 'act')
			->innerJoin('priv.resource', 'res')
			->where('ident = :identity')
				->setParameter('identity', $this->identity)
			->andWhere('div = :division')
				->setParameter('division', $this->division);
	}
}
