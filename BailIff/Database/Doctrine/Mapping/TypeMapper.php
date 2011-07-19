<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine\Mapping;
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

use BailIff\Database\Doctrine\ORM\Type;

class TypeMapper
extends \Nette\Object
{
	/** @var array */
	private static $typesMap=array(
		Type::TARRAY => '\BailIff\Database\Doctrine\Mapping\FieldTypes\ArrayType',
		Type::OBJECT => '\BailIff\Database\Doctrine\Mapping\FieldTypes\ObjectType',
		Type::BOOLEAN => '\BailIff\Database\Doctrine\Mapping\FieldTypes\BooleanType',
		Type::INTEGER => '\BailIff\Database\Doctrine\Mapping\FieldTypes\IntegerType',
		Type::SMALLINT => '\BailIff\Database\Doctrine\Mapping\FieldTypes\SmallIntType',
		Type::BIGINT => '\BailIff\Database\Doctrine\Mapping\FieldTypes\BigIntType',
		Type::STRING => '\BailIff\Database\Doctrine\Mapping\FieldTypes\StringType',
		Type::TEXT => '\BailIff\Database\Doctrine\Mapping\FieldTypes\TextType',
		Type::DATETIME => '\BailIff\Database\Doctrine\Mapping\FieldTypes\DateTimeType',
		Type::DATETIMETZ => '\BailIff\Database\Doctrine\Mapping\FieldTypes\DateTimeTzType',
		Type::DATE => '\BailIff\Database\Doctrine\Mapping\FieldTypes\DateType',
		Type::TIME => '\BailIff\Database\Doctrine\Mapping\FieldTypes\TimeType',
		Type::DECIMAL => '\BailIff\Database\Doctrine\Mapping\FieldTypes\DecimalType',
		Type::FLOAT => '\BailIff\Database\Doctrine\Mapping\FieldTypes\FloatType',
		Type::CALLBACK => '\BailIff\Database\Doctrine\Mapping\FieldTypes\CallbackType',
		Type::PASSWORD => '\BailIff\Database\Doctrine\Mapping\FieldTypes\PasswordType'
		);
	/** @var array */
	private $instances=array();


	/**
	 * @param string $type
	 * @return \BailIff\Database\Doctrine\Mapping\IFieldType
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
