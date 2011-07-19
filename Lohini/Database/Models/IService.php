<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 * @author	Patrik Votoček
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Model service interface
 */
interface IService
{
	/**
	 * @param Container
	 * @param string
	 */
	public function __construct(\Lohini\Database\Doctrine\BaseContainer $container, $entityClass);

	/**
	 * @return \Lohini\Database\Doctrine\BaseContainer
	 */
	public function getContainer();

	/**
	 * @return string
	 */
	public function getEntityClass();

	/**
	 * @param array|\Traversable
	 * @return \Lohini\Database\Models\IEntity
	 */
	public function create($values);

	/**
	 * @param \Lohini\Database\Models\IEntity
	 * @param array|\Traversable
	 * @return \Lohini\Database\Models\IEntity
	 */
	public function update(IEntity $entity, $values);

	/**
	 * @param \Lohini\Database\Models\IEntity
	 * @return \Lohini\Database\Models\IEntity
	 */
	public function delete(IEntity $entity);
}
