<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Loaders;

use Nette\Utils\Strings;

/**
 * of SplClassLoader
 *
 * @author Lopo <lopo@lohini.net>
 */
class SplClassLoader
extends \Nette\Loaders\AutoLoader
{
	/** @val \Lohini\Loaders\SplClassLoader */
	private static $instance;
	/** @var array */
	private $map;


	/**
	 * @param array $map
	 */
	public function __construct(array $map=array('Lohini' => LOHINI_DIR))
	{
		$this->map=array_map(function($item) {
				return trim($item, "\\");
				},
			$map);
	}

	/**
	 * @param string $namespace
	 * @param string $dir
	 * @return \Lohini\Loaders\SplClassLoader
	 */
	public function addAlias($namespace, $dir)
	{
		$this->map[trim($namespace, "\\")]=$dir;
		return $this;
	}

	/**
	 * Returns singleton instance with lazy instantiation.
	 * @param array $map
	 * @return \Lohini\Loaders\SplClassLoader
	 */
	public static function getInstance(array $map=array('Lohini' => LOHINI_DIR))
	{
		if (static::$instance===NULL) {
			static::$instance=new self($map);
			}
		return static::$instance;
	}

	/**
	 * Handles autoloading of classes or interfaces.
	 * @param string $type
	 */
	public function tryLoad($type)
	{
		$namespace=$this->getLongestNamespace($this->getFilteredByType($type));
		if ($namespace) {
			$type=substr($type, strlen($namespace)+1);
			$path=$this->map[$namespace].'/'. str_replace('\\', DIRECTORY_SEPARATOR, $type).'.php';

			if (file_exists($path)) {
				\Nette\Utils\LimitedScope::load($path);
				}
			}
	}

	/**
	 * @param string $type
	 * @return array
	 */
	public function getFilteredByType($type)
	{
		return array_filter(
			array_keys($this->map),
			function($namespace) use ($type) {
				return Strings::startsWith(strtolower($type), strtolower($namespace));
				}
			);
	}

	/**
	 * @param array $namespaces
	 * @return string
	 */
	public function getLongestNamespace(array $namespaces)
	{
		usort(
			$namespaces,
			function ($first, $second) {
				return substr_count($second, "\\")-substr_count($first, "\\");
				}
			);
		return reset($namespaces);
	}
}
