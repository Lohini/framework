<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Reflection;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class NamespaceUses
extends \Nette\Object
{
	/** @var \ReflectionClass */
	private $refl;


	/**
	 * @param \ReflectionClass $refl
	 * @throws \Nette\InvalidArgumentException
	 */
	public function __construct(\ReflectionClass $refl)
	{
		if (!$refl->isUserDefined()) {
			throw new \Nette\InvalidArgumentException('Native functions cannot be parsed.');
			}
		$this->refl=$refl;
	}

	/**
	 * @return string
	 */
	public function parse()
	{
		$uses=$this->parseFile();
		return $uses[$this->refl->getNamespaceName().'\\'];
	}

	/**
	 * @return array
	 * @throws \Nette\InvalidStateException
	 */
	private function parseFile()
	{
		$code=file_get_contents($this->refl->getFileName());

		$T_TRAIT= PHP_VERSION_ID<50400? -1 : T_TRAIT;

		$expected=FALSE;
		$class= $namespace= $name= '';
		$level= $minLevel= 0;
		$uses=array();

		foreach (@token_get_all($code) as $token) { // intentionally @
			if ($token==='}') {
				$level--;
				if ($class && $minLevel===$level) {
					$class=NULL;
					}
				}

			if (is_array($token)) {
				switch ($token[0]) {
					case T_COMMENT:
					case T_DOC_COMMENT:
					case T_WHITESPACE:
						continue 2;
					case T_NS_SEPARATOR:
					case T_STRING:
						if ($expected) {
							$name.=$token[1];
							continue 2;
							}
						break;
					case T_NAMESPACE:
					case T_CLASS:
					case T_INTERFACE:
					case T_USE:
					case $T_TRAIT:
						$expected=$token[0];
						$name='';
						continue 2;
					case T_CURLY_OPEN:
					case T_DOLLAR_OPEN_CURLY_BRACES:
						$level++;
					}
				}

			if ($expected) {
				switch ($expected) {
					case T_CLASS:
					case T_INTERFACE:
					case $T_TRAIT:
						if ($level===$minLevel) {
							$class=$namespace.$name;
							}
						break;
					case T_USE:
						if ($token===',') {
							$name.=$token;
							continue 2;
							}
						elseif ($token===';') {
							$list=array_map(callback('trim'), explode(',', $name));
							$uses[$namespace]= isset($uses[$namespace])? array_merge($uses[$namespace], $list) : $list;
							}
						break;
					case T_NAMESPACE:
						$namespace= $name? $name.'\\' : '';
						$minLevel= $token==='{'? 1 : 0;
					}

				$expected=NULL;
				}

			if ($token==='{') {
				$level++;
				}
			}
		return $uses;
	}
}
