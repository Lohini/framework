<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Presenters;

use Nette\Application\UI\Presenter,
	Nette\Environment as NEnvironment,
	Nette\Forms\Form,
	BailIff\Environment,
	BailIff\WebLoader\CssLoader,
	BailIff\WebLoader\JsLoader,
	BailIff\Forms\Controls\PswdInput,
	BailIff\Forms\Controls\CBox3S,
	BailIff\Forms\Controls\DatePicker,
	BailIff\Forms\Controls\ResetButton,
	BailIff\Components\Gravatar;

/**
 * Base presenter class
 * @author Lopo <lopo@losys.eu>
 */
abstract class BasePresenter
extends Presenter
{
	/**#@+ Base presenter flash messages class */
	const FLASH_SUCCESS='success';
	const FLASH_ERROR='error';
	const FLASH_INFO='info';
	const FLASH_WARNING='warning';
	/**#@-*/
	/**
	 * @var string
	 * @persistent string
	 */
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
		Form::extensionMethod('addReset', function (Form $form, $name, $label) { return $form[$name]=new ResetButton($label); });
	}

	/**
	 * (non-PHPdoc)
	 * @see Nette\Application.Presenter::beforeRender()
	 */
	protected function beforeRender()
	{
		$user=NEnvironment::getUser();
		$this->template->user= $user->isLoggedIn()? $user->getIdentity() : NULL;
		$this->template->webName=SITE;
		$this->template->titleSeparator=Environment::getConfig('titleSeparator', ' | ');
		$this->template->lang=$this->lang;
	}

	/**
	 * (non-PHPdoc)
	 * @see Nette\Application.Control::createTemplate()
	 */
	protected function createTemplate()
	{
		$template=parent::createTemplate();
		$template->registerHelperLoader('BailIff\Templating\TemplateHelpers::loader');
		$translator=NEnvironment::getService('Nette\Localization\ITranslator');
		$translator->setLang($this->lang);
		$template->setTranslator($translator);
		return $template;
	}

	/**
	 * (non-PHPdoc)
	 * @see Nette\Application.Presenter::formatLayoutTemplateFiles()
	 */
	public function formatLayoutTemplateFiles($presenter, $layout)
	{
		$user=NEnvironment::getUser();
		$i=$user->getIdentity();
		$skin= ($user->isLoggedIn() && isset($i->skin))? $i->skin : 'default';
		$skinDir=realpath(APP_DIR."/skins/$skin");
		$path='/'.str_replace(':', 'Module/', $presenter);
		$pathP=substr_replace($path, '', strrpos($path, '/'), 0);
		$list=array(
			"$skinDir$pathP/@$layout.latte",
			"$skinDir$pathP.@$layout.latte",
			);
		while (($path=substr($path, 0, strrpos($path, '/')))!==FALSE) {
			$list[]="$skinDir$path/@$layout.latte";
			}
		return $list;
	}

	/**
	 * (non-PHPdoc)
	 * @see Nette\Application.Presenter::formatTemplateFiles()
	 */
	public function formatTemplateFiles($presenter, $view)
	{
		$user=NEnvironment::getUser();
		$i=$user->getIdentity();
		$skin= ($user->isLoggedIn() && isset($i->skin))? $i->skin : 'default';
		$skinDir=realpath(NEnvironment::getVariable('appDir')."/skins/$skin");
		$path='/'.str_replace(':', 'Module/', $presenter);
		$pathP=substr_replace($path, '', strrpos($path, '/'), 0);
		$path=substr_replace($path, '', strrpos($path, '/'));
		return array(
			"$skinDir$pathP/$view.latte",
			"$skinDir$pathP.$view.latte",
			);
	}

	/**
	 * Creates CssLoader control
	 * @return CssLoader
	 */
	protected function createComponentCss()
	{
		return new CssLoader;
	}

	/**
	 * Creates Jsloader component
	 * @return JsLoader
	 */
	protected function createComponentJs()
	{
		return new JsLoader;
	}

	/**
	 * Creates Gravatar img component
	 * @return Gravatar
	 */
	protected function createComponentGravatar()
	{
		return new Gravatar;
	}
}
