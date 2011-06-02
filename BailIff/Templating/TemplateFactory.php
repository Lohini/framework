<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Templating;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

class TemplateFactory
extends \Nette\Object
implements ITemplateFactory
{
	/** @var \Nette\Latte\Engine */
	private $latteEngine;


	/**
	 * @param \Nette\Latte\Engine $latteEngine
	 */
	public function __construct(\Nette\Latte\Engine $latteEngine)
	{
		$this->latteEngine=$latteEngine;
	}

	/**
	 * @param \Nette\ComponentModel\Component $component
	 * @param string $class
	 * @return \BailIff\Templating\FileTemplate
	 */
	public function createTemplate(\Nette\ComponentModel\Component $component, $class=NULL)
	{
		$template= $class? new $class : new \Nette\Templating\FileTemplate;

		// find presenter
		$presenter=$component->getPresenter(FALSE);

		// latte
		$template->onPrepareFilters[]=callback($this, 'templatePrepareFilters');

		// helpers
		$template->registerHelperLoader('\BailIff\Templating\Helpers::loader');

		// default parameters
		$template->control=$component;
		$template->presenter=$presenter;

		// stuff from presenter
		if ($presenter instanceof \Nette\Application\UI\Presenter) {
			$template->setCacheStorage($presenter->getContext()->getService('templateCacheStorage'));
			$template->user=$presenter->getUser();
			$template->netteHttpResponse=$presenter->getContext()->getService('httpResponse');
			$template->netteCacheStorage=$presenter->getContext()->getService('cacheStorage');
			$template->baseUri=$template->baseUrl=$presenter->getContext()->getParam('baseUrl');
			$template->basePath=$presenter->getContext()->getParam('basePath');

			// flash message
			if ($presenter->hasFlashSession()) {
				$id=$this->getParamId('flash');
				$template->flashes=$presenter->getFlashSession()->$id;
			}
		}

		// default flash messages
		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes=array();
			}

		return $template;
	}

	/**
	 * @param \Nette\Templating\ITemplate
	 * @return void
	 */
	public function templatePrepareFilters(\Nette\Templating\ITemplate $template)
	{
		// default filters
		$template->registerFilter($this->latteEngine);
	}
}