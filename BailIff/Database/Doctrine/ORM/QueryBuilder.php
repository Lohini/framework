<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine\ORM;
/**
 * @author Filip Procházka
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

class QueryBuilder
extends \Doctrine\ORM\QueryBuilder
{
	/**
	 * Gets the root entity of the query. This is the first entity alias involved
	 * in the construction of the query.
	 *
	 * <code>
	 *     $qb = $em->createQueryBuilder()
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

	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}

	/**
	 * @param string property name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}

	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}
}
