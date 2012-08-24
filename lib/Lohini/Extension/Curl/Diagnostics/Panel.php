<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Curl\Diagnostics;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Extension\Curl\CurlException;

/**
 */
class Panel
extends \Nette\Object
{
	/**
	 * @param \Exception $e
	 * @return array
	 */
	public function renderException($e)
	{
		if ($e instanceof CurlException && !$e instanceof \Lohini\Extension\Curl\FailedRequestException) {
			return array(
				'tab' => 'Curl',
				'panel' => '<h3>Request</h3>'
					.\Nette\Diagnostics\Helpers::clickableDump($e->getRequest(), TRUE)
					.($e->getResponse()
						? '<h3>Responses</h3>'.static::allResponses($e->getResponse())
						: NULL
						)
				);
			}
	}

	/**
	 * @param \Lohini\Extension\Curl\Response $response
	 * @return string
	 */
	public static function allResponses($response)
	{
		if (!$response instanceof \Lohini\Extension\Curl\Response) {
			return NULL;
			}

		$responses=array(\Nette\Diagnostics\Helpers::clickableDump($response, TRUE));
		while ($response=$response->getPrevious()) {
			$responses[]=\Nette\Diagnostics\Helpers::clickableDump($response, TRUE);
			}
		return implode('', $responses);
	}

	/**
	 * @return \Lohini\Extension\Curl\Diagnostics\Panel
	 */
	public static function register()
	{
		\Nette\Diagnostics\Debugger::$blueScreen
			->addPanel(array($panel=new static(), 'renderException'));
		return $panel;
	}
}
