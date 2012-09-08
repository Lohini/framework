<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Extension\EventDispatcher;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Extension\EventDispatcher\EventArgs;

/**
 */
class EventListenerMock
extends \Nette\Object
implements \Lohini\Extension\EventDispatcher\EventSubscriber
{
	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			'onFoo',
			'onBar'
			);
	}

	/**
	 * @param EventArgs $args
	 */
	public function onFoo(EventArgs $args)
	{
	}

	/**
	 * @param EventArgs $args
	 */
	public function onBar(EventArgs $args)
	{
	}
}
