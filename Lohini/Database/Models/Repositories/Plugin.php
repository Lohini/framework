<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models\Repositories;

/**
 * Plugin repository
 *
 * @author Lopo <lopo@lohini.net>
 */
class Plugin
extends \Lohini\Database\Doctrine\ORM\EntityRepository
{
	/**
	 * Finds an entity by its primary key / identifier
	 * @param int $id
	 * @param type $lockMode
	 * @param type $lockVersion
	 * @return object
	 */
	public function find($id, $lockMode=\Doctrine\DBAL\LockMode::NONE, $lockVersion=NULL)
	{
		if (func_num_args()>1) {
			throw new \Nette\NotSupportedException("'".__CLASS__."' doesn't support locking.");
			}
		// Check identity map first
		if ($entity=$this->_em->getUnitOfWork()->tryGetById($id, $this->_class->rootEntityName)) {
			if (!($entity instanceof $this->_class->name)) {
				return NULL;
				}
			return $entity; // Hit!
			}

		$qb=$this->createQueryBuilder('p')
				->where('p.id = :id')
				->setParameter('id', $id);

		try {
			return $qb->getQuery()->getSingleResult();
			}
		catch (\Doctrine\ORM\NoResultException $e) {
			return NULL;
			}
	}
}
