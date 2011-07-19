<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Presenters;

use \Nette\Diagnostics\Debugger;

/**
 * Error presenter
 */
class ErrorPresenter
extends BasePresenter
{
	/**
	 * @param Exception $exception
	 */
	public function renderDefault($exception)
	{
		if ($this->isAjax()) { // AJAX request? Just note this error in payload.
			$this->payload->error=TRUE;
			$this->terminate();
			}
		elseif ($exception instanceof \Nette\Application\BadRequestException) {
			$code=$exception->getCode();
			$this->setView(in_array($code, array(403, 404, 405, 410, 500))? $code : '4xx'); // load template 403.latte or 404.latte or ... 4xx.latte
			}
		else {
			$this->setView('500'); // load template 500.latte
			Debugger::log($exception, Debugger::ERROR); // and log exception
			}
	}
}