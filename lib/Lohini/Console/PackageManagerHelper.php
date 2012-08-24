<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Console;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class PackageManagerHelper
extends \Symfony\Component\Console\Helper\Helper
{
	/** @var \Lohini\Packages\PackageManager */
	protected $packageManager;


	/**
	 * @param \Lohini\Packages\PackageManager $packageManager
	 */
	public function __construct(\Lohini\Packages\PackageManager $packageManager)
	{
		$this->packageManager=$packageManager;
	}

	/**
	 * @return \Lohini\Packages\PackageManager
	 */
	public function getPackageManager()
	{
		return $this->packageManager;
	}

	/**
	 * @see \Symfony\Component\Console\Helper\Helper::getSelector()
	 */
	public function getName()
	{
		return 'packageManager';
	}
}
