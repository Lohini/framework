<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine\Tools;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Database\Doctrine\Tools\NonLockingUniqueInserter;

/**
 */
class NonLockingUniqueInserterTest
extends \Lohini\Testing\OrmTestCase
{
	public function setup()
	{
		$this->createOrmSandbox(array(
			'Lohini\Tests\Database\Doctrine\Tools\EntityWithUniqueColumns'
			));
	}

	/**
	 * @group database
	 */
	public function testValidInsert()
	{
		$em=$this->getEntityManager();

		$entity=new EntityWithUniqueColumns;
		$entity->email='lopo@lohini.net';
		$entity->name='Filip';
		$entity->address='Starovičky';

		$inserter=new NonLockingUniqueInserter($em);
		$this->assertTrue($inserter->persist($entity));
		$this->assertTrue($em->isOpen());

		$em->clear();

		$this->assertEntityValues(
			get_class($entity),
			array(
				'email' => 'lopo@lohini.net',
				'name' => "Filip",
				'address' => 'Starovičky',
				),
			$entity->id
			);
	}

	/**
	 * @group database
	 */
	public function testInValidInsert()
	{
		$em=$this->getEntityManager();
		$em->persist(new EntityWithUniqueColumns(array('email' => 'lopo@lohini.net', 'name' => 'Filip')));
		$em->flush();
		$em->clear();

		$entity=new EntityWithUniqueColumns();
		$entity->email='lopo@lohini.net';
		$entity->name='Filip';
		$entity->address='Starovičky';

		$inserter=new NonLockingUniqueInserter($em);
		$this->assertFalse($inserter->persist($entity));
		$this->assertTrue($em->isOpen());
	}
}
