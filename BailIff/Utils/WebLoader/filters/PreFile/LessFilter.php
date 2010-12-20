<?php // vim: ts=4 sw=4 ai:
namespace BailIff\WebLoader\Filters;

use BailIff\WebLoader\WebLoader,
	BailIff\WebLoader\Filters\PreFileFilter;
/**
 * BailIff wrapping class for Leafo's lessphp
 * @link https://github.com/leafo/lessphp
 * @author Lopo <lopo@losys.eu>
 */
class LessFilter
extends PreFileFilter
{
	/**
	 * (non-PHPdoc)
	 * @see BailIff\WebLoader\Filters.PreFileFilter::__invoke()
	 * @throws \FileNotFoundException
	 */
	public static function __invoke($code, WebLoader $loader, $file=NULL)
	{
		if ($file===NULL || strtolower(pathinfo($file, PATHINFO_EXTENSION))!='less') {
			return $code;
			}
		$filter=new \lessc($file);
		return $filter->parse();
	}
}
