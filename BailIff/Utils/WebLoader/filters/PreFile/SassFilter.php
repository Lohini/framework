<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters;

use BailIff\WebLoader\Filters\Sass;

class SassFilter
extends PreFileFilter
{
	/**
	 * Invoke filter
	 * @param string $code
	 * @param WebLoader $loader
	 * @param string $file
	 * @return string
	 */
	public static function __invoke($code, \BailIff\WebLoader\WebLoader $loader, $file=NULL)
	{
		if ($file===NULL
			|| !in_array(\Nette\Utils\Strings::lower(pathinfo($file, PATHINFO_EXTENSION)), array(Sass\File::SASS, Sass\File::SCSS))
			) {
			return $code;
			}
		$filter=new Sass\Parser(array(/*Sass options*/));
		return $filter->toCss($file, TRUE);
	}
}

