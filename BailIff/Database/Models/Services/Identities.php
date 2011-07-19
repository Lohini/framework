<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Models\Services;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 * @author	Patrik Votoček
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * Identity model service
 */
class Identities
extends \BailIff\Database\Doctrine\ORM\BaseService
{
	/**
	 * @param array|\Traversable
	 * @param bool
	 * @return \BailIff\Database\Models\IEntity
	 * @throws \Nette\InvalidArgumentException
	 */
	public function create($values, $withoutFlush=FALSE)
	{
		try {
			if (!$values['role'] instanceof \BailIff\Database\Models\Entities\UserRole) {
				$roleService=$this->getContainer()->getModelService('BailIff\Database\Models\Entities\UserRole');
				$values['role']=$roleService->repository->find($values['role']);
				}

			$entity=parent::create($values, TRUE);
			$em=$this->getEntityManager();
			$em->persist($entity);
			if (!$withoutFlush) {
				$em->flush();
				}
			return $entity;
			}
		catch (\PDOException $e) {
			$this->processPDOException($e);
			}
	}
}
