<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Forms\Rendering;

/**
 * Form renderer
 *
 * @author Lopo <lopo@losys.eu>
 */
class FormRenderer
extends \Nette\Forms\Rendering\DefaultFormRenderer
{
	/**
	 * Provides complete form rendering.
	 * @param \Nette\Forms\Form
	 * @param string 'begin', 'errors', 'body', 'end' or empty to render all
	 * @return string
	 */
	public function render(\Nette\Forms\Form $form, $mode=NULL)
	{
		$form->setTranslator($form->getPresenter(FALSE)->getContext()->getService('translator'));
		return parent::render($form, $mode);
	}

	/**
	 * Renders form end.
	 * @return string
	 */
	public function renderEnd()
	{
		$basePath=rtrim($this->form->getPresenter(FALSE)->getContext()->getService('httpRequest')->getUrl()->getBasePath(), '/');
		$class=$this->form->getElementPrototype()->getClass();
		$ajax= (isset($class['ajax']) && $class['ajax'])? ", '$basePath/js/jquery.ajaxform.js', '$basePath/js/nette.ajax.js'" : '';
		return parent::renderEnd()
			.\Nette\Utils\Html::el('script', array('type' => 'text/javascript'))
				->add("head.ready(function() {head.js('$basePath/js/netteForms.js'$ajax);});");
	}
}