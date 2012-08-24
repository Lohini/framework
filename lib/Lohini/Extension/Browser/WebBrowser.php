<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Browser;
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
class WebBrowser
extends \Nette\Object
{
	/** @var Curl\CurlSender */
	private $curl;
	/** @var array */
	private $defaultHeaders=array(
		'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Charset' => 'windows-1250,utf-8;q=0.7,*;q=0.3',
		//'Accept-Encoding' => 'gzip,deflate,sdch',
		'Accept-Language' => 'cs',
		'Cache-Control' => 'max-age=0',
		'Connection' => 'keep-alive',
		);


	/**
	 * @param Curl\CurlSender $curl
	 */
	public function __construct(Curl\CurlSender $curl=NULL)
	{
		$this->curl= $curl ?: new Curl\CurlSender;
		$this->curl->headers+=$this->defaultHeaders;
		$this->curl->setUserAgent('Chrome');
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function setHeader($name, $value)
	{
		$this->curl->headers[$name]=$value;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->curl->setUserAgent($name);
	}

	/**
	 * @return BrowserSession
	 */
	public function createSession()
	{
		return new BrowserSession($this);
	}

	/**
	 * @param string $link
	 * @return WebPage
	 */
	public function open($link)
	{
		return $this->createSession()->open($link);
	}

	/**
	 * @param Curl\Request $request
	 * @return Curl\HtmlResponse
	 */
	public function send(Curl\Request $request)
	{
		return $this->curl->send($request);
	}

	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array();
	}
}
