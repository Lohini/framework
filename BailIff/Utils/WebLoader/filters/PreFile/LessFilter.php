<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters;

/**
 * BailIff wrapping class for Leafo's lessphp
 * @link https://github.com/leafo/lessphp
 * @author Lopo <lopo@losys.eu>
 */
class LessFilter
extends PreFileFilter
{
	/**
	 * Check if we have Leafo's lessc
	 * @throws NotSupportedException
	 */
	public function __construct()
	{
		if (!in_array('lessc', get_declared_classes()) && !class_exists('lessc')) {
			throw new \Nette\NotSupportedException("Don't have Leafo's lessc");
			}
	}

	/**
	 * @see PreFileFilter::__invoke()
	 */
	public static function __invoke($code, \BailIff\WebLoader\WebLoader $loader, $file=NULL)
	{
		if ($file===NULL || strtolower(pathinfo($file, PATHINFO_EXTENSION))!='less') {
			return $code;
			}
		$filter=new \lessc($file);
		return $filter->parse();
	}
}
