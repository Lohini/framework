<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application\Event;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class LifeCycleEventArgs
extends \Lohini\Extension\EventDispatcher\EventArgs
{
	/** @var \Lohini\Application\Application */
	private $application;
	/** @var \Exception */
	private $exception;


	/**
	 * @param \Lohini\Application\Application $application
	 * @param \Exception|NULL $exception
	 */
	public function __construct(\Lohini\Application\Application $application, \Exception $exception=NULL)
	{
		$this->application=$application;
		$this->exception=$exception;
	}

	/**
	 * @return \Lohini\Application\Application
	 */
	public function getApplication()
	{
		return $this->application;
	}

	/**
	 * @return \Exception
	 */
	public function getException()
	{
		return $this->exception;
	}
}
