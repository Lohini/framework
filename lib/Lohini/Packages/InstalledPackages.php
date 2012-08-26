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

use Nette\Utils\Neon;

/**
 */
class InstalledPackages
extends \Nette\Object
implements \IteratorAggregate, IPackageList
{
	/** @var string */
	private $appDir;


	/**
	 * @param string $appDir
	 */
	public function __construct($appDir)
	{
		if (!is_dir($appDir)) {
			throw new \Nette\InvalidArgumentException('Please provide an application directory %appDir%.');
			}

		$this->appDir=$appDir;
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->getPackages());
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		if (is_file($default=$this->appDir.'/config/packages.neon')) {
			return $default;
			}
		if (is_file($file=$this->appDir.'/packages.neon')) {
			return $file;
			}

		return $default;
	}

	/**
	 * @return array
	 * @throws \Nette\InvalidStateException
	 */
	public function getPackages()
	{
		if (!file_exists($file=$this->getFilename())) {
			$list=$this->supplyDefaultPackages($file);
			}
		else {
			try {
				$list=(array)Neon::decode(@file_get_contents($file));
				}
			catch (\Nette\Utils\NeonException $e) {
				throw new \Nette\InvalidStateException("Packages file '$file' is corrupted!", NULL, $e);
				}
			}

		if (!$list) {
			throw new \Nette\InvalidStateException("File '$file' is corrupted! Fix the file, or delete it.");
			}

		return $list;
	}

	/**
	 * @param string $file
	 * @return array
	 * @throws \Lohini\FileNotWritableException
	 */
	private function supplyDefaultPackages($file)
	{
		$default= class_exists('Lohini\CF')
			? \Lohini\CF::createPackagesList()->getPackages()
			: \Lohini\Core::getDefaultPackages();

		if (!@file_put_contents($file, Neon::encode($default, Neon::BLOCK))) {
			throw \Lohini\FileNotWritableException::fromFile($file);
			}
		@chmod($file, 0777);
		return $default;
	}
}
