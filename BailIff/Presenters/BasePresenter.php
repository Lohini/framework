<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Presenters;

use Nette\Application\Presenter,
	Nette\Environment as NEnvironment,
	BailIff\WebLoader\WebLoader,
	BailIff\WebLoader\CssLoader,
	BailIff\WebLoader\JsLoader,
	Nette\Web\IHttpResponse,
	Nette\String,
	Nette\Forms\Form;

abstract class Base
extends Presenter
{
	/**
	 * (non-PHPdoc)
	 * @see Nette\Application.Presenter::startup()
	 */
	protected function startup()
	{
		parent::startup();
		Form::extensionMethod('addPswd', function (Form $form, $name, $label) { return $form[$name]=new PswdInput($label); });
	}

	/**
	 * (non-PHPdoc)
	 * @see Nette\Application.Control::createTemplate()
	 */
	protected function createTemplate()
	{
		$template=parent::createTemplate();
		$template->registerHelperLoader('BailIff\Templates\TemplateHelpers::loader');
		return $template;
	}

	/**
	 * Create CssLoader control
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
	 * Create Jsloader component
	 * @return JsLoader
	 */
	protected function createComponentJs()
	{
		return new JsLoader;
	}

	/**
	 * send compiled css/js
	 * @param string $id
	 */
	final public function renderWebLoader($id=NULL)
	{
		$this->setLayout(FALSE);
		if ($id===NULL)
			$this->terminate();
		if (($content=WebLoader::getItem(String::webalize($id)))===NULL)
			$this->terminate(/*IHttpResponse::S404_NOT_FOUND*/); // everything exist, but empty :)
		$sh=$this->getHttpResponse();
		$sh->setHeader("Etag", $content[WebLoader::ETAG]);
		$sh->setExpiration(IHttpResponse::PERMANENT);
//		$sh->setHeader("Cache-Control", "must-revalidate");
		$inm=NEnvironment::getHttpRequest()->getHeader("if-none-match");
		if ($inm && $inm==$content[WebLoader::ETAG]) {
			$sh->setCode(IHttpResponse::S304_NOT_MODIFIED);
			$this->terminate();
			}
		$sh->setContentType($content[WebLoader::CONTENT_TYPE]);
//		$sh->setHeader("Content-Length", String::length($content[WebLoader::CONTENT]));
		echo $content[WebLoader::CONTENT];
		$this->terminate();
	}
}
