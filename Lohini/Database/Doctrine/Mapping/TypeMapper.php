<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Mapping;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Doctrine\ORM\Type;

class TypeMapper
extends \Nette\Object
{
	/** @var array */
	private static $typesMap=array(
		Type::TARRAY => '\Lohini\Database\Doctrine\Mapping\FieldTypes\ArrayType',
		Type::OBJECT => '\Lohini\Database\Doctrine\Mapping\FieldTypes\ObjectType',
		Type::BOOLEAN => '\Lohini\Database\Doctrine\Mapping\FieldTypes\BooleanType',
		Type::INTEGER => '\Lohini\Database\Doctrine\Mapping\FieldTypes\IntegerType',
		Type::SMALLINT => '\Lohini\Database\Doctrine\Mapping\FieldTypes\SmallIntType',
		Type::BIGINT => '\Lohini\Database\Doctrine\Mapping\FieldTypes\BigIntType',
		Type::STRING => '\Lohini\Database\Doctrine\Mapping\FieldTypes\StringType',
		Type::TEXT => '\Lohini\Database\Doctrine\Mapping\FieldTypes\TextType',
		Type::DATETIME => '\Lohini\Database\Doctrine\Mapping\FieldTypes\DateTimeType',
		Type::DATETIMETZ => '\Lohini\Database\Doctrine\Mapping\FieldTypes\DateTimeTzType',
		Type::DATE => '\Lohini\Database\Doctrine\Mapping\FieldTypes\DateType',
		Type::TIME => '\Lohini\Database\Doctrine\Mapping\FieldTypes\TimeType',
		Type::DECIMAL => '\Lohini\Database\Doctrine\Mapping\FieldTypes\DecimalType',
		Type::FLOAT => '\Lohini\Database\Doctrine\Mapping\FieldTypes\FloatType',
		Type::CALLBACK => '\Lohini\Database\Doctrine\Mapping\FieldTypes\CallbackType',
		Type::PASSWORD => '\Lohini\Database\Doctrine\Mapping\FieldTypes\PasswordType'
		);
	/** @var array */
	private $instances=array();


	/**
	 * @param string $type
	 * @return \Lohini\Database\Doctrine\Mapping\IFieldType
	 * @throws \Nette\MemberAccessException
	 */
	protected function getTypeMapper($type)
	{
		if (!isset($this->instances[$type])) {
			if (!self::$typesMap[$type]) {
				throw new \Nette\MemberAccessException("Unkwnown type '$type'.");
				}
			$this->instances[$type]=new self::$typesMap[$type]();
			}
		return $this->instances[$type];
	}

	/**
	 * @param mixed $currentValue
	 * @param mixed $newValue
	 * @param string $type
	 * @return mixed
	 */
	public function load($currentValue, $newValue, $type)
	{
		return $this->getTypeMapper($type)->load($newValue, $currentValue);
	}

	/**
	 * @param mixed $currentValue
	 * @param string $type
	 * @return mixed
	 */
	public function save($currentValue, $type)
	{
		return $this->getTypeMapper($type)->save($currentValue);
	}
}
