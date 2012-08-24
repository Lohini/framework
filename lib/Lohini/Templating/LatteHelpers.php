<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Templating;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Latte;

/**
 */
final class LatteHelpers
extends \Nette\Object
{
	final public function __construct()
	{
		throw new \Nette\StaticClassException("Can't instantiate static class ".get_class($this));
	}

	/**
	 * @param Latte\MacroTokenizer $tokenizer
	 * @param Latte\PhpWriter $writer
	 * @return array
	 * @throws Latte\CompileException
	 */
	public static function readArguments(Latte\MacroTokenizer $tokenizer, Latte\PhpWriter $writer)
	{
		$args=array();
		$tokenizer=$writer->preprocess($tokenizer);

		$key= $value= NULL;
		while ($token=$tokenizer->fetchToken()) {
			if ($tokenizer->isCurrent($tokenizer::T_STRING) || $tokenizer->isCurrent($tokenizer::T_SYMBOL)) {
				$value.=trim($token['value'], '\'"');

				if ($tokenizer->fetchUntil($tokenizer::T_CHAR)) {
					$key=$value;
					$value=NULL;
					continue;
					}
				if ($tokenizer->isNext('/')) {
					continue;
					}

				if ($key===NULL) {
					$args[]=$value;
					$value=NULL;
					}
				else {
					if (isset($args[$key])) {
						throw new Latte\CompileException("Ambiguous definition of '$key'.");
						}

					$args[$key]=$value;
					$key= $value= NULL;
					}
				}
			elseif ($tokenizer->isCurrent($tokenizer::T_CHAR) && $token['value']==='/') {
				$value.='/';
				}
			}

		if ($value) {
			$args[]=$value;
			}

		return $args;
	}

	/**
	 * @param string $content
	 * @return array
	 */
	public static function splitPhp($content)
	{
		$parts=array();
		$lastContext=NULL;
		foreach (token_get_all($content) as $token) {
			if (!is_array($token)) {
				end($parts);
				$parts[key($parts)].=$token;
				continue;
				}

			$context= $token[0]===T_INLINE_HTML? 'html' : 'php';
			if ($lastContext!==$context) {
				$parts[]=NULL;
				end($parts);
				}
			$parts[key($parts)].=$token[1];
			$lastContext=$context;
			}
		return $parts;
	}

	/**
	 * @param string $content
	 * @param NULL $before
	 * @param NULL $after
	 * @internal param Latte\PhpWriter $writer
	 * @return string
	 */
	public static function wrapTags($content, $before=NULL, $after=NULL)
	{
		$code=NULL;
		foreach (static::splitPhp($content) as $item) {
			if (substr($item, 0, 5)==='<?php') {
				$code.=$item;
				continue;
				}
			$code.=\Nette\Utils\Strings::replace($item, array(
				'~<([^<\s/]+)\s+~' => $before.'<\\1 ',
				'~\s+/>~' => " />$after",
				'~</([^<\s/]+)>~' => '</\\1>'.$after,
				));
			}
		return $code;
	}
}
