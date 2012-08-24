<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\Redis;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Caching\Cache;

/**
 */
class RedisJournalTest
extends \Lohini\Testing\TestCase
{
	/** @var \Lohini\Extension\Redis\RedisClient */
	private $client;
	/** @var \Lohini\Extension\Redis\RedisJournal */
	private $journal;


	protected function setUp()
	{
		$this->client=new \Lohini\Extension\Redis\RedisClient;
		try {
			$this->client->connect();
			$this->journal=new \Lohini\Extension\Redis\RedisJournal($this->client);
			}
		catch (\Lohini\Extension\Redis\RedisClientException $e) {
			$this->markTestSkipped($e->getMessage());
			}

		$this->client->flushDb();
	}

	public function testRemoveByTag()
	{
		$this->journal->write(
			'ok_test1',
			array(Cache::TAGS => array('test:homepage'))
			);

		$result=$this->journal->clean(array(Cache::TAGS => array('test:homepage')));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test1', $result[0]);
	}

	public function testRemovingByMultipleTags_OneIsNotDefined()
	{
		$this->journal->write(
			'ok_test2',
			array(Cache::TAGS => array('test:homepage', 'test:homepage2'))
			);

		$result=$this->journal->clean(array(Cache::TAGS => array('test:homepage2')));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test2', $result[0]);
	}

	public function testRemovingByMultipleTags_BothAreOnOneEntry()
	{
		$this->journal->write(
			'ok_test2b',
			array(Cache::TAGS => array('test:homepage', 'test:homepage2'))
			);

		$result=$this->journal->clean(array(Cache::TAGS => array('test:homepage', 'test:homepage2')));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test2b', $result[0]);
	}

	public function testRemoveByMultipleTags_TwoSameTags()
	{
		$this->journal->write(
			'ok_test2c',
			array(Cache::TAGS => array('test:homepage', 'test:homepage'))
			);

		$result=$this->journal->clean(array(Cache::TAGS => array('test:homepage', 'test:homepage')));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test2c', $result[0]);
	}

	public function testRemoveByTagAndPriority()
	{
		$this->journal->write(
			'ok_test2d',
			array(
				Cache::TAGS => array('test:homepage'),
				Cache::PRIORITY => 15
				)
			);

		$result=$this->journal->clean(array(Cache::TAGS => array('test:homepage'), Cache::PRIORITY => 20));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test2d', $result[0]);
	}

	public function testRemoveByPriority()
	{
		$this->journal->write(
			'ok_test3',
			array(Cache::PRIORITY => 10)
			);

		$result=$this->journal->clean(array(Cache::PRIORITY => 10));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test3', $result[0]);
	}

	public function testPriorityAndTag_CleanByTag()
	{
		$this->journal->write(
			'ok_test4',
			array(
				Cache::TAGS => array('test:homepage'),
				Cache::PRIORITY => 10
				)
			);

		$result=$this->journal->clean(array(Cache::TAGS => array('test:homepage')));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test4', $result[0]);
	}

	public function testPriorityAndTag_CleanByPriority()
	{
		$this->journal->write(
			'ok_test5',
			array(
				Cache::TAGS => array('test:homepage'),
				Cache::PRIORITY => 10,
				)
			);

		$result=$this->journal->clean(array(Cache::PRIORITY => 10));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test5', $result[0]);
	}

	public function testMultipleWritesAndMultipleClean()
	{
		for ($i=1; $i<=10; $i++) {
			$this->journal->write(
				'ok_test6_'.$i,
				array(
					Cache::TAGS => array('test:homepage', 'test:homepage/'.$i),
					Cache::PRIORITY => $i
					)
				);
		}

		$result=$this->journal->clean(array(Cache::PRIORITY => 5));
		$this->assertCount(5, $result, 'clean priority lower then 5');
		$this->assertSame('ok_test6_1', $result[0], 'clean priority lower then 5');

		$result=$this->journal->clean(array(Cache::TAGS => array('test:homepage/7')));
		$this->assertCount(1, $result, 'clean tag homepage/7');
		$this->assertSame('ok_test6_7', $result[0], 'clean tag homepage/7');

		$result=$this->journal->clean(array(Cache::TAGS => array('test:homepage/4')));
		$this->assertCount(0, $result, 'clean non exists tag');

		$result=$this->journal->clean(array(Cache::PRIORITY => 4));
		$this->assertCount(0, $result, 'clean non exists priority');

		$result=$this->journal->clean(array(Cache::TAGS => array('test:homepage')));
		$this->assertCount(4, $result, 'clean other');
		$this->assertSame('ok_test6_6', $result[0], 'clean other');
	}

	public function testSpecialChars()
	{
		$this->journal->write(
			'ok_test7ščřžýáíé',
			array(Cache::TAGS => array('čšřýýá', 'ýřžčýž/10'))
			);

		$result=$this->journal->clean(array(Cache::TAGS => array('čšřýýá')));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test7ščřžýáíé', $result[0]);
	}

	public function testDuplicates_SameTag()
	{
		$this->journal->write(
			'ok_test_a',
			array(Cache::TAGS => array('homepage'))
			);

		$this->journal->write(
			'ok_test_a',
			array(Cache::TAGS => array('homepage'))
			);

		$result=$this->journal->clean(array(Cache::TAGS => array('homepage')));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test_a', $result[0]);
	}

	public function testDuplicates_SamePriority()
	{
		$this->journal->write(
			'ok_test_b',
			array(Cache::PRIORITY => 12)
			);

		$this->journal->write(
			'ok_test_b',
			array(Cache::PRIORITY => 12)
			);

		$result=$this->journal->clean(array(Cache::PRIORITY => 12));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test_b', $result[0]);
	}

	public function testDuplicates_DifferentTags()
	{
		$this->journal->write(
			'ok_test_ba',
			array(Cache::TAGS => array('homepage'))
			);

		$this->journal->write(
			'ok_test_ba',
			array(Cache::TAGS => array('homepage2'))
			);

		$result=$this->journal->clean(array(Cache::TAGS => array('homepage')));
		$this->assertCount(0, $result);

		$result2=$this->journal->clean(array(Cache::TAGS => array('homepage2')));
		$this->assertCount(1, $result2);
		$this->assertSame('ok_test_ba', $result2[0]);
	}

	public function testDuplicates_DifferentPriorities()
	{
		$this->journal->write(
			'ok_test_bb',
			array(Cache::PRIORITY => 15)
			);

		$this->journal->write(
			'ok_test_bb',
			array(Cache::PRIORITY => 20)
			);

		$result=$this->journal->clean(array(Cache::PRIORITY => 30));
		$this->assertCount(1, $result);
		$this->assertSame('ok_test_bb', $result[0]);
	}

	public function testCleanAll()
	{
		$this->journal->write(
			'ok_test_all_tags',
			array(Cache::TAGS => array('test:all', 'test:all'))
			);

		$this->journal->write(
			'ok_test_all_priority',
			array(Cache::PRIORITY => 5)
			);

		$result=$this->journal->clean(array(Cache::ALL => TRUE));
		$this->assertNull($result);

		$result2=$this->journal->clean(array(Cache::TAGS => 'test:all'));
		$this->assertEmpty($result2);
	}
}
