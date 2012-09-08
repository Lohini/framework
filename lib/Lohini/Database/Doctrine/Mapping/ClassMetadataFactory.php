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

/**
 * @method \Lohini\Database\Doctrine\Mapping\ClassMetadata getMetadataFor($className)
 * @method \Lohini\Database\Doctrine\Mapping\ClassMetadata[] getAllMetadata()
 */
class ClassMetadataFactory
extends \Doctrine\ORM\Mapping\ClassMetadataFactory
{
	/**
	 * Enforce Nette\Reflection
	 */
	public function __construct()
	{
		$this->setReflectionService(new RuntimeReflectionService);
	}

	/**
	 * @param string|object $entity
	 * @return bool
	 */
	public function isAudited($entity)
	{
		$class= $this->getMetadataFor(is_object($entity)? get_class($entity) : $entity);
		return $class->isAudited();
	}

	/**
	 * @return \Lohini\Database\Doctrine\Mapping\ClassMetadata[]
	 */
	public function getAllAudited()
	{
		return array_filter(
			$this->getAllMetadata(),
			function(ClassMetadata $class) { return $class->isAudited(); }
			);
	}

	/**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return ClassMetadata
     */
    protected function newClassMetadataInstance($className)
    {
        return new ClassMetadata($className);
    }
}
