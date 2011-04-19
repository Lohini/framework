<?php // vim: ts=4 sw=4 ai:
namespace BailIff\WebLoader\Filters;

use Nette\NotSupportedException,
	BailIff\WebLoader\WebLoader,
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
	 * Check if we have Leafo's lessc
	 * @throws \NotSupportedException
	 */
	public function __construct()
	{
		if (!in_array('lessc', get_declared_classes()) && !class_exists('lessc')) {
			throw new NotSupportedException("Don't have Leafo's lessc");
			}
	}

	/**
	 * (non-PHPdoc)
	 * @see BailIff\WebLoader\Filters.PreFileFilter::__invoke()
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
