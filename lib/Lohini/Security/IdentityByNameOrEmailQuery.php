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
class IdentityByNameOrEmailQuery
extends \Lohini\Database\Doctrine\QueryObjectBase
{
	/** @var string */
	private $nameOrEmail;


	/**
	 * @param string $nameOrEmail
	 * @param \Nette\Utils\Paginator $paginator
	 */
	public function __construct($nameOrEmail, \Nette\Utils\Paginator $paginator=NULL)
	{
		parent::__construct($paginator);
		$this->nameOrEmail=$nameOrEmail;
	}

	/**
	 * @return string
	 */
	public function getNameOrEmail()
	{
		return $this->nameOrEmail;
	}

	/**
	 * @param \Lohini\Persistence\IQueryable $repository
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(\Lohini\Persistence\IQueryable $repository)
	{
		return $repository->createQueryBuilder('u')
			->leftJoin('u.info', 'i')
			->where('u.username = :nameOrEmail')
			->orWhere('u.email = :nameOrEmail')
			->setParameter('nameOrEmail', $this->nameOrEmail);
	}
}
