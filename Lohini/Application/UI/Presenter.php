<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application\UI;
/**
* This file is part of the Kdyby (http://www.kdyby.org)
*
* Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
*
* @license http://www.kdyby.org/license
* @author Filip Procházka
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Diagnostics\Debugger;

/**
 * @property-read \Lohini\DI\Container $context
 */
class Presenter
extends \Nette\Application\UI\Presenter
{
	/**
	 * @var string
	 * @persistent
	 */
	public $lang;
	/**
	 * @var string
	 * @persistent
	 */
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

		if (Debugger::isEnabled()) { // TODO: as panel
			Debugger::barDump($this->template->getParams(), 'Template variables');
			}
	}

	/**
	 * @see \Nette\Application\UI\Presenter::formatLayoutTemplateFiles()
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
				$skin= (isset($i->skin) && $i->skin)? $i->skin : 'default';
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
	 * @see \Nette\Application\UI\Presenter::formatTemplateFiles()
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
				$skin= (isset($i->skin) && $i->skin)? $i->skin : 'default';
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
}
