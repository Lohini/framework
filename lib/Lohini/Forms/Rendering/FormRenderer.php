<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Forms\Rendering;

use Nette\Utils\Html;

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
		$ajax= '';
		$class=$this->form->getElementPrototype()->getClass();
		if (isset($class['ajax']) && $class['ajax']) {
			$ajax="'$basePath/js/jquery.ajaxform.js', '$basePath/js/nette.ajax.js',";
			}
		return parent::renderEnd()
			.Html::el('script')
				->setText("head.js(
						'$basePath/js/netteForms.js',
						'$basePath/js/lohiniForms.js',
						$ajax
						function() {
							});");
	}
}