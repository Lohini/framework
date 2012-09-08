<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Console;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class StorageHelper
extends \Symfony\Component\Console\Helper\Helper
{
	/** @var \Nette\Caching\IStorage */
	protected $storage;


	/**
	 * @param \Nette\Caching\IStorage $storage
	 */
	public function __construct(\Nette\Caching\IStorage $storage)
	{
		$this->storage=$storage;
	}

	/**
	 * @return \Nette\Caching\IStorage
	 */
	public function getStorage()
	{
		return $this->storage;
	}

	/**
	 * @see \Symfony\Component\Console\Helper\Helper::getSelector()
	 */
	public function getName()
	{
		return 'storage';
	}
}
