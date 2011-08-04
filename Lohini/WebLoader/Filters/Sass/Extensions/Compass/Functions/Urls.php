<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Extensions\Compass\Functions;
/**
 * Compass extension SassScript urls functions class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass\Script\Literals,
	Lohini\WebLoader\Filters\Sass\Extensions\Compass\Config;

/**
 * Compass extension SassScript urls functions class.
 * A collection of functions for use in SassSCript.
 */
class Urls
{
	/**
	 * @param type $path
	 * @param \Lohini\WebLoader\Filters\Sass\Script\Literals\Boolean $only_path
	 * @return \Lohini\WebLoader\Filters\Script\Literals\String
	 */
	public function stylesheet_url($path, $only_path=NULL)
	{
		$path=$path->value; # get to the string value of the literal.

		# Compute the $path to the stylesheet, either root relative or stylesheet relative
		# or nil if the http_images_path is not set in the configuration.
		if (Config::config('relative_assets')) {
			$http_css_path=self::compute_relative_path(Config::config('css_path'));
			}
		elseif (Config::config('http_css_path')) {
			$http_css_path=Config::config('http_css_path');
			}
		else {
			$http_css_path=Config::config('css_dir');
			}

		return new Literals\String(self::clean("$http_css_path/$path", $only_path));
	}

	/**
	 * @param type $path
	 * @param \Lohini\WebLoader\Filters\Script\Literals\Boolean $only_path
	 * @return \Lohini\WebLoader\Filters\Script\Literals\String
	 */
	public function font_url($path, $only_path=NULL)
	{
		$path=$path->value; # get to the string value of the literal.

		# Short circuit if they have provided an absolute url.
		if (self::is_absolute_path($path)) {
			return new Literals\String("url('$path')");
			}

		# Compute the $path to the font file, either root relative or stylesheet relative
		# or nil if the http_fonts_path cannot be determined from the configuration.
		if (Config::config('relative_assets')) {
			$http_fonts_path=self::compute_relative_path(Config::config('fonts_path'));
			}
		else {
			$http_fonts_path=Config::config('http_fonts_path');
			}

		return new Literals\String(self::clean("$http_fonts_path/$path", $only_path));
	}

	/**
	 * @param type $path
	 * @param \Lohini\WebLoader\Filters\Script\Literals\Boolean $only_path
	 * @return \Lohini\WebLoader\Filters\Script\Literals\String
	 */
	public function image_url($path, $only_path=NULL)
	{
		$path=$path->value; # get to the string value of the literal.

		if (preg_match('%^'.preg_quote(Config::config('http_images_path'), '%').'/(.*)%', $path, $matches)) {
			# Treat root relative urls (without a protocol) like normal if they start with
			# the images $path.
			$path=$matches[1];
			}
		elseif (self::is_absolute_path($path)) {
			# Short curcuit if they have provided an absolute url.
			return new Literals\String("url('$path')");
			}

		# Compute the $path to the image, either root relative or stylesheet relative
		# or nil if the http_images_path is not set in the configuration.
		if (Config::config('relative_assets')) {
			$http_images_path=self::compute_relative_path(Config::config('images_path'));
			}
		elseif (Config::config('http_images_path')) {
			$http_images_path=Config::config('http_images_path');
			}
		else {
			$http_images_path=Config::config('images_dir');
			}

		# Compute the real $path to the image on the file stystem if the images_dir is set.
		if (Config::config('images_dir')) {
			$real_path=
				Config::config('project_path')
				.DIRECTORY_SEPARATOR.Config::config('images_dir')
				.DIRECTORY_SEPARATOR.$path;
			}

		# prepend the $path to the image if there's one
		if ($http_images_path) {
			$http_images_path.=(substr($http_images_path, -1)==='/'? '' : '/');
			$path=$http_images_path.$path;
			}

		return new Literals\String(self::clean($path, $only_path));
	}

	/**
	 * takes off any leading "./".
	 * if $only_path emits a $path, else emits a url
	 * @param string $url
	 * @param Literals\Boolean $only_path
	 * @return string
	 */
	private function clean($url, $only_path)
	{
		if (!$only_path instanceof Literals\Boolean) {
			$only_path=new Literals\Boolean(FALSE);
			}

		$url= substr($url, 0, 2)==='./'? substr($url, 2) : $url;
		return $only_path->toBoolean()? $url : "url('$url')";
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	private function is_absolute_path($path)
	{
		return ($path[0]==='/' || substr($path, 0, 4)==='http');
	}

	/**
	 * returns the path relative to the target css file
	 * @param string $path
	 * @return string
	 */
	private function compute_relative_path($path)
	{
		return $path;
	}
}
