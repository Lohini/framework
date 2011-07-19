<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine\ORM\Repositories;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

class NestedTreeRepository
extends \Gedmo\Tree\Entity\Repository\NestedTreeRepository
{
	/**
	 * Unefectively retrieves all the entities in tree
	 * NOTE: Fuck it for now, better that query for each entity
	 *
	 * @param int $id
	 * @param int|NULL $maxLevel
	 * @return \BailIff\Database\Doctrine\Entities\NestedNode
	 */
	public function findTreeByRootId($id, $maxLevel=0)
	{
		// prepare query
		$qb=$this->createQueryBuilder('n')
			->select('n', 'ch')
			->innerJoin('n.children', 'ch')
			->where('n.nodeRoot = :id');

		$qb->setParameter('id', $id);

		if ($maxLevel>0) {
			$qb->andWhere('n.nodeLvl <= :lvl');
			$qb->andWhere('ch.nodeLvl <= :lvl');
			$qb->setParameter('lvl', $maxLevel);
			}

		// fetch result
		$qb->getQuery()->getResult();

		// returns already managed entity
		return $this->find($id);
	}
}
