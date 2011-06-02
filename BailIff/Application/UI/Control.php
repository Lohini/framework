<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Application\UI;
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

/**
 * @property-read Presenter $presenter
 * @property \Nette\Templating\FileTemplate $template
 *
 * @method Presenter getPresenter() getPresenter()
 * @method \BailIff\Templates\FileTemplate getTemplate() getTemplate()
 */
class Control
extends \Nette\Application\UI\Control
{
	/** @var \Nette\DI\Container */
	private $context;

	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof \Nette\Application\UI\Presenter) {
			return;
			}

		$this->setContext($obj->getContext());
	}

	/**
	 * @param \Nette\DI\Container $context
	 */
	public function setContext(\Nette\DI\Container $context)
	{
		$this->context=$context;
	}

	/**
	 * @return \BailIff\DI\Container
	 * @throws \Nette\InvalidStateException
	 */
	public function getContext()
	{
		if (!$this->context) {
			throw new \Nette\InvalidStateException("Missing context, component wasn't yet attached to presenter.");
			}
		return $this->context;
	}

	/**
	 * @return \Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class=NULL)
	{
		return $this->getContext()->templateFactory->createTemplate($this, $class);
	}
}
