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
class DivisionResourcesQuery
extends \Lohini\Database\Doctrine\QueryObjectBase
{
	/** @var Division */
	private $division;

	/**
	 * @param Division $division
	 * @param \Nette\Utils\Paginator $paginator
	 */
	public function __construct(Division $division, \Nette\Utils\Paginator $paginator=NULL)
	{
		parent::__construct($paginator);
		$this->division=$division;
	}

	/**
	 * @param \Lohini\Persistence\IQueryable $repository
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(\Lohini\Persistence\IQueryable $repository)
	{
		return $repository->createQuery(
				'SELECT r FROM Lohini\Security\RBAC\Resource r, Lohini\Security\RBAC\Division d '
				.'INNER JOIN d.privileges p '
				.'WHERE p.resource = r AND d = :division '
				.'ORDER BY r.name ASC, r.description ASC'
			)->setParameter('division', $this->division);
	}
}
