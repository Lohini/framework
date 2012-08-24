<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application\UI;
/**
* @author Filip Procházka
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Diagnostics\Debugger;

/**
 * @property-read \SystemContainer|\Nette\DI\Container $container
 * @property-read \Lohini\Security\User $user
 * @property-read \Nette\Templating\FileTemplate $template
 * @property-read \Lohini\Database\Doctrine\Registry $doctrine
 *
 * @method \Lohini\Security\User getUser() getUser()
 * @method \SystemContainer|\Nette\DI\Container getContext() getContext()
 */
class Presenter
extends \Nette\Application\UI\Presenter
{
	/**
	 * @persistent
	 * @var string
	 */
	public $backlink;
	/** @var \Lohini\Templating\TemplateConfigurator */
	protected $templateConfigurator;


	/**
	 * Add namespace into payload.
	 */
	protected function startup()
	{
		parent::startup();
		$this->payload->lohini=(object)array();
	}

	/**
	 * @return \Lohini\Database\Doctrine\Registry
	 */
	public function getDoctrine()
	{
		return $this->getContext()->doctrine->registry;
	}

	/**
	 * @param string $entity
	 * @return \Lohini\Database\Doctrine\Dao
	 */
	public function getRepository($entity)
	{
		return $this->getDoctrine()->getDao($entity);
	}

	/**
	 * @param \Lohini\Templating\TemplateConfigurator $configurator
	 */
	public function setTemplateConfigurator(\Lohini\Templating\TemplateConfigurator $configurator=NULL)
	{
		$this->templateConfigurator=$configurator;
	}

	/**
	 * @param string $class
	 * @return \Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class=NULL)
	{
		$template=parent::createTemplate($class);
		if ($this->templateConfigurator!==NULL) {
			$this->templateConfigurator->configure($template);
			}

		return $template;
	}

	/**
	 * @param \Nette\Templating\Template $template
	 */
	public function templatePrepareFilters($template)
	{
		$engine=$this->getPresenter()->getContext()->nette->createLatte();
		if ($this->templateConfigurator!==NULL) {
			$this->templateConfigurator->prepareFilters($engine);
			}
		$template->registerFilter($engine);
	}

	/**
	 * If Debugger is enabled, print template variables to debug bar
	 * @todo enable/disable ?
	 */
//	protected function afterRender()
//	{
//		$this->invalidateControl('flashMessage');
//		parent::afterRender();
//
//		if (Debugger::isEnabled()) { // TODO: as panel
//			Debugger::barDump($this->template->getParameters(), 'Template variables');
//			}
//	}

	/**
	 * @see \Nette\Application\UI\Presenter::formatLayoutTemplateFiles()
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		$layout= $this->layout? $this->layout : 'layout';
		$skinDir=$this->getSkinDir();
		$ctx=$this->context;
		if ($this->isInPackage()) {
			$presenter=substr($name=$this->getName(), strrpos(':'.$name, ':'));
			$mapper=function($views) use ($presenter, $layout) {
				return array(
					"$views/$presenter/@$layout.latte",
					"$views/$presenter.@$layout.latte",
					"$views/@$layout.latte"
					);
				};
			return array_unique(array_filter(
					array_merge(
						$mapper(realpath(dirname($this->getReflection()->getFileName())."/../Resources/$skinDir")),
						$mapper(realpath(dirname($this->getReflection()->getFileName())."/../Resources/templates")),
						$mapper($ctx->expand("%appDir%/$skinDir"))
						),
					function($file) use ($ctx) {
						return \Nette\Utils\Strings::startsWith($file, $ctx->expand('%appDir%'));
						}
					));
			}
		$path='/'.str_replace(':', 'Module/', $this->getName());
		$pathP=substr_replace($path, '', strrpos($path, '/'), 0);
		$list=array(
			$ctx->expand("%appDir%/$skinDir$pathP/@$layout.latte"),
			$ctx->expand("%appDir%/$skinDir$pathP.@$layout.latte"),
			);
		while (($path=substr($path, 0, strrpos($path, '/')))!==FALSE) {
			$list[]=$ctx->expand("%appDir%/$skinDir$path/@$layout.latte");
			}
		return $list;
	}

	/**
	 * @see \Nette\Application\UI\Presenter::formatTemplateFiles()
	 */
	public function formatTemplateFiles()
	{
		$skinDir=$this->getSkinDir();
		$view=$this->view;
		$ctx=$this->context;
		if ($this->isInPackage()) {
			$presenter=substr($name=$this->getName(), strrpos(':'.$name, ':'));
			$mapper=function($views) use ($presenter, $view) {
				return array(
					"$views/$presenter/$view.latte",
					"$views/$presenter.$view.latte"
					);
				};
			return array_unique(array_filter(
					array_merge(
						$mapper(realpath(dirname($this->getReflection()->getFileName())."/../Resources/$skinDir")),
						$mapper(realpath(dirname($this->getReflection()->getFileName())."/../Resources/templates")),
						$mapper($ctx->expand("%appDir%/$skinDir"))
						),
					function($file) use ($ctx) {
						return \Nette\Utils\Strings::startsWith($file, $ctx->expand('%appDir%'));
						}
					));
			}
		$path='/'.str_replace(':', 'Module/', $this->getName());
		$pathP=substr_replace($path, '', strrpos($path, '/'), 0);
		$path=substr_replace($path, '', strrpos($path, '/'));
		return array(
			$ctx->expand("%appDir%/$skinDir$pathP/$view.latte"),
			$ctx->expand("%appDir%/$skinDir$pathP.$view.latte")
			);
	}

	/**
	 * @return string 
	 */
	private function getSkinDir()
	{
		if (!isset($this->context->parameters['useSkins']) || !$this->context->parameters['useSkins']) {
			return 'templates';
			}
		$user=$this->getUser();
		if (!$user->isLoggedIn()) {
			$skin='default';
			}
		else {
			$i=$user->getIdentity();
			$skin= (isset($i->skin) && $i->skin)? $i->skin : 'default';
			}
		return "skins/$skin";
	}

	/**
	 * Presenter is in package, when "Package" keyword is in it's namespace
	 * and "Module" keyword isn't. Because packages disallow modules.
	 *
	 * @return bool
	 */
	private function isInPackage()
	{
		return stripos(get_called_class(), 'Package\\')!==FALSE
				&& stripos(get_called_class(), 'Module\\')===FALSE;
	}

	/**
	 * Sends AJAX payload to the output.
	 *
	 * @param array|object|NULL $payload
	 * @throws \Nette\Application\AbortException
	 */
	public function sendPayload($payload=NULL)
	{
		if ($payload!==NULL) {
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse($payload));
			}

		parent::sendPayload();
	}

	/**
	 * @param string $name
	 * @return \Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		$method='createComponent'.ucfirst($name);
		if (method_exists($this, $method)) {
			$this->checkRequirements($this->getReflection()->getMethod($method));
			}

		return parent::createComponent($name);
	}

	/**
	 * Checks for requirements such as authorization.
	 *
	 * @param \Reflector $element
	 */
	public function checkRequirements($element)
	{
		if ($element instanceof \Reflector) {
			$this->getUser()->protectElement($element);
			}
	}
}
