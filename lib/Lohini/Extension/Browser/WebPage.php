<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Browser;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Extension\Curl;

/**
 */
class WebPage
extends DomElement
{
	/** @var \Nette\Http\UrlScript */
	private $address;
	/** @var BrowserSession */
	private $session;


	/**
	 * @param string|\DOMDocument $document
	 * @param \Nette\Http\UrlScript $address
	 */
	public function __construct($document, \Nette\Http\UrlScript $address)
	{
		if (!$document instanceof \DOMDocument){
			$document=DomDocument::fromMalformedHtml($document);
			}

		parent::__construct($document);
		$this->address=$address;
	}

	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getAddress()
	{
		return $this->address;
	}

	/**
	 * @param BrowserSession $session
	 */
	public function setSession(BrowserSession $session)
	{
		$this->session=$session;
	}

	/**
	 * @return BrowserSession
	 */
	public function getSession()
	{
		return $this->session ?: new BrowserSession;
	}

	/**
	 * @param IDocumentProcessor $processor
	 * @return mixed
	 */
	public function process(IDocumentProcessor $processor)
	{
		return $processor->process($this->getElement());
	}

	/**
	 * @param string $selector
	 * @return Form
	 */
	public function findForm($selector)
	{
		return ($form=$this->findOne($selector))? new Form($form, $this) : NULL;
	}

	/**
	 * @param string|\DOMElement $link
	 * @return WebPage|NULL
	 */
	public function open($link)
	{
		if (is_string($link)) {
			if (!\Nette\Utils\Validators::isUrl($link)) {
				if (!$link=$this->findText($link, 'a')) {
					return NULL;
					}

				$link=current($link);
				}
			}
		elseif ($link instanceof \DOMElement && strtolower($link->tagName)==='a') {
			$link=$link->getAttribute('href');
			}
		else {
			return NULL;
			}

		return $this->getSession()->open($link);
	}

	/**
	 * @param Form $form
	 * @param string $button
	 * @return WebPage
	 */
	public function submit(Form $form, $button=NULL)
	{
		if (!$button instanceof \DOMElement) {
			$button=$form->findButton($button);
			}

		$request=new Curl\Request($form->getAction());
		$request->method=$form->getMethod();
		if ($request->method!==Curl\Request::GET) {
			$request->post=$form->getSubmitValues($button);
			}
		else {
			$request->getUrl()->appendQuery($form->getSubmitValues($button));
			}

		return $this->getSession()->send($request);
	}
}
