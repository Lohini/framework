<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\WebLoader;

use Nette\Utils\Html,
	Nette\Diagnostics\Debugger,
	BailIff\WebLoader\Filters\CssUrlsFilter;

/**
 * CssLoader
 *
 * @author Jan Marek
 * @license MIT
 * @author Lopo <lopo@losys.eu>
 */
class CssLoader
extends WebLoader
{
	/** @var bool */
	private $absolutizeUrls=TRUE;

	/**
	 * @param IContainer $parent
	 * @param string $name
	 */
	public function __construct(\Nette\ComponentModel\IContainer $parent=NULL, $name=NULL)
	{
		parent::__construct($parent, $name);
		$this->setGeneratedFileNamePrefix('cssldr-');
		$this->setGeneratedFileNameSuffix('.css');
		$this->sourcePath=WWW_DIR.'/css';
		$this->contentType='text/css';
		$this->preFileFilters[]=new \BailIff\WebLoader\Filters\LessFilter;
		$this->preFileFilters[]=new \BailIff\WebLoader\Filters\CCssFilter;
		$this->preFileFilters[]=new \BailIff\WebLoader\Filters\XCssFilter;
		$this->preFileFilters[]=new \BailIff\WebLoader\Filters\SassFilter;
		$this->fileFilters[]=new CssUrlsFilter;
	}

	/**
	 * Get media
	 * @return string
	 */
	public function getMedia()
	{
		return $this->media;
	}

	/**
	 * Set media
	 * @param string $media
	 * @return CssLoader
	 */
	public function setMedia($media)
	{
		$this->media=$media;
		return $this;
	}

	/**
	 * Set URL absolutization on/off
	 * @param bool $abs
	 */
	public function setAbsolutizeUrls($abs)
	{
		$this->absolutizeUrls=(bool)$abs;
		return $this;
	}

	/**
	 * @see BailIff\WebLoader.WebLoader::addFile()
	 */
	public function addFile($file, $media='all')
	{
		foreach ($this->files as $f) {
			if ($f[0]==$file) {
				return;
				}
			}
		if (!file_exists("$this->sourcePath/$file")) {
			if ($this->throwExceptions) {
				if ($this->getPresenter(FALSE)->getContext()->params['productionMode']) {
					throw new \Nette\FileNotFoundException("File '$this->sourcePath/$file' doesn't exist.");
					}
				else {
					Debugger::log(new \Nette\FileNotFoundException("File '$this->sourcePath/$file' doesn't exist."), Debugger::ERROR);
					return;
					}
				}
			}
		$this->files[]=array($file, $media);
	}

	/**
	 * @see BailIff\WebLoader.WebLoader::renderFiles()
	 */
	public function renderFiles()
	{
		if (count($this->files)==1 && substr($this->files[0][0], -4)=='.css') { // single raw, don't parse|cache
			echo $this->getElement($this->getPresenter(FALSE)->getContext()->getService('httpRequest')->getUrl()->getBaseUrl().'css/'.$this->files[0][0], $this->files[0][1]);
			return;
			}
		$filesByMedia=array();
		foreach ($this->files as $f) {
			$filesByMedia[$f[1]][]=$f[0];
			}
		foreach ($filesByMedia as $media => $filenames) {
			if ($this->joinFiles) {
				echo $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate($filenames)), $media);
				}
			else {
				foreach ($filenames as $filename) {
					echo $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array($filename))), $media);
					}
				}
			}
	}

	/**
	 * @see BailIff\WebLoader.WebLoader::getElement()
	 */
	public function getElement($source, $media='all')
	{
		return Html::el('link')
				->rel('stylesheet')
				->type('text/css')
				->media($media)
				->href($source);
	}

	/**
	 * Generates compiled+compacted file and render link
	 * @example {control css:compact 'file.css', 'file2.css'}
	 */
	public function renderCompact()
	{
		if (($hasArgs=(func_num_args()>0)) && func_num_args()==1) {
			$arg=func_get_arg(0);
			$file= is_array($arg)? key($arg) : $arg;
			$media= is_array($arg)? $arg[$file] : 'all';
			if (strtolower(substr($file, -4))=='.css') {
				echo $this->getElement($this->getPresenter(FALSE)->getContext()->getService('httpRequest')->getUrl()->getBaseUrl().'css/'.$file, $media);
				return;
				}
			}
		if ($hasArgs) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}

		$filesByMedia=array();
		foreach ($this->files as $f) {
			$filesByMedia[$f[1]][]=$f[0];
			}
		foreach ($filesByMedia as $media => $filenames) {
			echo $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate($filenames)), $media);
			}
		if ($hasArgs) {
			$this->files=$backup;
			}
	}

	/**
	 * Generates compiled files and render links
	 * @example {control css:singles 'file.css', 'file2.css'}
	 */
	public function renderSingles()
	{
		if ($hasArgs=(func_num_args()>0)) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}

		foreach ($this->files as $f) {
			if (strtolower(substr($f[0], -4))=='.css') {
				echo $this->getElement($this->getPresenter(FALSE)->getContext()->getService('httpRequest')->getUrl()->getBaseUrl().'css/'.$f[0], $f[1]);
				}
			else {
				echo $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array($f[0]))), $f[1]);
				}
			}
		if ($hasArgs) {
			$this->files=$backup;
			}
	}

	/**
	 * Generates and render links - no processing
	 * @example {control css:static 'file.css', 'file2.css'}
	 */
	public function renderStatic()
	{
		if ($hasArgs=(func_num_args()>0)) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}

		foreach ($this->files as $f) {
			echo $this->getElement($this->getPresenter(FALSE)->getContext()->getService('httpRequest')->getUrl()->getBaseUrl().'css/'.$f[0], $f[1]);
			}
		if ($hasArgs) {
			$this->files=$backup;
			}
	}
}
