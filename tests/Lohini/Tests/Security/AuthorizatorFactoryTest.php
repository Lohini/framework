<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Security;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class AuthorizatorFactoryTest
extends \Lohini\Testing\OrmTestCase
{
	/** @var \Lohini\Security\AuthorizatorFactory */
	private $factory;
	/** @var \Lohini\Security\User */
	private $user;
	/** @var \Nette\Security\IUserStorage|\PHPUnit_Framework_MockObject_MockObject */
	private $userStorage;
	/** @var \Nette\DI\Container */
	private $userContext;
	/** @var \Nette\Http\Session|\PHPUnit_Framework_MockObject_MockObject */
	private $session;


	public function setUp()
	{
		$this->createOrmSandbox(array(
			'Lohini\Security\Identity',
			'Lohini\Security\RBAC\BasePermission',
			'Lohini\Security\RBAC\RolePermission',
			'Lohini\Security\RBAC\UserPermission',
			));

		// mock session
		$this->session=$this->getMockBuilder('Nette\Http\Session')
			->disableOriginalConstructor()->getMock();

		// create factory
		$this->factory=new \Lohini\Security\AuthorizatorFactory(
			$this->user=new \Lohini\Security\User(
				$this->userStorage=new \Lohini\Security\SimpleUserStorage,
				$this->userContext=new \Nette\DI\Container,
				$this->getDoctrine()
				),
			$this->session,
			$this->getDoctrine()
			);

		// register authenticator
		$this->userContext->classes['nette\security\iauthenticator']='authenticator';
		$this->userContext->addService('authenticator', $this->user);
	}

	/**
	 * @param string $divisionName
	 * @param string $username
	 * @return \Lohini\Security\User
	 */
	private function prepareUserWithPermission($divisionName, $username)
	{
		$division=$this->getDao('Lohini\Security\RBAC\Division')->findOneBy(array('name' => $divisionName));
		$identity=$this->getDao('Lohini\Security\Identity')->findOneBy(array('username' => $username));

		// build permission object
		$permission=$this->factory->create($identity, $division);
		$this->assertInstanceOf('Nette\Security\IAuthorizator', $permission);

		// prepare user storage
		$this->userStorage->setIdentity($identity);
		$this->userStorage->setAuthenticated(TRUE);

		// set authorizator service
		$this->userContext->classes['nette\security\iauthorizator']='authorizator';
		$this->userContext->addService('authorizator', $permission);

		return $this->user;
	}

	/**
	 * @group database
	 * @Fixture('RBAC\Fixture\AclData')
	 */
	public function testPermissionsOfLopoForBlog()
	{
		$user=$this->prepareUserWithPermission('blog', 'Lopo');

		$this->assertTrue($user->isAllowed('article', 'access'));
		$this->assertTrue($user->isAllowed('article', 'view'));
		$this->assertTrue($user->isAllowed('comment', 'access'));
		$this->assertTrue($user->isAllowed('comment', 'view'));

		$this->assertFalse($user->isAllowed('article', 'delete'));
		$this->assertFalse($user->isAllowed('article', 'edit'));
		$this->assertFalse($user->isAllowed('comment', 'delete'));
		$this->assertFalse($user->isAllowed('comment', 'edit'));
	}

	/**
	 * @group database
	 * @Fixture('RBAC\Fixture\AclData')
	 */
	public function testPermissionsOfClientForAdmin()
	{
		$user=$this->prepareUserWithPermission('administration', 'macho-client');

		$this->assertTrue($user->isAllowed('article', 'access'));
		$this->assertTrue($user->isAllowed('article', 'view'));
		$this->assertTrue($user->isAllowed('identity', 'access'));
		$this->assertTrue($user->isAllowed('identity', 'view'));

		$this->assertFalse($user->isAllowed('article', 'delete'));
		$this->assertFalse($user->isAllowed('article', 'edit'));
		$this->assertFalse($user->isAllowed('identity', 'delete'));
		$this->assertFalse($user->isAllowed('identity', 'edit'));
	}

	/**
	 * @group database
	 * @Fixture('RBAC\Fixture\AclData')
	 */
	public function testPermissionsOfClientForForum()
	{
		$user=$this->prepareUserWithPermission('forum', 'macho-client');

		$this->assertTrue($user->isAllowed('thread', 'access'));
		$this->assertTrue($user->isAllowed('thread', 'view'));

		$this->assertFalse($user->isAllowed('thread', 'delete'));
		$this->assertFalse($user->isAllowed('thread', 'edit'));
		$this->assertFalse($user->isAllowed('thread', 'create'));
	}
}
