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

use Nette\Http\IRequest,
	Nette\Http\UrlScript;

/**
 * @method Request setUrl(string $url)
 * @method Request setMethod(string $url)
 */
class Request
extends RequestOptions
{
	/**#@+ HTTP Request method */
	const GET=IRequest::GET;
	const POST=IRequest::POST;
	const PUT=IRequest::PUT;
	const HEAD=IRequest::HEAD;
	const DELETE=IRequest::DELETE;
	const DOWNLOAD='DOWNLOAD';
	/**#@- */
	/**#@+ verify host for certificates */
	const VERIFYHOST_NO=0;
	const VERIFYHOST_COMMON=1;
	const VERIFYHOST_MATCH=2;
	/**#@- */

	/** @var \Nette\Http\UrlScript */
	public $url;
	/** @var string */
	public $method=self::GET;
	/** @var array */
	public $headers=array();
	/** @var array name => value */
	public $cookies=array();
	/** @var array|string */
	public $post=array();
	/** @var array */
	public $files=array();
	/** @var CurlSender */
	private $sender;


	/**
	 * @param string $url
	 * @param array|string $post
	 */
	public function __construct($url, $post=array())
	{
		$this->setUrl($url);
		$this->post = $post;
	}

	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getUrl()
	{
		if (!$this->url instanceof UrlScript) {
			$this->url=new UrlScript($this->url);
			}
		return $this->url;
	}

	/**
	 * @return HttpCookies
	 */
	public function getCookies()
	{
		return new HttpCookies($this->cookies);
	}

	/**
	 * @param string $method
	 * @return bool
	 */
	public function isMethod($method)
	{
		return $this->method===$method;
	}

	/**
	 * @param CurlSender $sender
	 * @return Request (fluent)
	 */
	public function setSender(CurlSender $sender)
	{
		$this->sender=$sender;
		return $this;
	}

	/**
	 * @return Response
	 */
	public function send()
	{
		if ($this->sender===NULL) {
			$this->sender=new CurlSender;
			}

		return $this->sender->send($this);
	}

	/**
	 * @param array|string $query
	 * @return Response
	 */
	public function get($query=NULL)
	{
		$this->method=static::GET;
		$this->post= $this->files= array();
		$this->getUrl()->appendQuery($query);
		return $this->send();
	}

	/**
	 * @param array|string $post
	 * @param array $files
	 * @return Response
	 */
	public function post($post=array(), array $files=NULL)
	{
		$this->method=static::POST;
		$this->post=$post;
		$this->files=(array)$files;
		return $this->send();
	}

	/**
	 * @param array|string $post
	 * @return Response
	 */
	public function put($post=array())
	{
		$this->method=static::PUT;
		$this->post=$post;
		$this->files=array();
		return $this->send();
	}

	/**
	 * @return Response
	 */
	public function delete()
	{
		$this->method=static::DELETE;
		$this->post= $this->files= array();
		return $this->send();
	}

	/**
	 * @param array|string $post
	 * @return Response
	 */
	public function download($post=array())
	{
		$this->method=static::DOWNLOAD;
		$this->post=$post;
		return $this->send();
	}

	/**
	 * Creates new request that can follow requested location
	 *
	 * @param Response $response
	 * @return Request
	 */
	final public function followRedirect(Response $response)
	{
		$request=clone $this;
		$request->setMethod(Request::GET);
		$request->post= $request->files= array();
		$request->cookies=$response->getCookies()+$request->cookies;
		$request->setUrl(static::fixUrl($request->getUrl(), $response->headers['Location']));
		return $request;
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return \Nette\ObjectMixin::callProperty($this, $name, $args);
	}

	/**
	 * Clones the url
	 */
	public function __clone()
	{
		if ($this->url instanceof UrlScript) {
			$this->url=clone $this->url;
			}
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @return UrlScript
	 * @throws InvalidUrlException
	 */
	public static function fixUrl($from, $to)
	{
		$lastUrl=new UrlScript($from);
		$url=new UrlScript($to);

		if (!$to instanceof UrlScript && $url->path[0]!=='/') { // relative
			$url->path=substr($lastUrl->path, 0, strrpos($lastUrl->path, '/')+1).$url->path;
			}

		foreach (array('scheme', 'host', 'port') as $copy) {
			if (empty($url->{$copy})) {
				if (empty($lastUrl->{$copy})) {
					throw new InvalidUrlException("Missing URL $copy!");
					}
				$url->{$copy}=$lastUrl->{$copy};
				}
			}

		if (!$url->path || $url->path[0]!=='/') {
			$url->path='/'.$url->path;
			}

		return $url;
	}

	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array('url', 'method', 'headers', 'options', 'cookies', 'post', 'files');
	}
}
