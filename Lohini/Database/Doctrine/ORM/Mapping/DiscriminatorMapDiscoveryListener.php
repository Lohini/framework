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

class DiscriminatorMapDiscoveryListener
extends \Nette\Object
implements \Doctrine\Common\EventSubscriber
{
	/** @var \Doctrine\Common\Annotations\Reader */
	private $annotationsReader;


	/**
	 * @param \Doctrine\Common\Annotations\Reader $reader
	 */
	public function __construct(\Doctrine\Common\Annotations\Reader $reader)
	{
		$this->annotationsReader=$reader;
	}

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			\Doctrine\ORM\Events::loadClassMetadata,
			);
	}

	/**
	 * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
	 */
	public function loadClassMetadata(\Doctrine\ORM\Event\LoadClassMetadataEventArgs $args)
	{
		$meta=$args->getClassMetadata();
		$entry=$this->annotationsReader->getClassAnnotation(
				$meta->getReflectionClass(),
				'Doctrine\ORM\Mapping\DiscriminatorEntry'
				);

		if ($entry===NULL) {
			return;
			}

		$em=$args->getEntityManager();
		foreach ($meta->parentClasses as $parent) {
			$parentMeta=$em->getClassMetadata($parent);
			$map=$parentMeta->discriminatorMap+array($entry->name => $meta->name);

			if ($parentMeta->inheritanceType===\Doctrine\ORM\Mapping\ClassMetadataInfo::INHERITANCE_TYPE_NONE) {
				continue;
				}

			$parentMeta->setDiscriminatorMap($map);
			$meta->setDiscriminatorMap($map);
			$parentMeta->subClasses=array_unique($parentMeta->subClasses);
			}
	}
}
