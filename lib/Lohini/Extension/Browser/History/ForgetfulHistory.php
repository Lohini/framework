<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Browser\History;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class ForgetfulHistory extends EagerHistory
{
	/**
	 * @param \Lohini\Extension\Browser\WebPage|\stdClass $content
	 * @param \Lohini\Extension\Curl\Request|NULL $request
	 * @param \Lohini\Extension\Curl\Response|NULL $response
	 */
	public function push($content, \Lohini\Extension\Curl\Request $request=NULL, \Lohini\Extension\Curl\Response $response=NULL)
	{
		$this->clean();
		parent::push($content, $request, $response);
	}
}
