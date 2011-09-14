<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Plugins;

/**
 * The plugin manager.
 *
 * @author Lopo
 */
interface IManager
{
	/**
	 * Adds the specified plugin or plugin factory to the manager.
	 * @param string $name
	 * @param mixed $plugin object, class name or callback
	 */
//	function addPlugin($name, $plugin);

	/**
	 * Gets the plugin object of the specified type.
	 * @param string $name
	 * @return mixed
	 */
//	function getPlugin($name);

	/**
	 * Removes the specified plugin type from the manager.
	 * @param string $name
	 */
//	function removePlugin($name);

	/**
	 * Exists the plugin?
	 * @param string $name
	 * @return bool
	 */
//	function hasPlugin($name);
}
