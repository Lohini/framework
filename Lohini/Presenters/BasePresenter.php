<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Presenters;

/**
 * Base presenter class
 * @author Lopo <lopo@lohini.net>
 */
abstract class BasePresenter
extends \Lohini\Application\UI\Presenter
{
	/**#@+ Base presenter flash messages */
	const FLASH_SUCCESS='success';
	const FLASH_ERROR='error';
	const FLASH_INFO='info';
	const FLASH_WARNING='warning';
	/**#@-*/
	/** @var string */
	protected $loginLink=':Core:Auth:login';


	/**
	 * @see \Nette\Application\UI\Presenter::beforeRender()
	 */
	protected function beforeRender()
	{
		$user=$this->getUser();
		$this->template->identity= $user->isLoggedIn()? $user->getIdentity() : NULL;
		$this->template->titleSeparator= $this->context->getParam('titleSeparator', ' | ');
		$this->template->lang=$this->lang;
	}

	/**
	 * @see \Nette\Application\UI\Presenter::afterRender()
	 */
	protected function afterRender()
	{
		$this->invalidateControl('flashMessage');
		parent::afterRender();
	}

	/**
	 * @see \Nette\Application\UI\Control::createTemplate()
	 */
	protected function createTemplate($class=NULL)
	{
		$template=$this->context->templateFactory->createTemplate($this, $class);
		return $template;
	}

	/**
	 * Creates CssLoader component
	 * @return \Lohini\WebLoader\CssLoader
	 */
	protected function createComponentCss()
	{
		return new \Lohini\WebLoader\CssLoader;
	}

	/**
	 * Creates Jsloader component
	 * @return \Lohini\WebLoader\JsLoader
	 */
	protected function createComponentJs()
	{
		return new \Lohini\WebLoader\JsLoader;
	}

	/**
	 * Creates Gravatar img component
	 * @return \Lohini\Components\Gravatar
	 */
	protected function createComponentGravatar()
	{
		return new \Lohini\Components\Gravatar;
	}

	/**
	 * Creates Texyla JsLoader component
	 * @return \Lohini\WebLoader\TexylaLoader
	 */
	protected function createComponentTexyla()
	{
		return new \Lohini\WebLoader\TexylaLoader;
	}
}
