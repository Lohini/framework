<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Security\RBAC\Fixture;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class AclData
extends \Doctrine\Common\DataFixtures\AbstractFixture
{
	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager|\Doctrine\ORM\EntityManager $manager
	 */
	public function load(\Doctrine\Common\Persistence\ObjectManager $manager)
	{
		$acl=\Nette\Utils\Neon::decode(file_get_contents(__DIR__.'/AclData.neon'));
		$builder=new \Lohini\Security\RBAC\UnitBuilder($acl);
		$builder->build();
		$builder->persist($manager);
		$manager->clear();
	}
}
