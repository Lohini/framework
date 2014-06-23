<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
namespace Lohini\Application\UI;

/**
 * Application base presenter
 *
 * @author Lopo <lopo@lohini.net>
 */
abstract class Presenter
extends \Nette\Application\UI\Presenter
{
	/** @var \Lohini\Templating\ITemplateFilesFormatter */
	protected $templateFilesFormatter;


	/**
	 * @param \Lohini\Templating\ITemplateFilesFormatter $formatter
	 */
	public function injectTemplateFilesFormatter(\Lohini\Templating\ITemplateFilesFormatter $formatter)
	{
		$this->templateFilesFormatter=$formatter;
	}

	/**
	 * @return \Lohini\Templating\ITemplateFilesFormatter
	 */
	public function getTemplateFilesFormatter()
	{
		return $this->templateFilesFormatter;
	}

	/**
	 * @param string $class
	 * @return \Nette\Templating\ITemplate
	 */
	protected function createTemplate($class=NULL)
	{
		/** @var \Nette\Templating\FileTemplate|\stdClass $template */
		$template=parent::createTemplate($class);

		$template->registerHelper('mtime', function ($f) {
			return $f.'?v='.filemtime($this->context->parameters['wwwDir'].'/'.$f);
			});

		return $template;
	}

	/**
	 * Formats layout template file names
	 *
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		return array_unique(array_merge(
				$this->getTemplateFilesFormatter()->formatLayoutTemplateFiles($this->getName(), $this->getLayout() ?: 'layout'),
				parent::formatLayoutTemplateFiles()
				));
	}

	/**
	 * Formats view template file names
	 *
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		return array_unique(array_merge(
				$this->getTemplateFilesFormatter()->formatTemplateFiles($this->getName(), $this->getView()),
				parent::formatTemplateFiles()
				));
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->invalidateControl('flashMessages');
	}
}
