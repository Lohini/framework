<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Mapping;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Reflection\ClassType;

/**
 */
class DiscriminatorMapDiscoveryListener
extends \Nette\Object
implements \Lohini\Extension\EventDispatcher\EventSubscriber
{
	/** @var \Doctrine\Common\Annotations\Reader */
	private $reader;
	/** @var \Doctrine\ORM\Mapping\Driver\Driver */
	private $driver;


	/**
	 * @param \Doctrine\Common\Annotations\Reader $reader
	 */
	public function __construct(\Doctrine\Common\Annotations\Reader $reader)
	{
		$this->reader=$reader;
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
		$this->driver=$args->getEntityManager()->getConfiguration()->getMetadataDriverImpl();

		if ($meta->isInheritanceTypeNone()) {
			return;
			}

		$map=$meta->discriminatorMap;
		foreach ($this->getChildClasses($meta->name) as $className) {
			if (!in_array($className, $meta->discriminatorMap) && $entry=$this->getEntryName($className)) {
				$map[$entry->name]=$className;
				}
			}

		$meta->setDiscriminatorMap($map);
		$meta->subClasses=array_unique($meta->subClasses);
	}

	/**
	 * @param string $currentClass
	 * @return array
	 */
	private function getChildClasses($currentClass)
	{
		$classes=array();
		foreach ($this->driver->getAllClassNames() as $className) {
			if (!ClassType::from($className)->isSubclassOf($currentClass)) {
				continue;
				}

			$classes[]=$className;
			}
		return $classes;
	}

	/**
	 * @param string $className
	 * @return object|NULL
	 */
	private function getEntryName($className)
	{
		return $this->reader->getClassAnnotation(
			ClassType::from($className),
			'Lohini\Database\Doctrine\Mapping\DiscriminatorEntry'
			) ?: NULL;
	}
}
