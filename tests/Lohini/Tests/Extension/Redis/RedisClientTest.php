<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\Redis;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class RedisClientTest
extends \Lohini\Testing\TestCase
{
	/** @var \Lohini\Extension\Redis\RedisClient */
	private $client;
	/** @var string */
	private $ns;


	protected function setUp()
	{
		$this->client=new \Lohini\Extension\Redis\RedisClient;
		try {
			$this->client->connect();
			}
		catch (\Lohini\Extension\Redis\RedisClientException $e) {
			$this->markTestSkipped($e->getMessage());
			}

		$this->ns=\Nette\Utils\Strings::random();
	}

	public function testPrimitives()
	{
		$secret="I'm batman";
		$key=$this->ns.'redis-test-secred';

		$this->client->set($key, $secret);
		$this->client->expire($key, 10);

		$this->assertSame($secret, $this->client->get($key));
	}

	public function testLargeData()
	{
		$data=str_repeat('Lohini', 1e6);
		$this->client->set('large', $data);
		$this->assertSame($data, $this->client->get('large'));
	}
}
