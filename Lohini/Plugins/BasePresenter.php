<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Plugins;

/**
 * Base presenter
 *
 * @author Lopo <lopo@lohini.net>
 */
class BasePresenter
extends \Lohini\Presenters\BasePresenter
{
	/**
	 * @see \Nette\Application\UI\Presenter::formatLayoutTemplateFiles()
	 */
	public function formatLayoutTemplateFiles()
	{
		list($pname, $name)=explode(':', $this->getName(), 2);
		$path='/'.str_replace(':', 'Module/', $name);
		$pathP=substr_replace($path, '', strrpos($path, '/'), 0);
		$layout= $this->layout? $this->layout : 'layout';
		$list=array();
		if (isset($this->context->params['useSkins']) && $this->context->params['useSkins']) {
			$user=$this->getUser();
			if (!$user->isLoggedIn()) {
				$skin='default';
				}
			else {
				$i=$user->getIdentity();
				$skin= (isset($i->skin) && $i->skin)? $i->skin : 'default';
				}
			
			$skinDir=APP_DIR."/Plugins/$pname/skins/$skin";
			$list=array(
				"$skinDir$pathP/@$layout.latte",
				"$skinDir$pathP.@$layout.latte",
				);
			$pathx=$path;
			while (($pathx=substr($pathx, 0, strrpos($pathx, '/')))!==FALSE) {
				$list[]="$skinDir$pathx/@$layout.latte";
				}
			$al=APP_DIR."/skins/$skin/@$layout.latte";
			}
		$tplDir=APP_DIR."/Plugins/$pname/templates";
		$atplDir=APP_DIR.'/templates';
		$list[]="$tplDir$pathP/@$layout.latte";
		$list[]="$tplDir$pathP.@$layout.latte";
		while (($path=substr($path, 0, strrpos($path, '/')))!==FALSE) {
			$list[]="$tplDir$path/@$layout.latte";
			}
		if (isset($al)) {
			$list[]=$al;
			}
		$list[]="$atplDir/@$layout.latte";
		return $list;
	}

	/**
	 * @see \Nette\Application\UI\Presenter::formatTemplateFiles()
	 */
	public function formatTemplateFiles()
	{
		list($pname, $name)=explode(':', $this->getName(), 2);
		$path='/'.str_replace(':', 'Module/', $name);
		$pathP=substr_replace($path, '', strrpos($path, '/'), 0);
		$path=substr_replace($path, '', strrpos($path, '/'));
		$list=array();
		if (isset($this->context->params['useSkins']) && $this->context->params['useSkins']) {
			$user=$this->getUser();
			if (!$user->isLoggedIn()) {
				$skin='default';
				}
			else {
				$i=$user->getIdentity();
				$skin= (isset($i->skin) && $i->skin)? $i->skin : 'default';
				}
			$skinDir=realpath(APP_DIR."/Plugins/$pname/skins/$skin");
			$list=array(
				"$skinDir$pathP/$this->view.latte",
				"$skinDir$pathP.$this->view.latte",
				);
			}
		$skinDir=realpath(APP_DIR."/Plugins/$pname/templates");
		$list[]="$skinDir$pathP/$this->view.latte";
		$list[]="$skinDir$pathP.$this->view.latte";
		return $list;
	}

	/**
	 * Creates CssLoader control
	 * @return \Lohini\WebLoader\CssLoader
	 */
	protected function createComponentPcss()
	{
		list($pname)=explode(':', $this->getName());
		$ldr=new \Lohini\WebLoader\CssLoader;
		$ldr->setSourcePath(APP_DIR."/Plugins/$pname/htdocs/css");
		$ldr->setEnableDirect(FALSE);
		return $ldr;
	}

	/**
	 * Creates Jsloader component
	 * @return \Lohini\WebLoader\JsLoader
	 */
	protected function createComponentPjs()
	{
		list($pname)=explode(':', $this->getName());
		$ldr=new \Lohini\WebLoader\JsLoader;
		$ldr->setSourcePath(APP_DIR."/Plugins/$pname/htdocs/js");
		$ldr->setEnableDirect(FALSE);
		return $ldr;
	}
}
