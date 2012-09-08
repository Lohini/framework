<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Forms;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class ItemPairsQuery
extends \Lohini\Database\Doctrine\QueryObjectBase
{
	/** @var object */
	private $entity;
	/** @var string */
	private $field;
	/** @var string */
	private $value;
	/** @var string */
	private $key;


	/**
	 * @param string $entity
	 * @param string $field
	 * @param string $value
	 * @param string $key
	 */
	public function __construct($entity, $field, $value, $key='id')
	{
		$this->entity=$entity;
		$this->field=$field;
		$this->value=$value;
		$this->key=$key;
	}

	/**
	 * @param \Lohini\Persistence\IQueryable $repository
	 * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(\Lohini\Persistence\IQueryable $repository)
	{
		return $repository->createQuery(
			"SELECT i.$this->key, i.$this->value FROM ".get_class($this->entity).' e '
			."LEFT JOIN e.$this->field i "
			.'WHERE e = :id'
			)->setParameter('id', $this->entity);
	}
}
