<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Extensions\Compass\Functions;
/**
 * Compass extension SassScript image size functions class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass\Script\Literals;

/**
 * Compass extension SassScript image size functions class.
 * A collection of functions for use in SassSCript.
 */
class ImageSize
{
	/**
	 * Returns the $width of the image relative to the images directory
	 * @param type $image_file
	 * @return Literals\Number
	 */
	public function image_width($image_file)
	{
		$image_size=getimagesize(self::real_path($image_file));
		return new Literals\Number($image_size[0].'px');
	}

	/**
	 * Returns the height of the image relative to the images directory
	 * @param type $image_file
	 * @return Literals\Number
	 */
	public function image_height($image_file)
	{
		$image_size=getimagesize(self::real_path($image_file));
		return new Literals\Number($image_size[1].'px');
	}

	/**
	 * @param type $image_file
	 * @return string
	 */
	private function real_path($image_file) {
		$path=$image_file->value;
		# Compute the real path to the image on the file stystem if the images_dir is set.
		if (Config::config('images_path')) {
			return Config::config('images_path').DIRECTORY_SEPARATOR.$path;
			}
		else {
			return Config::config('project_path').DIRECTORY_SEPARATOR.$path;
			}
	}
}
