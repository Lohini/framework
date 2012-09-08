<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine\Entities;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping as ORM;

/**
 */
class IdentifiedEntityTest
extends \Lohini\Testing\OrmTestCase
{
	protected function setUp()
	{
		$this->createOrmSandbox(array(__NAMESPACE__.'\Foo'));
	}

	public function testProxyProvidesIdentity()
	{
		$dao=$this->getDao(__NAMESPACE__.'\Foo');
		$dao->save(array(
			new Foo('Mladinká, ale řádně vyvinutá modelka Kate Upton'),
			new Foo($dancingName='hříšně tančila jen v miniaturních bikinách')
			));
		$this->getEntityManager()->clear();

		// ...

		/** @var \Lohini\Tests\Database\Doctrine\Entities\Foo|\Doctrine\ORM\Proxy\Proxy $dancing */
		$dancing=$dao->getReference(2);
		$this->assertInstanceOf('Doctrine\ORM\Proxy\Proxy', $dancing);
		$this->assertEquals(2, $dancing->getId());
		$this->assertFalse($dancing->__isInitialized__); // proxy property
		$this->assertEquals($dancingName, $dancing->name);
		$this->assertTrue($dancing->__isInitialized__); // proxy property
	}
}


/**
 * @ORM\Entity()
 */
class Foo
extends \Lohini\Database\Doctrine\Entities\IdentifiedEntity
{
	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name=$name;
	}
}
