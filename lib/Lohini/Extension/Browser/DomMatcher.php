<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Browser;
/**
 * @copyright (c) 2012, Karel Čížek (kaja47@k47.cz)
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Utils\Strings,
	Symfony\Component\CssSelector\CssSelector;

/**
 */
class DomMatcher
extends \Nette\Object
{
	/** @var callable */
	private $callback;


	/**
	 * @param callable $callback
	 */
	public function __construct($callback)
	{
		$this->callback=callback($callback);
	}

	/**
	 * @return mixed
	 */
	public function __invoke()
	{
		return $this->callback->invokeArgs(func_get_args());
	}

	/**
	 * @param \DOMDocument $dom
	 * @param \DOMNode $contextNode
	 * @param callable $extractor
	 * @return array|mixed|NULL
	 */
	public function processDom(\DOMDocument $dom, \DOMNode $contextNode=NULL, $extractor=NULL)
	{
		return $this($dom, $contextNode, $extractor);
	}

	/**
	 * Applies function $f to result of matcher (*after* extractor)
	 *
	 * @param callable $f
	 * @return DomMatcher
	 */
	public function andThen($f)
	{ // todo
		$self=$this;
		return new DomMatcher(function (\DOMDocument $dom, \DOMNode $contextNode=NULL, $extractor=NULL) use ($self, $f) {
			return $f($self($dom, $contextNode, $extractor));
			});
	}

	/**
	 * regexps without named patterns will return numeric array without key 0
	 * if result of previous matcher is array, it recursively applies regex on every element of that array
	 *
	 * @param string $regex
	 * @return DomMatcher
	 * @throws \InvalidArgumentException
	 */
	public function regex($regex)
	{
		$f=function($res) use ($regex, & $f) { // &$f for anonymous recursion
			if ($res===NULL) {
				return NULL;
				}
			if (is_string($res)) {
				preg_match($regex, $res, $m);
				if (count(array_filter(array_keys($m), 'is_string'))===0) { // regex has no named subpatterns
					unset($m[0]);
					}
				else {
					foreach ($m as $k => $v) {
						if (is_int($k)) {
							unset($m[$k]);
							}
						}
					}
				return $m;
				}
			if (is_array($res)) {
				$return=array();
				foreach ($res as $k => $v) {
					$return[$k]=$f($v);
					}
				return $return;
				}
			throw new \InvalidArgumentException("Method 'regex' should be applied only to DomMatcher::single which returns string or array of strings");
			};
		return $this->andThen($f);
	}

	/**
	 * @param string $basePath
	 * @param array|object $paths
	 * @param callable|NULL $defaultExtractor defaultExtractor == null => use outer extractor
	 * @return DomMatcher
	 */
	public static function multi($basePath, $paths=NULL, $defaultExtractor=NULL)
	{
		if (is_callable($paths)) {
			$defaultExtractor=$paths;
			$paths=NULL;
			}

		return new DomMatcher(function(\DOMDocument $dom, \DOMNode $contextNode=NULL, $extractor=NULL) use ($basePath, $paths, $defaultExtractor) {
			$xpath=new \DOMXpath($dom);
			$extractor=DomMatcher::_getExtractor($defaultExtractor, $extractor);

			if (strpos($basePath, '/')===FALSE) {
				$basePath=CssSelector::toXPath($basePath);
				}
			$matches=$xpath->query($basePath, $contextNode);

			$return=array();
			if (!$paths) {
				foreach ($matches as $m) {
					$return[]=$extractor->invoke($m);
					}
				}
			else {
				foreach ($matches as $m) {
					$return[]=DomMatcher::_extractPaths($dom, $m, $paths, $extractor);
					}
				}

			return $return;
			});
	}

	/**
	 * @param string $path
	 * @param callable|NULL $defaultExtractor
	 * @return DomMatcher
	 */
	public static function single($path, $defaultExtractor=NULL)
	{
		return new DomMatcher(function(\DOMDocument $dom, \DOMNode $contextNode=NULL, $extractor=NULL) use ($path, $defaultExtractor) {
			$xpath=new \DOMXpath($dom);
			$extractor=DomMatcher::_getExtractor($defaultExtractor, $extractor);

			if (is_array($path)) {
				return DomMatcher::_extractPaths($dom, $contextNode, $path, $extractor);
				}
			if (strpos($path, '/')===FALSE) {
				$path=CssSelector::toXPath($path);
				}
			return DomMatcher::_extractValue($extractor, $xpath->query($path, $contextNode));
			});
	}

	/**
	 * @param callable|NULL $defaultExtractor
	 * @param callable|NULL $extractor
	 * @return \Nette\Callback|callable
	 */
	public static function _getExtractor($defaultExtractor, $extractor)
	{
		if ($defaultExtractor!==NULL) {
			return callback($defaultExtractor);
			}
		if ($extractor===NULL) { // use default extractor
			return callback(get_called_class().'::defaultExtractor');
			}
		// use outer extractor passed as explicit argument
		return callback($extractor);
	}

	/**
	 * @internal
	 * @param \DOMDocument $dom
	 * @param \DOMNode $contextNode
	 * @param array $paths
	 * @param callable $extractor
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public static function _extractPaths(\DOMDocument $dom, \DOMNode $contextNode, $paths, $extractor)
	{
		$xpath=new \DOMXpath($dom);
		$return=array();

		foreach ($paths as $key => $val) {
			if (is_array($val)) { // path => array()
				if (strpos($key, '/')===FALSE) {
					$key=CssSelector::toXPath($key);
					}

				$node=$xpath->query($key, $contextNode)->item(0);
				$r= ($node===NULL)
					? array_fill_keys(array_keys($val), NULL)
					: self::_extractPaths($dom, $node, $val, $extractor);
				$return=array_merge($return, $r); // todo: object result
				}
			elseif ($val instanceof DomMatcher || $val instanceof \Closure) { // key => multipath
				$return[$key]=$val($dom, $contextNode, $extractor);
				}
			elseif (is_string($val)) { // key => path
				if (strpos($val, '/')===FALSE) {
					$val=CssSelector::toXPath($val);
					}

				$return[$key]=self::_extractValue($extractor, $xpath->query($val, $contextNode));
				}
			else {
				throw new \InvalidArgumentException('Invalid path. Expected string, array or marcher, '.gettype($val).' given');
				}
			}

		return $return;
	}

	/**
	 * @param callable $extractor
	 * @param \DOMNodeList $matches
	 * @return mixed|NULL
	 */
	public static function _extractValue($extractor, $matches)
	{
		return $matches->length===0? NULL : callback($extractor)->invoke($matches->item(0));
	}

	/**
	 * @internal
	 * @param \DOMNode|string $n
	 * @return string
	 */
	public static function defaultExtractor($n)
	{
		return static::normalizeWhitespaces($n);
	}

	/**
	 * @internal
	 * @param \DOMNode|string $n
	 * @return string
	 */
	public static function htmlExtractor($n)
	{
		$dom=$n->ownerDocument;
		$html='';
		foreach ($n->childNodes as $child) {
			$html.=$dom->saveXML($child);
			}
		return static::normalizeWhitespaces($html);
	}

	/**
	 * @param \DOMNode|string $n
	 * @return string
	 */
	public static function normalizeWhitespaces($n)
	{
		return trim(Strings::replace(Strings::normalize($n instanceof \DOMNode? $n->nodeValue : $n), array('~\xc2\xa0~' => ' ')));
	}
}
