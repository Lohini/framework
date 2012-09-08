<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations\Console;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class MigrationsManagerHelper
extends \Symfony\Component\Console\Helper\Helper
{
	/** @var \Lohini\Database\Migrations\MigrationsManager */
	protected $manager;


	/**
	 * @param \Lohini\Database\Migrations\MigrationsManager $manager
	 */
	public function __construct(\Lohini\Database\Migrations\MigrationsManager $manager)
	{
		$this->manager=$manager;
	}

	/**
	 * @return \Lohini\Database\Migrations\MigrationsManager
	 */
	public function getMigrationsManager()
	{
		return $this->manager;
	}

	/**
	 * @see \Symfony\Component\Console\Helper\Helper::getName()
	 */
	public function getName()
	{
		return 'migrationsManager';
	}
}
