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

use Lohini\Extension\Curl,
	Nette\Http\UrlScript;

/**
 */
class BrowserSession
extends \Nette\Object
{
	/** @var \Nette\Http\UrlScript */
	private $page;
	/** @var History\EagerHistory */
	private $history;
	/** @var WebBrowser */
	private $browser;
	/** @var array */
	private $cookies=array();


	/**
	 * @param WebBrowser $browser
	 * @param History\EagerHistory $history
	 */
	public function __construct(WebBrowser $browser=NULL, History\EagerHistory $history=NULL)
	{
		$this->browser=$browser;
		$this->history= $history ?: new History\EagerHistory;
	}

	/**
	 * @param WebBrowser $browser
	 */
	public function setBrowser(WebBrowser $browser)
	{
		$this->browser=$browser;
		$this->history->clean();
	}

	/**
	 * @return WebBrowser
	 * @throws \Nette\InvalidStateException
	 */
	public function getBrowser()
	{
		if ($this->browser===NULL) {
			$class=get_called_class();
			throw new \Nette\InvalidStateException("No WebBrowser was provided. Please provide it using $class::setBrowser(\$browser).");
			}

		return $this->browser;
	}

	/**
	 */
	public function cleanHistory()
	{
		$this->history->clean();
	}

	/**
	 * @return \SplObjectStorage|WebPage[]
	 */
	public function getHistory()
	{
		return $this->history->getPages();
	}

	/**
	 * @return int
	 */
	public function getRequestsCount()
	{
		return $this->history->count();
	}

	/**
	 * @return int
	 */
	public function getRequestsTotalTime()
	{
		return $this->history->getRequestsTotalTime();
	}

	/**
	 * @return WebPage
	 */
	public function getLastPage()
	{
		return $this->history->getLast();
	}

	/**
	 * @param array $cookies
	 */
	public function setCookies(array $cookies)
	{
		$this->cookies=$cookies;
	}

	/**
	 * @return array
	 */
	public function getCookies()
	{
		return $this->cookies;
	}

	/**
	 * @param string|\Nette\Http\UrlScript $page
	 */
	public function setPage($page)
	{
		$this->page=new UrlScript($page);
	}

	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * @param $link
	 * @return WebPage
	 */
	public function open($link)
	{
		return $this->send(new Curl\Request($link));
	}

	/**
	 * @param Curl\Request $request
	 * @return WebPage
	 * @throws Curl\CurlException
	 */
	public function send(Curl\Request $request)
	{
		$request->cookies=$this->getCookies();
		if ($this->getPage()!==NULL) {
			$request->url=Curl\Request::fixUrl($this->getPage(), $request->getUrl());
			}

		// apply history
		if ($last=$this->history->getLast()) {
			$request->setReferer($last->getAddress());
			}

		// send
		$response=$this->getBrowser()->send($request);

		// create page from response document
		$page=new WebPage($response->getDocument(), $response->getUrl());
		$page->setSession($this);

		// store
		$this->history->push($page, $request, $response);
		$this->cookies=$response->getCookies();
		$this->page=new UrlScript($request->url->getHostUrl());

		// return
		return $page;
	}

	/**
	 * @param Curl\Request $request
	 * @return string
	 */
	public function ajax(Curl\Request $request)
	{
		$request->cookies=$this->getCookies();
		$request->headers['X-Requested-With']='XMLHttpRequest';
		if ($this->getPage()!==NULL) {
			$request->url=Curl\Request::fixUrl($this->getPage(), $request->getUrl());
			}

		// apply history
		if ($last=$this->history->getLast()) {
			$request->setReferer($last->getAddress());
			}

		// send
		$response=$this->getBrowser()->send($request);
		$content=$response->getResponse();

		// store
		$this->history->push((object)array('content' => $content), $request, $response);

		// return
		return $content;
	}

	/**
	 * @return array
	 */
	public function __sleep()
	{
		if ($this->history) {
			$this->history->clean();
			}

		return array('cookies', 'page', 'history');
	}
}
