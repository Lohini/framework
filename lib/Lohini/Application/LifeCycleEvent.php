<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Application\Request,
	Nette\Application\IResponse;

/**
 */
final class LifeCycleEvent
extends \Nette\Object
{
	/** Occurs before the application loads presenter */
	const onStartup='onStartup';
	/** Occurs before the application shuts down */
	const onShutdown='onShutdown';
	/** Occurs when a new request is ready for dispatch */
	const onRequest='onRequest';
	/** Occurs when a new response is received */
	const onResponse='onResponse';
	/** Occurs when an unhandled exception occurs in the application */
	const onError='onError';


	/**
	 * @param \Nette\Application\Application $app
	 * @param \Lohini\Extension\EventDispatcher\EventManager $evm
	 */
	public static function register(\Nette\Application\Application $app, \Lohini\Extension\EventDispatcher\EventManager $evm)
	{
		$app->onStartup[]=function($application) use ($evm) {
			/** @var \Lohini\Extension\EventDispatcher\EventManager $evm */
			if ($evm->hasListeners(LifeCycleEvent::onStartup)) {
				$args=new Event\LifeCycleEventArgs($application);
				$evm->dispatchEvent(LifeCycleEvent::onStartup, $args);
				}
			};

		$app->onRequest[]=function($application, Request $request) use ($evm) {
			/** @var \Lohini\Extension\EventDispatcher\EventManager $evm */
			if ($evm->hasListeners(LifeCycleEvent::onRequest)) {
				$args=new Event\LifeCycleRequestEventArgs($application, $request);
				$evm->dispatchEvent(LifeCycleEvent::onRequest, $args);
				}
			};

		$app->onResponse[]=function($application, IResponse $response) use ($evm) {
			/** @var \Lohini\Extension\EventDispatcher\EventManager $evm */
			if ($evm->hasListeners(LifeCycleEvent::onResponse)) {
				$args=new Event\LifeCycleResponseEventArgs($application, $response);
				$evm->dispatchEvent(LifeCycleEvent::onResponse, $args);
				}
			};

		$app->onError[]=function($application, \Exception $exception=NULL) use ($evm) {
			/** @var \Lohini\Extension\EventDispatcher\EventManager $evm */
			if ($evm->hasListeners(LifeCycleEvent::onError)) {
				$args=new Event\LifeCycleEventArgs($application, $exception);
				$evm->dispatchEvent(LifeCycleEvent::onError, $args);
				}
			};

		$app->onShutdown[]=function($application, \Exception $exception=NULL) use ($evm) {
			/** @var \Lohini\Extension\EventDispatcher\EventManager $evm */
			if ($evm->hasListeners(LifeCycleEvent::onShutdown)) {
				$args=new Event\LifeCycleEventArgs($application, $exception);
				$evm->dispatchEvent(LifeCycleEvent::onShutdown, $args);
				}
			};
	}
}
