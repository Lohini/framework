<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application\UI;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * @property \Nette\Templating\FileTemplate|\stdClass $template
 * @property-read \Nette\Templating\FileTemplate|\stdClass $template
 * @method \Nette\Templating\FileTemplate|\stdClass getTemplate() getTemplate()
 *
 * @property \Lohini\Application\UI\Presenter $presenter
 * @property-read \Lohini\Application\UI\Presenter $presenter
 * @method \Lohini\Application\UI\Presenter getPresenter() getPresenter(bool $need = TRUE)
 */
abstract class Control
extends \Nette\Application\UI\Control
{
	/** @var \Lohini\Templating\TemplateConfigurator */
	protected $templateConfigurator;


	/**
	 * @param \Lohini\Templating\TemplateConfigurator $configurator
	 */
	public function setTemplateConfigurator(\Lohini\Templating\TemplateConfigurator $configurator=NULL)
	{
		$this->templateConfigurator=$configurator;
	}

	/**
	 * @param string|NULL $class
	 * @return \Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class=NULL)
	{
		$template=parent::createTemplate($class);
		if ($file=$this->getTemplateDefaultFile()) {
			$template->setFile($file);
			}

		if ($this->templateConfigurator!==NULL) {
			$this->templateConfigurator->configure($template);
			}

		return $template;
	}

	/**
	 * Derives template path from class name.
	 *
	 * @return string|NULL
	 */
	protected function getTemplateDefaultFile()
	{
		$class=$this->getReflection();
		do {
			$file=dirname($class->getFileName()).'/'.$class->getShortName().'.latte';
			if (file_exists($file)) {
				return $file;
				}
			if (!$class=$class->getParentClass()) {
				break;
				}
			} while (TRUE);
	}

	/**
	 * Renders the default template
	 */
	public function render()
	{
		$this->template->render();
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
		if ($element instanceof \Reflector && $presenter=$this->getPresenter(FALSE)) {
			$presenter->getUser()->protectElement($element);
			}
	}
}
