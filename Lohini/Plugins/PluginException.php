<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Plugins;


/**
 * Plugin not found exception
 */
class PluginException
extends \Exception
{
	const MISSING_TYPE=1;
	const AMBIGUOUS_TYPE=2;
	const INVALID_VERSION=3;
	const INVALID_OBJECT=4;
	const MISSING_DEPENDENCY=5;
	const OUTDATED_DEPENDENCY=6;
	const INSTALL_ERROR=7;
	const NOT_FOUND=8;


	/**
	 * @param string $type
	 * @return PluginException
	 */
	public static function missingType($type)
	{
		return new self("Plugin matching '$type' type not found.", self::MISSING_TYPE);
	}

	/**
	 * @param string $type
	 * @return PluginException
	 */
	public static function ambiguousType($type)
	{
		return new self("Found more than one plugin matching '$type' type.", self::AMBIGUOUS_TYPE);
	}

	/**
	 * @param string $iversion
	 * @param string $version
	 * @return PluginException
	 */
	public static function invalidVersion($iversion, $version)
	{
		return new self("Invalid plugin source version: installed '$iversion' vs source '$version'.", self::INVALID_VERSION);
	}

	/**
	 * @param object $object
	 * @return PluginException
	 */
	public static function invalidObject($object)
	{
		return new self("Invalid object type, expected Plugin entity, got '".get_class($object)."'", self::INVALID_VERSION);
	}

	/**
	 * @param string $dep
	 * @return PluginException
	 */
	public static function missingDependency($dep)
	{
		return new self("Missing dependency $dep.", self::MISSING_DEPENDENCY);
	}

	/**
	 * @param string $dep
	 * @param string $min
	 * @param string $act
	 * @return PluginException
	 */
	public static function outdatedDependency($dep, $min, $act)
	{
		return new self("Missing dependency $dep, requires $min at least, $act found.", self::OUTDATED_DEPENDENCY);
	}

	/**
	 * @param string $info
	 * @return PluginException
	 */
	public static function installError($info=NULL)
	{
		return new self("Installation error $info", self::INSTALL_ERROR);
	}

	/**
	 * @param string $name
	 * @return PluginException
	 */
	public static function notFound($name)
	{
		return new self("Plugin $name not found.", self::NOT_FOUND);
	}
}
