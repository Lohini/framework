<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Utils\Filesystem;

/**
 */
abstract class QueryWriter
extends \Nette\Object
{
	/** @var string */
	protected $version;
	/** @var \Lohini\Packages\Package */
	protected $package;
	/** @var string */
	protected $dir;
	/** @var string */
	protected $file;


	/**
	 * @param string $version
	 * @param \Lohini\Packages\Package $package
	 */
	public function __construct($version, \Lohini\Packages\Package $package)
	{
		$this->version=$version;
		$this->package=$package;

		$this->dir=$this->package->getPath().'/Migration';

		if (!is_dir($this->dir)) {
			Filesystem::mkDir($this->dir);
			}
	}

	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * Finds any existing migrations with same version and deletes them.
	 */
	public function removeExisting()
	{
		foreach (\Nette\Utils\Finder::findFiles($this->version.'*')->in($this->dir) as $file) {
			Filesystem::rm($file);
			}
	}

	/**
	 * @param array $sqls
	 * @return bool
	 */
	abstract public function write(array $sqls);
}
