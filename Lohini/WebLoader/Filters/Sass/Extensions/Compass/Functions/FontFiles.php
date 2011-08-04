<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Extensions\Compass\Functions;
/**
 * Compass extension SassScript font files functions class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass\Script;

/**
 * Compass extension SassScript font files functions class.
 * A collection of functions for use in SassSCript.
 */
class FontFiles
{
	/**
	 * @return Script\Literals\String
	 * @throws Script\FunctionException
	 */
	public function font_files()
	{
		if (func_num_args()%2) {
			throw new Script\FunctionException('An even number of arguments must be passed to font_files()', Script\Parser::$context->node);
			}

		$args=func_get_args();
		$files=array();
		while ($args) {
		    $files[]='#{font_url('.array_shift($args).")} format('".trim(array_shift($args), '\'"')."')";
			}
		return new Script\Literals\String(join(", ", $files));
	}
}
