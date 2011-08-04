<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Extensions\Compass\Functions;
/**
 * Compass extension SassScript inline data class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass\Script,
	Lohini\WebLoader\Filters\Sass\Extensions\Compass\Config;

/**
 * Compass extension Script inline data functions class.
 * A collection of functions for use in Script.
 */
class InlineData
{
	public function inline_image($path, $mime_type=NULL)
	{
		$path=$path->value;
		$real_path=Config::config('images_path').DIRECTORY_SEPARATOR.$path;
		$url='url(data:'.self::compute_mime_type($path, $mime_type).';base64,'.self::data($real_path).')';
		return new Script\Literals\String($url);
	}

	public function inline_font_files()
	{
		if (func_num_args()%2) {
			throw new Script\FunctionException('An even number of arguments must be passed to inline_font_files()', Script\Parser::$context->node);
			}

		$args=func_get_args();
		$files=array();
		while ($args) {
			$path=array_shift($args);
			$real_path=Config::config('fonts_path').DIRECTORY_SEPARATOR.$path->value;
			$fp=fopen($real_path, 'rb');
			$url='url(data:'.self::compute_mime_type($path).';base64,'.self::data($real_path).')';
			$files[]="$url format('".array_shift($args)."')";
			}
		return new Script\Literals\String(join(", ", $files));
	}

	private function compute_mime_type($path, $mime_type=NULL)
	{
		if ($mime_type) {
			return $mime_type;
			}

		switch (true) {
			case preg_match('/\.png$/i', $path):
				return 'image/png';
				break;
			case preg_match('/\.jpe?g$/i', $path):
				return 'image/jpeg';
				break;
			case preg_match('/\.gif$/i', $path):
				return 'image/gif';
				break;
			case preg_match('/\.otf$/i', $path):
				return 'font/opentype';
				break;
			case preg_match('/\.ttf$/i', $path):
				return 'font/truetype';
				break;
			case preg_match(' /\.woff$/i', $path):
				return 'font/woff';
				break;
			case preg_match(' /\.off$/i', $path):
				return 'font/openfont';
				break;
			case preg_match('/\.([a-zA-Z]+)$/i', $path, $matches):
				return 'image/'.strtolower($matches[1]);
				break;
			default:
				throw new Script\FunctionException("Unable to determine mime type for $path, please specify one explicitly", Script\Parser::$context->node);
				break;
			}
	}

	private function data($real_path)
	{
		if (file_exists($real_path)) {
			return base64_encode(file_get_contents($real_path));
			}
		throw new Script\FunctionException("Unable to find 'file': $real_path", Script\Parser::$context->node);
	}
}
