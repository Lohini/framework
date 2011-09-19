<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models\Services;

/**
 * Uploads model service
 */
class Uploads
extends \Lohini\Database\Doctrine\ORM\BaseService
{
	/**
	 * @param array|\Traversable
	 * @param bool
	 * @return \Lohini\Database\Models\IEntity
	 * @throws \Nette\InvalidArgumentException
	 */
	public function create($values, $withoutFlush=FALSE)
	{
		try {
			$values['created']=new \DateTime;
			$values['user']= $this->getContainer()->getRepository('LE:User')
					->findOneById(\Nette\Environment::getUser()->identity->getId());
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
