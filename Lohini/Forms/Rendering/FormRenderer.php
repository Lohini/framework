<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Forms\Rendering;

use Nette\Forms,
	Nette\Utils\Html;

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
		$ajax= $fnTA= $fnT= $texyla= '';
		$class=$this->form->getElementPrototype()->getClass();
		if (isset($class['ajax']) && $class['ajax']) {
			$ajax="'$basePath/js/jquery.ajaxform.js', '$basePath/js/nette.ajax.js',";
			}
		foreach ($this->form->getControls() as $control) {
			if ($control instanceof Forms\Controls\TextArea && $fnTA=='') {
				$fid=$this->form->getElementPrototype()->id;
				$fnTA="$('#$fid textarea').ctrlEnter('button', function() { $('#$fid').submit();});";
				}
			if ($control instanceof \Lohini\Forms\Controls\Texyla && $fnT=='') {
				$presenter=$this->form->presenter;
				$ldr=new \Lohini\WebLoader\TexylaLoader($presenter, 'texyla');
				$texyla="'".$ldr->getLink()."',";
				$fid=$this->form->getElementPrototype()->id;
				if (!in_array($lng=$presenter->lang, array('en', 'cs', 'sk'))) {
					$lng='en';
					}
				$fnT="$.texyla.setDefaults({
						language: '$lng',
						baseDir: '".\Nette\Environment::getVariable('basePath')."/texyla',
						previewPath: '".$presenter->link(':Texyla:preview')."',
						filesPath: '".$presenter->link(':Texyla:listFiles')."',
						filesUploadPath: '".$presenter->link(':Texyla:upload')."',
						filesMkDirPath: '".$presenter->link(':Texyla:mkDir')."',
						filesRenamePath: '".$presenter->link(':Texyla:rename')."',
						filesDeletePath: '".$presenter->link(':Texyla:delete')."'
						});
					$('#$fid textarea.texyla').texyla({
						toolbar: [
							'h1', 'h2', 'h3', 'h4', null,
							'bold', 'italic', null,
							'center', ['left', 'right', 'justify'], null,
							'ul', 'ol', ['olAlphabetSmall', 'olAlphabetBig', 'olRomans', 'olRomansSmall'], null,
							'link', 'img', 'table', 'emoticon', 'symbol', null,
							'files', 'youtube', 'gravatar', null,
							'color', 'textTransform', null,
							'div', ['html', 'blockquote', 'text', 'comment'], null,
							'code',	['codeHtml', 'codeCss', 'codeJs', 'codePhp', 'codeSql'], 'codeInline', 'html', null,
							['sup', 'sub', 'del', 'acronym', 'hr', 'notexy', 'web']
							],
						bottomRightPreviewToolbar: []
						});";
				}
			}
		return parent::renderEnd()
			.Html::el('link', array('rel' => 'stylesheet', 'href' => "$basePath/texyla/css/style.css"))
			.Html::el('script')
				->setText("head.js(
						'$basePath/js/netteForms.js',
						'$basePath/js/lohiniForms.js',
						$ajax
						$texyla
						function() {
							$fnTA
							$fnT
							});");
	}
}