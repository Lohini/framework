<?php // vim: ts=4 sw=4 ai:
namespace BailIff\WebLoader;

use Nette\Web\Html,
	Nette\Environment as NEnvironment,
	Nette\IComponentContainer,
	Nette\Debug,
	BailIff\WebLoader\Filters\LessFilter,
	BailIff\WebLoader\Filters\CCssFilter,
	BailIff\WebLoader\Filters\CssUrlsFilter;

/**
 * CssLoader
 *
 * @author Jan Marek
 * @license MIT
 * @author Lopo <lopo@losys.eu> BailIff port
 */
class CssLoader
extends WebLoader
{
	/** @var bool */
	private $absolutizeUrls=TRUE;

	/**
	 * Construct
	 * @param IComponentContainer $parent
	 * @param string $name
	 */
	public function __construct(IComponentContainer $parent=NULL, $name=NULL)
	{
		parent::__construct($parent, $name);
		$this->setGeneratedFileNamePrefix('cssldr-');
		$this->setGeneratedFileNameSuffix('.css');
		$this->sourcePath=WWW_DIR.'/css';
		$this->sourceUri=NEnvironment::getVariable('baseUri').'css/';
		$this->contentType='text/css';
		$this->preFileFilters[]=new LessFilter;
		$this->preFileFilters[]=new CCssFilter;
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
	 * (non-PHPdoc)
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
				if (NEnvironment::isProduction()) {
					throw new \FileNotFoundException("File '$this->sourcePath/$file' doesn't exist.");
					}
				else {
					Debug::log(new \FileNotFoundException("File '$this->sourcePath/$file' doesn't exist."), Debug::ERROR);
					return;
					}
				}
			}
		$this->files[]=array($file, $media);
	}

	/**
	 * (non-PHPdoc)
	 * @see BailIff\WebLoader.WebLoader::renderFiles()
	 */
	public function renderFiles()
	{
		if (count($this->files)==1 && substr($this->files[0][0], -4)=='.css') { // single raw, don't parse|cache
			echo $this->getElement($this->sourceUri.$this->files[0][0], $this->files[0][1]);
			return;
			}
		$filesByMedia=array();
		foreach ($this->files as $f) {
			$filesByMedia[$f[1]][]=$f[0];
			}
		foreach ($filesByMedia as $media => $filenames) {
			if ($this->joinFiles) {
				echo $this->getElement($this->getPresenter()->link('WebLoader', $this->generate($filenames)), $media);
				}
			else {
				foreach ($filenames as $filename) {
					echo $this->getElement($this->getPresenter()->link('WebLoader', $this->generate(array($filename))), $media);
					}
				}
			}
	}

	/**
	 * (non-PHPdoc)
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
	 * Generate compiled+compacted file and render link
	 * @example {control css:compact 'file.css', 'file2.css'}
	 */
	public function renderCompact()
	{
		if (($hasArgs=(func_num_args()>0)) && func_num_args()==1) {
			$arg=func_get_arg(0);
			$file= is_array($arg)? $arg : array($arg => 'all');
			if (strtolower(substr(key($file), -4))=='.css') {
				echo $this->getElement($this->sourceUri.key($file), current($file));
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
			echo $this->getElement($this->getPresenter()->link('WebLoader', $this->generate($filenames)), $media);
			}
		if ($hasArgs) {
			$this->files=$backup;
			}
	}
}