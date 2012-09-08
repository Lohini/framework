<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Curl;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * @property-read array $headers
 * @property-read Response|NULL $previous
 * @property-read string $response
 * @property-read array $cookies
 * @property-read array $info
 */
class Response
extends \Nette\Object
{
	/** @var array */
	private $headers;
	/** @var array */
	private $cookies=array();
	/** @var Response */
	private $previous;
	/** @var CurlWrapper */
	protected $curl;


	/**
	 * @param CurlWrapper $curl
	 * @param array $headers
	 */
	public function __construct(CurlWrapper $curl, array $headers)
	{
		$this->curl=$curl;
		$this->headers=$headers;

		if (isset($headers['Set-Cookie'])) {
			// Set-Cookie is parsed in CurlWrapper to object
			$this->cookies=(array)$headers['Set-Cookie'];
			}
	}

	/**
	 * @param Response $previous
	 * @return Response (fluent)
	 */
	public function setPrevious(Response $previous=NULL)
	{
		$this->previous=$previous;
		return $this;
	}

	/**
	 * @return Response|NULL
	 */
	public function getPrevious()
	{
		return $this->previous;
	}

	/**
	 * @return string
	 */
	public function getResponse()
	{
		return $this->curl->response;
	}

	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getUrl()
	{
		return $this->curl->getUrl();
	}

	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @return array
	 */
	public function getCookies()
	{
		return $this->cookies;
	}

	/**
	 * @return array
	 */
	public function getInfo()
	{
		return $this->curl->info;
	}

	/**
	 * @param CurlWrapper $curl
	 * @return array
	 * @throws CurlException
	 */
	public static function stripHeaders(CurlWrapper $curl)
	{
		$curl->responseHeaders=substr($curl->response, 0, $headerSize=$curl->info['header_size']);
		if (!$headers=CurlWrapper::parseHeaders($curl->responseHeaders)) {
			throw new CurlException('Failed parsing of response headers');
			}

		$curl->response=substr($curl->response, $headerSize);
		return $headers;
	}
}
