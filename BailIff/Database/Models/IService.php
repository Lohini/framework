<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Models;
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
 * Model service interface
 */
interface IService
{
	/**
	 * @param Container
	 * @param string
	 */
	public function __construct(\BailIff\Database\Doctrine\BaseContainer $container, $entityClass);

	/**
	 * @return \BailIff\Database\Doctrine\BaseContainer
	 */
	public function getContainer();

	/**
	 * @return string
	 */
	public function getEntityClass();

	/**
	 * @param array|\Traversable
	 * @return \BailIff\Database\Models\IEntity
	 */
	public function create($values);

	/**
	 * @param \BailIff\Database\Models\IEntity
	 * @param array|\Traversable
	 * @return \BailIff\Database\Models\IEntity
	 */
	public function update(IEntity $entity, $values);

	/**
	 * @param \BailIff\Database\Models\IEntity
	 * @return \BailIff\Database\Models\IEntity
	 */
	public function delete(IEntity $entity);
}
