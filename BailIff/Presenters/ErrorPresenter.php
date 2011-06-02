<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Presenters;

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
			\Nette\Diagnostics\Debugger::log($exception, Debugger::ERROR); // and log exception
			}
	}
}
