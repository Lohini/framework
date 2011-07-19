<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\ORM\Mapping;
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

class ClassMetadata
extends \Doctrine\ORM\Mapping\ClassMetadata
{
	/**
	 * The name of the custom repository class used for the entity class.
	 * (Optional).
	 *
	 * @var string
	 */
	public $customRepositoryClassName='Lohini\Database\Doctrine\ORM\EntityRepository';
	/** @var string */
	public $serviceClassName='Lohini\Database\Doctrine\ORM\BaseService';


	/**
	 * Registers a service class for the entity class.
	 *
	 * @param string
	 */
	public function setServiceClass($serviceClassName)
	{
		$this->serviceClassName=$serviceClassName;
	}

	/**
	 * Determines which fields get serialized.
	 *
	 * It is only serialized what is necessary for best unserialization performance.
	 * That means any metadata properties that are not set or empty or simply have
	 * their default value are NOT serialized.
	 *
	 * Parts that are also NOT serialized because they can not be properly unserialized:
	 *      - reflClass (ReflectionClass)
	 *      - reflFields (ReflectionProperty array)
	 *
	 * @return array The names of all the fields that should be serialized.
	 */
	public function __sleep()
	{
		$serialized=parent::__sleep();
		$serialized[]='serviceClassName';

		return $serialized;
	}
}
