<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Extensions\Compass;
/**
 * Compass extension configuration class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */
 
/**
 * Compass extension configuration class.
 */
class Config
{
	public static $config;
	private static $defaultConfig=array(
		'project_path' => '',
		'http_path' => '/',
		'css_dir' => 'css',
		'css_path' => '',
		'http_css_path' => '/css',
		'fonts_dir' => 'fonts',
		'fonts_path' => '',
		'http_fonts_path' => '',
		'images_dir' => 'img',
		'images_path' => '',
		'http_images_path' => '',
		'javascripts_dir' => 'js',
		'javascripts_path' => '',
		'http_javascripts_path' => '',
		'relative_assets' => TRUE,
		);


	/**
	 * Sets configuration settings or returns a configuration setting.
	 * @param mixed array: configuration settings; string: configuration setting to return
	 * @return string|NULL configuration setting. Null if setting does not exist.
	 */
	public function config($config)
	{
		if (is_array($config)) {
			self::$config=array_merge(self::$defaultConfig, $config);
			self::setDefaults();
			}
		elseif (is_string($config) && isset(self::$config[$config])) {
			return self::$config[$config];
			}
	}

	/**
	 * Sets default values for paths not specified
	 */
	private static function setDefaults()
	{
		foreach (array('css', 'images', 'fonts', 'javascripts') as $asset) {
			if (empty(self::$config[$asset.'_path'])) {
				self::$config[$asset.'_path']=self::$config['project_path'].DIRECTORY_SEPARATOR.self::$config[$asset.'_dir'];
				}
			if (empty(self::$config["http_{$asset}_path"])) {
				self::$config["http_{$asset}_path"]=self::$config['http_path'].self::$config[$asset.'_dir'];
				}
			}
	}
}
