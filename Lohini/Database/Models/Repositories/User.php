<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models\Repositories;

/**
 * UserRole repository
 *
 * @author Lopo <lopo@lohini.net>
 */
class User
extends \Lohini\Database\Doctrine\ORM\EntityRepository
{
	/**
	 * Returns all role entities ordered by name
	 *
	 * @param bool
	 * @return array
	 */
	public function findAllOrderedByName($desc=FALSE)
	{
		$qb=$this->createQueryBuilder('u')
				->orderBy('u.username', $desc? 'DESC' : 'ASC');
		return $qb->getQuery()->getResult();
	}

	/**
	 * @param string $nameOrEmail
	 * @return \Nette\Security\IIdentity
	 */
	public function findByUsernameOrEmail($nameOrEmail)
	{
		$qb=$this->createQueryBuilder('u')
				->where('u.username = :nameOrEmail')
				->orWhere('u.email = :nameOrEmail')
				->setParameter('nameOrEmail', $nameOrEmail);

		try {
			return $qb->getQuery()->getSingleResult();
			}
		catch (\Doctrine\ORM\NoResultException $e) {
			return NULL;
			}
	}
}
