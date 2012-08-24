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

/**
 */
class DomElement
extends \Nette\Object
{
	/** @var \DOMElement|DomDocument */
	protected $element;


	/**
	 * @param \DOMNode $element
	 */
	public function __construct(\DOMNode $element)
	{
		$this->element=$element;
	}

	/**
	 * @return \DOMElement|DomDocument
	 */
	public function getElement()
	{
		return $this->element;
	}

	/**
	 * @param string $selector
	 * @param \DOMNode $context
	 * @return \DOMNode[]|\DOMElement[]|NULL
	 */
	public function find($selector, $context=NULL)
	{
		$document= $this->element->ownerDocument ?: $this->element;
		return $document->find($selector, $context ?: $this->element);
	}

	/**
	 * @param string $selector
	 * @param \DOMNode $context
	 * @return \DOMNode|\DOMElement
	 */
	public function findOne($selector, $context=NULL)
	{
		$document= $this->element->ownerDocument ?: $this->element;
		return $document->findOne($selector, $context ?: $this->element);
	}

	/**
	 * @param string $text
	 * @param string $element
	 * @return array|NULL
	 */
	public function findText($text, $element=NULL)
	{
		$xpath=new \DOMXPath($this->element->ownerDocument);
		$element= $element? \Symfony\Component\CssSelector\CssSelector::toXPath($element) : NULL;
		return DomDocument::nodeListToArray($xpath->query($element.'[contains(., "'.$text.'")]', $this->element));
	}

	/**
	 * @param string $selector
	 * @param ISnippetProcessor $processor
	 * @return mixed
	 */
	public function processSnippets($selector, ISnippetProcessor $processor)
	{
		$result=array();
		foreach ($this->find($selector) as $node) {
			$result[]=$processor->process($node);
			}

		return $result;
	}
}
