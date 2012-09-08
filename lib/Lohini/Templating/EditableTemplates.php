<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Templating;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Caching\Cache;

/**
 */
class EditableTemplates
extends \Nette\Object
{
	const CACHE_NS='Lohini.EditableTemplates';

	/** @var \Lohini\Database\Doctrine\Dao */
	private $sourcesDao;
	/** @var \Lohini\Caching\LatteStorage */
	private $latteStorage;
	/** @var \Nette\Caching\IStorage */
	private $cacheStorage;


	/**
	 * @param \Lohini\Database\Doctrine\Registry $doctrine
	 * @param \Lohini\Caching\LatteStorage $latteStorage
	 * @param \Nette\Caching\IStorage $cacheStorage
	 */
	public function __construct(\Lohini\Database\Doctrine\Registry $doctrine, \Lohini\Caching\LatteStorage $latteStorage, \Nette\Caching\IStorage $cacheStorage=NULL)
	{
		$this->sourcesDao=$doctrine->getDao('Lohini\Templating\TemplateSource');
		$this->latteStorage=$latteStorage;
		$this->cacheStorage=$cacheStorage;
	}

	/**
	 * @param TemplateSource $template
	 */
	public function invalidate(TemplateSource $template)
	{
		$this->latteStorage->clean(array(
			Cache::TAGS => array('dbTemplate#'.$template->getId())
			));

		if ($this->cacheStorage!==NULL) {
			$this->cacheStorage->clean(array(
				Cache::TAGS => array('dbTemplate#'.$template->getId())
				));
			}
	}

	/**
	 * @param \Lohini\Templating\TemplateSource $template
	 */
	public function save(TemplateSource $template)
	{
		static $trigger;
		if (!isset($trigger)) {
			$trigger=$template;
			}

		if ($extended=$template->getExtends()) {
			$this->save($extended);
			}

		$dp=array();
		if ($source=$template->build($this, $dp)) {
			$this->latteStorage->write(self::CACHE_NS.Cache::NAMESPACE_SEPARATOR.$template->getId(), $source, $dp);
			}

		if (isset($trigger) && $trigger===$template) {
			$this->sourcesDao->save($trigger);
			$trigger=NULL;
			}
	}

	/**
	 * @param \Lohini\Templating\TemplateSource $template
	 */
	public function remove(TemplateSource $template)
	{
		$this->invalidate($template);
		$this->sourcesDao->delete($template);
	}

	/**
	 * @param \Lohini\Templating\TemplateSource $template
	 * @param string $layoutFile
	 * @return string
	 * @throws \Nette\InvalidStateException
	 * @throws \Lohini\FileNotFoundException
	 */
	public function getTemplateFile(TemplateSource $template, $layoutFile=NULL)
	{
		if (!$template->getId()) {
			$this->save($template);
			}

		$key=self::CACHE_NS.Cache::NAMESPACE_SEPARATOR.$template->getId();
		if ($layoutFile!==NULL) {
			$key.='.l'.substr(md5(serialize($layoutFile)), 0, 8);
			}

		// load or save
		if (!$cached=$this->latteStorage->read($key)) {
			$dp=array();
			$this->latteStorage->write($key, $template->build($this, $dp, $layoutFile), $dp);
			$cached=$this->latteStorage->read($key);
			}

		if ($cached===NULL) {
			throw new \Nette\InvalidStateException('No template found.');
			}
		if (!file_exists($cached['file'])) {
			throw \Lohini\FileNotFoundException::fromFile($cached['file']);
			}

		@fclose($cached['handle']);
		return $cached['file'];
	}
}
