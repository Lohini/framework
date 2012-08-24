<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\EventDispatcher;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class EventArgsList
extends EventArgs
{
	/** @var array */
	private $args;


	/**
	 * @param array $args
	 */
	public function __construct(array $args)
	{
		$this->args=$args;
	}

	/**
	 * @return array
	 */
	public function getArgs()
	{
		return $this->args;
	}
}
