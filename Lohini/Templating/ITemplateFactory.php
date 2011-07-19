<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Templating;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip ProchĂˇzka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip ProchĂˇzka
 */

interface ITemplateFactory
{
	/**
	 * @param \Nette\ComponentModel\Component $component
	 * @return \Nette\Templating\ITemplate
	 */
	function createTemplate(\Nette\ComponentModel\Component $component);
}
