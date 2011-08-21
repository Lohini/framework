<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Forms\Rendering;

use Nette\Forms;

/**
 * Form renderer
 *
 * @author Lopo <lopo@lohini.net>
 */
class FormRenderer
extends \Nette\Forms\Rendering\DefaultFormRenderer
{
	/**
	 * Renders form end.
	 * @return string
	 */
	public function renderEnd()
	{
		$basePath=rtrim($this->form->getPresenter(FALSE)->getContext()->httpRequest->getUrl()->getBasePath(), '/');
		$ajax= $fnTA= '';
		$class=$this->form->getElementPrototype()->getClass();
		if (isset($class['ajax']) && $class['ajax']) {
			$ajax=", '$basePath/js/jquery.ajaxform.js', '$basePath/js/nette.ajax.js'";
			}
		foreach ($this->form->getControls() as $control) {
			if ($control instanceof Forms\Controls\TextArea && $fnTA=='') {
				$fid=$this->form->getElementPrototype()->id;
				$fnTA="$('#$fid textarea').ctrlEnter('button', function() { $('#$fid').submit();});";
				continue;
				}
			}
		return parent::renderEnd()
			.\Nette\Utils\Html::el('script', array('type' => 'text/javascript'))
				->setText("head.ready(function() {head.js('$basePath/js/netteForms.js', '$basePath/js/lohiniForms.js'$ajax, function() { $fnTA});});");
	}
}