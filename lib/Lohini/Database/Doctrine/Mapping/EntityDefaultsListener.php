<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Mapping;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class EntityDefaultsListener
extends \Nette\Object
implements \Doctrine\Common\EventSubscriber
{
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
	 * @throws \Nette\InvalidStateException
	 */
	public function loadClassMetadata(\Doctrine\ORM\Event\LoadClassMetadataEventArgs $args)
	{
		$meta=$args->getClassMetadata();
		if ($meta->isMappedSuperclass) {
			return;
			}

		if (!$meta->customRepositoryClassName) {
			$meta->setCustomRepositoryClass('Lohini\Database\Doctrine\Dao');
			}

		$refl=new \Nette\Reflection\ClassType($meta->customRepositoryClassName);
		if (!$refl->implementsInterface('Lohini\Persistence\IDao')) {
			throw new \Nette\InvalidStateException("Your repository class for entity '$meta->name' should extend 'Lohini\\Database\\Doctrine\\Dao'.");
			}
	}
}
