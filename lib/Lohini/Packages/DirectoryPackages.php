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

use Nette\Reflection\ClassType;

/**
 */
class DirectoryPackages
extends \Nette\Object
implements \IteratorAggregate, IPackageList
{
	/** @var string */
	private $dir;
	/** @var string */
	private $ns;


	/**
	 * @param string $dir
	 * @param string $ns
	 */
	public function __construct($dir, $ns=NULL)
	{
		$this->dir=realpath($dir);
		$this->ns=$ns;
	}

	/**
	 * @return array
	 */
	public function getPackages()
	{
		if (!is_dir($this->dir)) {
			return array();
			}

		$packages=array();
		foreach (\Nette\Utils\Finder::findFiles('*Package.php')->from($this->dir) as $file) {
			$refl=$this->getClass($file);
			if ($this->isPackage($refl)) {
				$packages[]=$refl->getName();
				}
			}

		sort($packages);
		return $packages;
	}

	/**
	 * @param ClassType $refl
	 * @return bool
	 */
	protected function isPackage(ClassType $refl)
	{
		return $refl->isSubclassOf('Lohini\Packages\Package') && !$refl->isAbstract();
	}

	/**
	 * @param \SplFileInfo $file
	 * @return ClassType
	 */
	protected function getClass(\SplFileInfo $file)
	{
		$class=$this->ns.'\\'.ltrim(substr($this->getRelative($file), 0, -4), '\\');
		if (!class_exists($class, FALSE)) {
			require_once $file->getRealpath();
			}

		return ClassType::from($class);
	}

	/**
	 * @param \SplFileInfo $file
	 * @return string
	 */
	protected function getRelative(\SplFileInfo $file)
	{
		return strtr($file->getRealpath(), array($this->dir => '', '/' => '\\'));
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->getPackages());
	}
}
