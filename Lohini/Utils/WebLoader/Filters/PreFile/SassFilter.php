<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters;

use Nette\Environment as NEnvironment,
	Lohini\WebLoader\Filters\Sass;

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
	public static function __invoke($code, \Lohini\WebLoader\WebLoader $loader, $file=NULL)
	{
		if ($file===NULL
			|| !in_array(\Nette\Utils\Strings::lower(pathinfo($file, PATHINFO_EXTENSION)), array(Sass\File::SASS, Sass\File::SCSS))
			) {
			return $code;
			}
		$so=array();
		if (!NEnvironment::isProduction()) {
			$so['debug_info']=TRUE;
			}
		$filter=new Sass\Parser($so);
		return $filter->toCss($file, TRUE);
	}
}

