<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\Curl;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Extension\Curl;

/**
 */
class CurlWrapperTest
extends \Lohini\Testing\TestCase
{
	const TEST_PATH='http://test.lohini.net';

	/** @var string */
	private $tempFile;


	public function setUp()
	{
		$this->skipIfNoInternet();
	}

	protected function tearDown()
	{
		if ($this->tempFile && file_exists($this->tempFile)) {
			@unlink($this->tempFile);
			}
	}

	public function testGet()
	{
		$curl=new Curl\CurlWrapper(static::TEST_PATH.'/get.php');

		$this->assertTrue($curl->execute());
		$this->assertEquals($this->dumpVar(array()), $curl->response);
	}

	public function testPost()
	{
		$curl=new Curl\CurlWrapper(static::TEST_PATH.'/post.php', Curl\Request::POST);
		$curl->setPost($post=array('hi' => 'hello'));

		$this->assertTrue($curl->execute());
		$this->assertEquals($this->dumpVar($post).$this->dumpVar(array()), $curl->response);
	}

	public function testPostFiles()
	{
		$curl=new Curl\CurlWrapper(static::TEST_PATH.'/post.php', Curl\Request::POST);
		$curl->setPost($post=array('hi' => 'hello'), $files=array('txt' => $this->tempFile()));
		$this->assertTrue($curl->execute());
		$this->assertStringMatchesFormat($this->dumpVar($post).$this->dumpPostFiles($files), $curl->response);
	}

	public function testGet_Cookies()
	{
		$curl=new Curl\CurlWrapper(static::TEST_PATH.'/cookies.php');
		$curl->setOption('header', TRUE);
		$this->assertTrue($curl->execute());

		$headers=Curl\Response::stripHeaders($curl);
		$this->assertEquals(
			Curl\HttpCookies::from(
				array(
					'lohini' => 'is awesome',
					'nette' => 'is awesome',
					'array' => array(
						'one' => 'Lister',
						'two' => 'Rimmer'
						),
					),
				FALSE
				),
			$headers['Set-Cookie']
			);
	}

	/**
	 * @param mixed $variable
	 * @return string
	 */
	private function dumpVar($variable)
	{
		ob_start();
		print_r($variable);
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	private function tempFile()
	{
		$this->tempFile=$this->getContext()->expand('%tempDir%').'/curl-test.txt';
		file_put_contents($this->tempFile, 'ping');
		@chmod($this->tempFile, 0755);
		return $this->tempFile;
	}

	/**
	 * @param array $files
	 * @return string
	 */
	private function dumpPostFiles($files)
	{
		array_walk_recursive(
			$files,
			function(&$input, $key) {
				$input=array(
					'name' => basename($input),
					'type' => '%s',
					'tmp_name' => '%s',
					'error' => '0',
					'size' => filesize($input),
					);
			}
			);

		return $this->dumpVar($files);
	}
}
