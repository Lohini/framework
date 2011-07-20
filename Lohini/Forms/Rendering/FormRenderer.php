<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Forms\Rendering;

/**
 * Form renderer
 *
 * @author Lopo <lopo@lohini.net>
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
		$basePath=rtrim($this->form->getPresenter(FALSE)->getContext()->httpRequest->getUrl()->getBasePath(), '/');
		$class=$this->form->getElementPrototype()->getClass();
		$hA='';
		foreach ($this->form->getControls() as $control) {
			if ($control instanceof \Nette\Forms\Controls\TextArea) {
				$fid=$this->form->getElementPrototype()->id;
				$hA=", function() { $('#$fid textarea').ctrlEnter('button', function() { $('#$fid').submit();});}";
				break;
				}
			}
		$ajax= (isset($class['ajax']) && $class['ajax'])? ", '$basePath/js/jquery.ajaxform.js', '$basePath/js/nette.ajax.js'" : '';
		return parent::renderEnd()
			.\Nette\Utils\Html::el('script', array('type' => 'text/javascript'))
				->add("head.ready(function() {head.js('$basePath/js/netteForms.js', '$basePath/js/lohiniForms.js'$ajax$hA);});");
	}
}