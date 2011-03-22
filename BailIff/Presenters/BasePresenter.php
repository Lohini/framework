<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Presenters;

use Nette\Application\Presenter,
	Nette\Environment as NEnvironment,
	BailIff\WebLoader\CssLoader,
	BailIff\WebLoader\JsLoader,
	Nette\Forms\Form,
	BailIff\Forms\PswdInput,
	BailIff\Forms\CBox3S,
	BailIff\Forms\DatePicker;

abstract class BasePresenter
extends Presenter
{
	/** @persistent string */
	public $lang='en';


	/**
	 * (non-PHPdoc)
	 * @see Nette\Application.Presenter::startup()
	 */
	protected function startup()
	{
		parent::startup();
		Form::extensionMethod('addPswd', function (Form $form, $name, $label) { return $form[$name]=new PswdInput($label); });
		Form::extensionMethod('addCBox3S', function (Form $form, $name, $label) { return $form[$name]=new CBox3S($label); });
		Form::extensionMethod('addDatePicker', function (Form $form, $name, $label) { return $form[$name]=new DatePicker($label); });
	}

	/**
	 * (non-PHPdoc)
	 * @see Nette\Application.Control::createTemplate()
	 */
	protected function createTemplate()
	{
		$template=parent::createTemplate();
		$template->registerHelperLoader('BailIff\Templates\TemplateHelpers::loader');
		$translator=NEnvironment::getService('Nette\ITranslator');
		$translator->setLang($this->lang);
		$template->setTranslator($translator);
		return $template;
	}

	/**
	 * Creates CssLoader control
	 * @return CssLoader
	 */
	protected function createComponentCss()
	{
		$css=new CssLoader;
		// cesta na disku ke zdroji
		$css->setSourcePath(WWW_DIR."/css");
		return $css;
	}

	/**
	 * Creates Jsloader component
	 * @return JsLoader
	 */
	protected function createComponentJs()
	{
		return new JsLoader;
	}
}
