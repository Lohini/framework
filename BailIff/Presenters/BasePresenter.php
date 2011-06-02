<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Presenters;

use Nette\Environment as NEnvironment,
	Nette\Forms\Form;

/**
 * Base presenter class
 * @author Lopo <lopo@losys.eu>
 */
abstract class BasePresenter
extends \BailIff\Application\UI\Presenter
{
	/**#@+ Base presenter flash messages class */
	const FLASH_SUCCESS='success';
	const FLASH_ERROR='error';
	const FLASH_INFO='info';
	const FLASH_WARNING='warning';
	/**#@-*/


	/**
	 * @see Nette\Application\UI.Presenter::startup()
	 */
	protected function startup()
	{
		parent::startup();
		Form::extensionMethod('addPswd', function (Form $form, $name, $label) { return $form[$name]=new \BailIff\Forms\Controls\PswdInput($label); });
		Form::extensionMethod('addCBox3S', function (Form $form, $name, $label) { return $form[$name]=new \BailIff\Forms\Controls\CBox3S($label); });
		Form::extensionMethod('addDatePicker', function (Form $form, $name, $label) { return $form[$name]=new \BailIff\Forms\Controls\DatePicker($label); });
		Form::extensionMethod('addReset', function (Form $form, $name, $label) { return $form[$name]=new \BailIff\Forms\Controls\ResetButton($label); });
	}

	/**
	 * @see Nette\Application\UI.Presenter::beforeRender()
	 */
	protected function beforeRender()
	{
		$user=$this->getUser();
		$this->template->identity= $user->isLoggedIn()? $user->getIdentity() : NULL;
		$this->template->titleSeparator=NEnvironment::getVariable('titleSeparator', ' | ');
		$this->template->lang=$this->lang;
	}

	/**
	 * @param string $class
	 * @see Nette\Application\UI\Control::createTemplate()
	 */
	protected function createTemplate($class=NULL)
	{
		$template=$this->getContext()->templateFactory->createTemplate($this, $class);
//		$template=parent::createTemplate($class);
//		$template->registerHelperLoader('BailIff\Templating\Helpers::loader');
		$template->setTranslator($this->context->translator->setLang($this->lang));
		return $template;
	}

	/**
	 * @see Nette\Application\UI.Presenter::formatLayoutTemplateFiles()
	 */
	public function formatLayoutTemplateFiles()
	{
		$layout= $this->layout? $this->layout : 'layout';
		if (isset($this->context->params['useSkins']) && $this->context->params['useSkins']) {
			$user=$this->getUser();
			if (!$user->isLoggedIn()) {
				$skin='default';
				}
			else {
				$i=$user->getIdentity();
				$skin= isset($i->skin)? $i->skin : 'default';
				}
			$skinDir=realpath(APP_DIR."/skins/$skin");
			}
		else {
			$skinDir=realpath(APP_DIR.'/templates');
			}
		$path='/'.str_replace(':', 'Module/', $this->getName());
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
	 * @see Nette\Application\UI.Presenter::formatTemplateFiles()
	 */
	public function formatTemplateFiles()
	{
		if (isset($this->context->params['useSkins']) && $this->context->params['useSkins']) {
			$user=$this->getUser();
			if (!$user->isLoggedIn()) {
				$skin='default';
				}
			else {
				$i=$user->getIdentity();
				$skin= isset($i->skin)? $i->skin : 'default';
				}
			$skinDir=realpath(APP_DIR."/skins/$skin");
			}
		else {
			$skinDir=realpath(APP_DIR.'/templates');
			}
		$path='/'.str_replace(':', 'Module/', $this->getName());
		$pathP=substr_replace($path, '', strrpos($path, '/'), 0);
		$path=substr_replace($path, '', strrpos($path, '/'));
		return array(
			"$skinDir$pathP/$this->view.latte",
			"$skinDir$pathP.$this->view.latte",
			);
	}

	/**
	 * Creates CssLoader control
	 * @return \BailIff\WebLoader\CssLoader
	 */
	protected function createComponentCss()
	{
		return new \BailIff\WebLoader\CssLoader;
	}

	/**
	 * Creates Jsloader component
	 * @return \BailIff\WebLoader\JsLoader
	 */
	protected function createComponentJs()
	{
		return new \BailIff\WebLoader\JsLoader;
	}

	/**
	 * Creates Gravatar img component
	 * @return \BailIff\Components\Gravatar
	 */
	protected function createComponentGravatar()
	{
		return new \BailIff\Components\Gravatar;
	}
}
