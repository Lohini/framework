<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Forms\Rendering;

class FormRenderer
extends \Nette\Forms\Rendering\DefaultFormRenderer
{
	/**
	 * Renders form end.
	 * @return string
	 */
	public function renderEnd()
	{
		$basePath=rtrim($this->form->getPresenter(FALSE)->getContext()->getService('httpRequest')->getUrl()->getBasePath(), '/');
		return parent::renderEnd().\Nette\Utils\Html::el('script', array('type' => 'text/javascript'))
			->add("head.js('$basePath/js/netteForms.js', '$basePath/js/jquery.ajaxform.js', '$basePath/js/nette.ajax.js');");
	}
}