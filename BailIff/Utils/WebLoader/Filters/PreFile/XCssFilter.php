<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters;

/**
 * BailIff wrapping class for Pawlik's xCSS
 * @link http://xcss.antpaw.org/
 * @author Lopo <lopo@losys.eu>
 */
class XCssFilter
extends PreFileFilter
{
	/**
	 * Check if we have Pawlik's xCSS
	 * @throws NotSupportedException
	 */
	public function __construct()
	{
		if (!in_array('xCSS', get_declared_classes()) && !class_exists('xCSS')) {
			throw new \Nette\NotSupportedException("Don't have Pawlik's xCSS");
			}
	}

	/**
	 * @see PreFileFilter::__invoke()
	 */
	public static function __invoke($code, \BailIff\WebLoader\WebLoader $loader, $file=NULL)
	{
		if ($file===NULL || strtolower(pathinfo($file, PATHINFO_EXTENSION))!='xcss') {
			return $code;
			}
		$config=array(
			'path_to_css_dir' => WWW_DIR.'/css',
			'xCSS_files' => array(),
			);
		define('XCSSCLASS', '');
		$filter=new \xCSS($config);
		return $filter->compile(file_get_contents($file));
	}
}
