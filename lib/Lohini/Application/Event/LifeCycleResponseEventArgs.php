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
class LifeCycleResponseEventArgs
extends LifeCycleEventArgs
{
	/** @var \Nette\Application\IResponse */
	private $response;


	/**
	 * @param \Lohini\Application\Application $application
	 * @param \Nette\Application\IResponse $response
	 */
	public function __construct(\Lohini\Application\Application $application, \Nette\Application\IResponse $response)
	{
		parent::__construct($application);
		$this->response=$response;
	}

	/**
	 * @return \Nette\Application\IResponse
	 */
	public function getResponse()
	{
		return $this->response;
	}
}
