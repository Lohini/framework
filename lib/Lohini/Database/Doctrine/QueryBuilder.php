<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\ObjectMixin,
	Nette\Utils\Strings;

/**
 */
class QueryBuilder
extends \Doctrine\ORM\QueryBuilder
{
	/**
	 * Gets the root entity of the query. This is the first entity alias involved
	 * in the construction of the query.
	 *
	 * <code>
	 *	 $qb = $em->createQueryBuilder()
	 *		 ->select('u')
	 *		 ->from('User', 'u');
	 *
	 *	 echo $qb->getRootEntity(); // User
	 * </code>
	 *
	 * @return string $rootEntity
	 */
	public function getRootEntity()
	{
		return current($this->getRootEntities());
	}

	/**
	 * Gets the root entity of the query. This is the first entity alias involved
	 * in the construction of the query.
	 *
	 * <code>
	 *	 $qb = $em->createQueryBuilder()
	 *		 ->select('u')
	 *		 ->from('User', 'u');
	 *
	 *	 $qb->getRootAlias(); // array('u')
	 * </code>
	 *
	 * @return string $rootEntity
	 */
	public function getRootAlias()
	{
		return current($this->getRootAliases());
	}

	/**
	 * @return array
	 */
	public function getEntityAliases()
	{
		static $joined=array();
		if ($joined) {
			return $joined;
			}

		$joined[]= $rootAlias= $this->getRootAlias();
		$joins=$this->getDQLPart('join');
		if (!isset($joins[$rootAlias])) {
			return $joined;
			}

		foreach ($joins[$rootAlias] as $join) {
			/** @var \Doctrine\ORM\Query\Expr\Join $join */
			if ($m=Strings::match((string)$join, '~^(?:LEFT|INNER)\s+JOIN\s+([^\s]+)(?:\s+([^\s]+)\s*(?:ON|WITH)?\s*.*)?$~i')) {
				$joined[$m[1]]=$m[2];
				}
			}

		return $joined;
	}

	/**
	 * @param string $alias
	 * @param array $values
	 * @return QueryBuilder
	 */
	public function andWhereEquals($alias, array $values)
	{
		$suffix=Strings::random(4);

		foreach ($values as $key => $value) {
			$paramName=$key.'_'.$suffix;

			$this->andWhere($alias.'.'.$key.' = :'.$paramName);
			$this->setParameter($paramName, $value);
			}

		return $this;
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