<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\EventDispatcher;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class LazyEventManager
extends EventManager
{
	/** @var \Lohini\Config\TaggedServices */
	private $subscribers;


	/**
	 * @internal
	 * @param \Lohini\Config\TaggedServices $subscribers
	 */
	public function addSubscribers(\Lohini\Config\TaggedServices $subscribers)
	{
		$this->subscribers=$subscribers;
	}

	/**
	 * Registers all found subscribers when needed
	 */
	private function registerSubscribers()
	{
		if ($this->subscribers) {
			$subscribers=$this->subscribers;
			$this->subscribers=NULL;

			foreach ($subscribers as $subscriber) {
				$this->addEventSubscriber($subscriber);
				}
			}
	}

	/**
	 * @param string $eventName
	 * @param \Doctrine\Common\EventArgs|NULL $eventArgs
	 */
	public function dispatchEvent($eventName, \Doctrine\Common\EventArgs $eventArgs=NULL)
	{
		$this->registerSubscribers();
		parent::dispatchEvent($eventName, $eventArgs);
	}

	/**
	 * @param NULL $event
	 * @return array
	 */
	public function getListeners($event=NULL)
	{
		$this->registerSubscribers();
		return parent::getListeners($event);
	}

	/**
	 * @param string $event
	 * @return bool
	 */
	public function hasListeners($event)
	{
		$this->registerSubscribers();
		return parent::hasListeners($event);
	}
}
