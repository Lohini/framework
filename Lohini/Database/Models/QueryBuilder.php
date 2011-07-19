<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models;
/**
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

class QueryBuilder
extends \Doctrine\ORM\QueryBuilder
{
	/**
	 * Gets the root entity of the query. This is the first entity alias involved
	 * in the construction of the query.
	 *
	 * <code>
	 *     $qb=$em->createQueryBuilder()
	 *         ->select('u')
	 *         ->from('User', 'u');
	 *
	 *     echo $qb->getRootEntity(); // User
	 * </code>
	 *
	 * @return string $rootEntity
	 */
	public function getRootEntity()
	{
		$from=$this->getDQLPart('from');
		return $from[0]->getFrom();
	}

	/********************* Nette\Object behaviour ****************d*g**/
	/**
	 * @return \Nette\Reflection\ClassType
	 */
	public static function getReflection()
	{
		return new \Nette\Reflection\ClassType(get_called_class());
	}

	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}

	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}

	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}

	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}

	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}
}
