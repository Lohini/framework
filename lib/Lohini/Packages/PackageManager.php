<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Packages;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class PackageManager
extends \Nette\Object
{
	/** @var PackagesContainer|Package[] */
	private $packages;


	/**
	 * @param PackagesContainer $packages
	 */
	public function setActive(PackagesContainer $packages)
	{
		$this->packages=$packages;
	}

	/**
	 * @return Package[]
	 */
	public function getPackages()
	{
		return $this->packages->getPackages();
	}

	/**
	 * @param string $name
	 */
	public function hasPackage($name)
	{
		return isset($this->packages[$name]);
	}

	/**
	 * @param string $name
	 * @return Package
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getPackage($name)
	{
		if (!isset($this->packages[$name])) {
			throw new \Nette\InvalidArgumentException("Package named '$name' is not active.");
			}

		return $this->packages[$name];
	}

	/**
	 * Checks if a given class name belongs to an active package.
	 *
	 * @param string $class
	 * @return bool
	 */
	public function isClassInActivePackage($class)
	{
		foreach ($this->packages as $package) {
			if (strpos($class, $package->getNamespace())===0) {
				return class_exists($class);
				}
			}

		return FALSE;
	}

	/**
	 * Returns the file path for a given resource.
	 *
	 * A Resource can be a file or a directory.
	 *
	 * The resource name must follow the following pattern:
	 *
	 *	 @<PackageName>/path/to/a/file.something
	 *
	 * Looks first into Resources directory, than into package root.
	 *
	 * @param string $name  A resource name to locate
	 * @return array
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function formatResourcePaths($name)
	{
		if ($name[0]!=='@') {
			throw new \Nette\InvalidArgumentException("A resource name must start with @ ('$name' given).");
			}

		if (strpos($name, '..')!==FALSE) {
			throw new \Nette\InvalidArgumentException("File name '$name' contains invalid characters (..).");
			}

		$packageName=substr($name, 1);
		$path='';
		if (strpos($packageName, '/')!==FALSE) {
			list($packageName, $path)=explode('/', $packageName, 2);
			}

		$package=$this->getPackage($packageName);
		return array(
			$package->getPath().'/'.$path,
			$package->getPath().'/Resources/'.$path,
			$package->getPath().'/Resources/public/'.$path,
			);
	}

	/**
	 * Returns the file path for a given resource.
	 *
	 * A Resource can be a file or a directory.
	 *
	 * The resource name must follow the following pattern:
	 *
	 *	 @<PackageName>/path/to/a/file.something
	 *
	 * Looks first into Resources directory, than into package root.
	 *
	 * @param string $name A resource name to locate
	 * @return string|array
	 * @throws \Nette\InvalidArgumentException
	 */
	public function locateResource($name)
	{
		foreach ($this->formatResourcePaths($name) as $path) {
			if (file_exists($path)) {
				return $path;
				}
			}

		throw new \Nette\InvalidArgumentException("Unable to find file '$name'.");
	}
}
