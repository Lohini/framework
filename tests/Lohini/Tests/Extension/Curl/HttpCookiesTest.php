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

use Lohini\Extension\Curl\HttpCookies;

/**
 */
class HttpCookiesTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @return array
	 */
	public function dataCookies()
	{
		$yesterday=date_create()->modify('-1 day')->format(HttpCookies::COOKIE_DATETIME);
		$tomorrow=date_create()->modify('+1 day')->format(HttpCookies::COOKIE_DATETIME);

		return array(
			'lohini=is+awesome; expires='.$tomorrow,
			'nette=is+awesome; expires='.$tomorrow,
			'array[one]=Lister; expires='.$tomorrow.'; path=/; secure',
			'array[two]=Rimmer; expires='.$tomorrow.'; path=/; secure; httponly',
			'symfony=is+ok; expires='.$yesterday,
			);
	}

	public function testRead()
	{
		$cookies=new HttpCookies($this->dataCookies());
		$this->assertEquals(
			HttpCookies::from(
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
			$cookies
			);
	}

	public function testCompile()
	{
		$cookies=new HttpCookies($this->dataCookies());

		$expected='lohini=is+awesome; nette=is+awesome; array[one]=Lister; array[two]=Rimmer';
		$this->assertEquals($expected, $cookies->compile());
		$this->assertEquals($cookies->compile(), (string)$cookies);
	}
}
