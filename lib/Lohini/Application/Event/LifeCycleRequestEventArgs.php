<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application\Event;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class LifeCycleRequestEventArgs
extends LifeCycleEventArgs
{
	/** @var \Nette\Application\Request */
	private $request;


	/**
	 * @param \Lohini\Application\Application $application
	 * @param \Nette\Application\Request $response
	 */
	public function __construct(\Lohini\Application\Application $application, \Nette\Application\Request $response)
	{
		parent::__construct($application);
		$this->request=$response;
	}

	/**
	 * @return \Nette\Application\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}
}
