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

use Lohini\Database\Doctrine\Type;

/**
 */
class ContainerBuilder
extends \Nette\Object
{
	/** @var \Nette\Forms\Container|\Lohini\Application\UI\Form */
	private $container;
	/** @var \Lohini\Database\Doctrine\Mapping\ClassMetadata */
	private $class;


	/**
	 * @param \Nette\Forms\Container $container
	 * @param \Lohini\Database\Doctrine\Mapping\ClassMetadata $class
	 */
	public function __construct(\Nette\Forms\Container $container, \Lohini\Database\Doctrine\Mapping\ClassMetadata $class)
	{
		$this->container=$container;
		$this->class=$class;
	}

	/**
	 * Adds all fields to container
	 */
	public function addAllFields()
	{
		foreach ($this->class->getFieldNames() as $field) {
			if ($this->class->isIdentifier($field)) {
				continue;
				}
			if (in_array($this->class->getTypeOfField($field), array('array', 'object', 'blog'))) {
				continue;
				}

			$this->addFields($field);
			}
	}

	/**
	 * @param string|array $fields
	 */
	public function addFields($fields)
	{
		foreach (is_array($fields)? $fields : func_get_args() as $field) {
			$control=$this->buildField($field);
			$this->buildValidations($control);
			}
	}

	/**
	 * @param $field
	 * @return \Nette\Forms\Controls\BaseControl
	 * @throws \Nette\NotSupportedException
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function buildField($field)
	{
		if (!$this->class->hasField($field)) {
			if (!$this->class->hasAssociation($field)) {
				throw new \Nette\InvalidArgumentException("Given name '$field' is not entity field.");
				}
			throw new \Nette\NotSupportedException("Association container for '$field' cannot be auto-generated.");
			}

		switch ($this->class->getTypeOfField($field)) {
			case Type::BIGINT:
			case Type::DECIMAL:
			case Type::INTEGER:
			case Type::SMALLINT:
			case Type::STRING:
			case Type::FLOAT:
				return $this->container->addText($field, $field);
			case Type::DATE:
				return $this->container->addDate($field, $field);
			case Type::TIME:
				return $this->container->addTime($field, $field);
			case Type::DATETIME:
			case Type::DATETIMETZ:
				return $this->container->addDatetime($field, $field);
			case Type::TEXT:
			case Type::BLOB:
				return $this->container->addTextArea($field, $field);
			case Type::BOOLEAN:
				return $this->container->addCheckbox($field, $field);
			default:
				throw new \Nette\NotSupportedException("Form type for '$field' cannot be resolved.");
			}
	}

	/**
	 * @param \Nette\Forms\Controls\BaseControl $control
	 */
	public function buildValidations(\Nette\Forms\Controls\BaseControl $control)
	{
		$mapping=$this->class->getFieldMapping($field=$control->getName());
		switch ($this->class->getTypeOfField($field)) {
			case Type::BIGINT:
			case Type::INTEGER:
			case Type::SMALLINT:
			case Type::DECIMAL:
				$control->addCondition(Form::FILLED)
					->addRule(Form::NUMERIC);
				break;
			case Type::STRING:
				$control->addCondition(Form::FILLED)
					->addRule(Form::MAX_LENGTH, NULL, $mapping['length'] ?: 255);
				break;
			case Type::TEXT:
				if ($mapping['length']) {
					$control->addCondition(Form::FILLED)
						->addRule(Form::MAX_LENGTH, NULL, $mapping['length']);
					}
				break;
			case Type::FLOAT:
				$control->addCondition(Form::FILLED)
					->addRule(Form::FLOAT);
				break;
			}

		if (!$mapping['nullable']) {
			$control->setRequired();
			}
	}
}
