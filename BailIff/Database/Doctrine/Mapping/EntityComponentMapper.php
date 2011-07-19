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

abstract class EntityComponentMapper
extends \Nette\Object
{
	/** @var \SplObjectStorage */
	private $assignment;
	/** @var \BailIff\Database\Doctrine\Workspace */
	private $workspace;
	/** @var TypeMapper */
	private $typeMapper;


	/**
	 * @param \BailIff\Database\Doctrine\Workspace $workspace
	 */
	public function __construct(\BailIff\Database\Doctrine\Workspace $workspace)
	{
		$this->workspace=$workspace;
		$this->assignment=new \SplObjectStorage;
	}

	/**
	 * @param string|object $entity
	 * @return \Doctrine\ORM\Mapping\ClassMetadata
	 */
	public function getMetadata($entity)
	{
		$entity=is_object($entity)? get_class($entity) : $entity;
		return $this->workspace->getClassMetadata($entity);
	}

	/**
	 * @return \BailIff\Database\Doctrine\Mapping\TypeMapper
	 */
	protected function doCreateTypeMapper()
	{
		return new TypeMapper;
	}

	/**
	 * @return \BailIff\Database\Doctrine\Mapping\TypeMapper
	 */
	public function getTypeMapper()
	{
		if ($this->typeMapper===NULL) {
			$this->typeMapper=$this->doCreateTypeMapper();
			}
		return $this->typeMapper;
	}

	/**
	 * @param object $entity
	 * @param \Nette\ComponentModel\IComponent $component
	 * @return \BailIff\Database\Doctrine\Mapping\EntityComponentMapper (fluent)
	 */
	public function assing($entity, \Nette\ComponentModel\IComponent $component)
	{
		$this->assignment->attach($entity, $component);
		return $this;
	}

	/**
	 * @return \SplObjectStorage
	 */
	public function getAssignment()
	{
		return $this->assignment;
	}

	/**
	 * @param object $entity
	 * @return \Nette\ComponentModel\IComponent
	 */
	public function getComponent($entity)
	{
		if (!$this->assignment->contains($entity)) {
			return NULL;
			}
		return $this->assignment->offsetGet($entity);
	}

	/************************ fields ************************/
	/**
	 * @param object $entity
	 * @param string $property
	 * @param array|\Nette\ArrayHash $data
	 */
	public function loadProperty($entity, $property, $data)
	{
		$meta=$this->getMetadata($entity);
		$propMapping=$meta->getFieldMapping($property);
		$propRef=$meta->getReflectionProperty($propMapping['fieldName']);

		$data=$this->getTypeMapper()->load($propRef->getValue($entity), $data, $propMapping['type']);
		$propRef->setValue($entity, $data);
	}

	/**
	 * @param object $entity
	 * @param string $property
	 * @return array
	 */
	public function saveProperty($entity, $property)
	{
		$meta=$this->getMetadata($entity);
		$propMapping=$meta->getFieldMapping($property);
		$propRef=$meta->getReflectionProperty($propMapping['fieldName']);

		return $this->getTypeMapper()->save($propRef->getValue($entity), $propMapping['type']);
	}

	/**
	 * @param object $entity
	 * @param string $property
	 */
	public function hasProperty($entity, $property)
	{
		$meta=$this->getMetadata($entity);
		return isset($meta->fieldMappings[$property]);
	}

	/************************ associations ************************/
	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return bool
	 */
	public function hasAssocation($entity, $assocation)
	{
		return isset($this->getMetadata($entity)->associationMappings[$assocation]);
	}

	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return object
	 */
	public function getAssocation($entity, $assocation)
	{
		$meta=$this->getMetadata($entity);
		$propMapping=$meta->getAssociationMapping($assocation);
		$propRef=$meta->getReflectionProperty($assocation);

		return $propRef->getValue($entity);
	}

	/************************ load & save to component ************************/
	/**
	 * @return array
	 */
	abstract public function load();

	/**
	 * @return array
	 */
	abstract public function save();
}
