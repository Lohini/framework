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

use Nette\Diagnostics\Debugger;

/**
 * @property-read \BailIff\DI\Container $context
 * @property Bundle $applicationBundle
 */
class Presenter
extends \Nette\Application\UI\Presenter
{
	/** @persistent */
	public $lang='en';
	/** @persistent */
	public $backlink;


	public function __construct()
	{
		parent::__construct(NULL, NULL);
	}

	/**
	 * @param string $class
	 * @return \Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class=NULL)
	{
		return $this->getContext()->templateFactory->createTemplate($this, $class);
	}

	/**
	 * If Debugger is enabled, print template variables to debug bar
	 */
	protected function afterRender()
	{
		parent::afterRender();

		if (Debugger::isEnabled()) { // todo: as panel
			Debugger::barDump($this->template->getParams(), 'Template variables');
			}
	}
}