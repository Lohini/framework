<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Curl;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
interface IRequestLogger
{
	/**
	 * @param Request $request
	 * @return string the id to pass to response
	 */
	function request(Request $request);

	/**
	 * @param Response $response
	 * @param string $id
	 */
	function response(Response $response, $id);
}
