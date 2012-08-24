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
class PackagesList
extends \Nette\Object
implements IPackageList
{
	/** @var array */
	private $packages;


	/**
	 * @param array $packages
	 */
	public function __construct(array $packages)
	{
		$this->packages=$packages;
	}

	/**
	 * @return string[]
	 */
	public function getPackages()
	{
		return $this->packages;
	}
}
