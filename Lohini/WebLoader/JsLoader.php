<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader;

use Nette\Utils\Html,
	Nette\Utils\Strings,
	Nette\Diagnostics\Debugger;

/**
 * JsLoader
 *
 * @author Jan Marek
 * @license MIT
 * @author Lopo <lopo@lohini.net>
 */
class JsLoader
extends WebLoader
{
	/**#@+*/
	const COMPACT='c';
	const MINIFY='m';
	const PACK='p';
	/**#@-*/
	/** @var array */
	public $codes=array();
	/** @var bool */
	public $useHeadJs=TRUE;

	/**
	 * @param \Nette\ComponentModel\IContainer parent
	 * @param string name
	 */
	public function __construct(\Nette\ComponentModel\IContainer $parent=NULL, $name=NULL)
	{
		parent::__construct($parent, $name);
		$this->setGeneratedFileNamePrefix('jsldr-');
		$this->setGeneratedFileNameSuffix('.js');
		$this->sourcePath=WWW_DIR.'/js';
		$this->contentType='text/javascript';
	}

	/**
	 * @see \Lohini\WebLoader\WebLoader::addFile()
	 * @throws \Nette\FileNotFoundException
	 */
	public function addFile($file, $processing=self::COMPACT)
	{
		foreach ($this->files as $f) {
			if ($f[0]==$file) {
				return;
				}
			}
		if (!file_exists("$this->sourcePath/$file")) {
			if ($this->throwExceptions) {
				if ($this->getPresenter(FALSE)->context->params['productionMode']) {
					throw new \Nette\FileNotFoundException("File '$this->sourcePath/$file' doesn't exist.");
					}
				else {
					Debugger::processException(new \Nette\FileNotFoundException("File '$this->sourcePath/$file' doesn't exist."));
					return;
					}
				}
			}
		$this->files[]=array($file, $processing);
	}

	/**
	 * Add code
	 * @var string only raw javascript code no files, send to output
	 *
	 * in case you want to send some javascript code, that is generated in presenters, and should not be cached, and it is easier to create this code in presenters
	 * something like this
	 * <script>BASE_IMAGES={$basePath}'design/images/';</script>
	 */
	public function addCode($code)
	{
		$this->codes[]=$code;
	}

	/**
	 * @see \Lohini\WebLoader\WebLoader::renderFiles()
	 * @throws \Nette\FileNotFoundException
	 */
	public function renderFiles()
	{
		$filenames=array();
		$content='';
		if (($cnt=count($this->files))>0) {
			if ($this->enableDirect && $cnt==1 && $this->files[0][1]==self::COMPACT) {
				$this->sourceUri=$this->getPresenter(FALSE)->context->httpRequest->getUrl()->getBaseUrl().'js/';
				echo $this->useHeadJs
					? $this->getHeadJsElement($this->sourceUri.$this->files[0][0])
					: $this->getElement($this->sourceUri.$this->files[0][0]);
				}
			else {
				$dc=get_declared_classes();
				// u javascriptu zalezi na poradi
				foreach ($this->files as $file) {
					switch ($file[1]) {
						case self::COMPACT:
							$content.=$this->loadFile($file[0]);
							break;
						case self::MINIFY:
							// dean edwards packer neumi cz/sk znaky!!
							if (Strings::endsWith($file[0], '.min.js')) { // already minified ?
								$content.=$this->loadFile($file[0]);
								}
							elseif (is_file($mfile="$this->sourcePath/".substr($file[0], 0, -3).'.min.js')) { // have minified ?
								$content.=file_get_contents($mfile);
								}
							elseif (in_array('JSMin', $dc) || class_exists('JSMin')) { // minify
								$content.=\JSMin::minify($this->loadFile($file[0]));
								}
							else {
								if ($this->throwExceptions) {
									if ($this->getPresenter(FALSE)->context->params['productionMode']) {
										throw new \Nette\FileNotFoundException("Don't have JSMin class.");
										}
									else {
										Debugger::processException(new \Nette\FileNotFoundException("Don't have JSMin class"));
										}
									}
								$content.=$this->loadFile($file[0]);
								}
							break;
						case self::PACK:
							if (Strings::endsWith($file[0], '.pack.js')) { // already packed ?
								$content.=$this->loadFile($file[0]);
								}
							elseif (is_file($pfile="$this->sourcePath/".substr($file[0], 0, -3).'.pack.js')) { // have packed ?
								$content.=file_get_contents($pfile);
								}
							elseif (in_array('JavaScriptPacker', $dc) || class_exists('JavaScriptPacker')) {
								$jsp=new \JavaScriptPacker($this->loadFile($file[0]));
								$content.=$jsp->pack();
								}
							else {
								if ($this->throwExceptions) {
									if ($this->getPresenter(FALSE)->context->params['productionMode']) {
										throw new \Nette\FileNotFoundException("Don't have JavaScriptPacker class.");
										}
									else {
										Debugger::processException(new \Nette\FileNotFoundException("Don't have JavaScriptPacker class"));
										}
									}
								$content.=$this->loadFile($file[0]);
								}
							break;
						default:
							return;
						}
					$filenames[]=$file[0];
					}
				echo $this->useHeadJs
					? $this->getHeadJsElement($this->getPresenter()->link(':WebLoader:', $this->generate($filenames, $content)))
					: $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate($filenames, $content)));
				}
			}
		// raw code az nakonec
		foreach ($this->codes as $code) {
			echo $this->getCodeElement($code);
			}
	}

	/**
	 * @see \Lohini\WebLoader\WebLoader::getElement()
	 */
	public function getElement($source)
	{
		return Html::el('script')
				->src($source);
	}

	/**
	 * Get script code element
	 * @param string $code
	 * @return Html
	 */
	public function getCodeElement($code)
	{
		return Html::el('script')
				->setHtml($code);
	}

	/**
	 * Get script code element
	 * @param string $source
	 * @return Html
	 */
	public function getHeadJsElement($source)
	{
		return Html::el('script')
				->setHtml("head.js('$source');");
	}

	/**
	 * Generates compiled files and render links
	 * @example {control js:singles 'file.js', 'file2.js'}
	 * @throws \Nette\FileNotFoundException
	 */
	public function renderSingles()
	{
		if ($hasArgs=(func_num_args()>0)) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}
		$dc=get_declared_classes();
		$this->sourceUri=$this->getPresenter(FALSE)->context->httpRequest->getUrl()->getBaseUrl().'js/';
		// u javascriptu zalezi na poradi
		foreach ($this->files as $file) {
			switch ($file[1]) {
				case self::COMPACT:
					echo $this->enableDirect
						? $this->getElement($this->sourceUri.$file[0])
						: $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array($file[0]))));
					break;
				case self::MINIFY:
					// dean edwards packer neumi cz/sk znaky!!
					if (Strings::endsWith($file[0], '.min.js')) { // already minified ?
						echo $this->enableDirect
							? $this->getElement($this->sourceUri.$file[0])
							: $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array($file[0]))));
						}
					elseif (is_file("$this->sourcePath/".substr($file[0], 0, -3).'.min.js')) { // have minified ?
						echo $this->enableDirect
							? $this->getElement($this->sourceUri.substr($file[0], 0, -3).'.min.js')
							: $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array(substr($file[0], 0, -3).'.min.js'))));
						}
					elseif (in_array('JSMin', $dc) || class_exists('JSMin')) { // minify
						$content=\JSMin::minify($this->loadFile($file[0]));
						echo $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array($file[0]), $content)));
						}
					else {
						if ($this->throwExceptions) {
							if ($this->getPresenter(FALSE)->context->params['productionMode'])
								throw new \Nette\FileNotFoundException("Don't have JSMin class.");
							else {
								Debugger::processException(new \Nette\FileNotFoundException("Don't have JSMin class"));
								}
							}
					echo $this->enableDirect
						? $this->getElement($this->sourceUri.$file[0])
						: $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array($file[0]))));
						}
					break;
				case self::PACK:
					if (Strings::endsWith($file[0], '.pack.js')) { // already packed ?
						echo $this->enableDirect
							? $this->getElement($this->sourceUri.$file[0])
							: $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array($file[0]))));
						}
					elseif (is_file($pfile="$this->sourcePath/".substr($file[0], 0, -3).'.pack.js')) { // have packed ?
						echo $this->enableDirect
							? $this->getElement($this->sourceUri.substr($file[0], 0, -3).'.pack.js')
							: $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array(substr($file[0], 0, -3).'.pack.js'))));
						}
					elseif (in_array('JavaScriptPacker', $dc) || class_exists('JavaScriptPacker')) {
						$jsp=new \JavaScriptPacker($this->loadFile($file[0]));
						$content=$jsp->pack();
						echo $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array($file[0]), $content)));
						}
					else {
						if ($this->throwExceptions) {
							if ($this->getPresenter(FALSE)->context->params['productionMode'])
								throw new \Nette\FileNotFoundException("Don't have JavaScriptPacker class.");
							else {
								Debugger::processException(new \Nette\FileNotFoundException("Don't have JavaScriptPacker class"));
								}
							}
						echo $this->enableDirect
							? $this->getElement($this->sourceUri.$file[0])
							: $this->getElement($this->getPresenter()->link(':WebLoader:', $this->generate(array($file[0]))));
						}
					break;
				default:
					return;
					}
			}
		// raw code az nakonec
		foreach ($this->codes as $code) {
			echo $this->getCodeElement($code);
			}
		if ($hasArgs) {
			$this->files=$backup;
			}
	}

	/**
	 * Generates and render links - no processing
	 * @example {control js:static 'file.js', 'file2.js'}
	 */
	public function renderStatic()
	{
		if (!$this->enableDirect) {
			throw new \Nette\InvalidStateException('Static linking not available with disabled direct linking');
			}
		if ($hasArgs=(func_num_args()>0)) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}
		$this->sourceUri=$this->getPresenter(FALSE)->context->httpRequest->getUrl()->getBaseUrl().'js/';
		// u javascriptu zalezi na poradi
		foreach ($this->files as $file) {
			switch ($file[1]) {
				case self::COMPACT:
					echo $this->getElement($this->sourceUri.$file[0]);
					break;
				case self::MINIFY:
					// dean edwards packer neumi cz/sk znaky!!
					if (Strings::endsWith($file[0], '.min.js')) { // already minified ?
						echo $this->getElement($this->sourceUri.$file[0]);
						}
					elseif (is_file("$this->sourcePath/".substr($file[0], 0, -3).'.min.js')) { // have minified ?
						echo $this->getElement($this->sourceUri.substr($file[0], 0, -3).'.min.js');
						}
					else {
						echo $this->getElement($this->sourceUri.$file[0]);
						}
					break;
				case self::PACK:
					if (Strings::endsWith($file[0], '.pack.js')) { // already packed ?
						echo $this->getElement($this->sourceUri.$file[0]);
						}
					elseif (is_file($pfile="$this->sourcePath/".substr($file[0], 0, -3).'.pack.js')) { // have packed ?
						echo $this->getElement($this->sourceUri.substr($file[0], 0, -3).'.pack.js');
						}
					else {
						echo $this->getElement($this->sourceUri.$file[0]);
						}
					break;
				default:
					return;
					}
			}
		// raw code az nakonec
		foreach ($this->codes as $code) {
			echo $this->getCodeElement($code);
			}
		if ($hasArgs) {
			$this->files=$backup;
			}
	}

	/**
	 * Generates and render links - disables use of Head.js
	 *
	 * usefull for @layout
	 * @example {control js:noHead 'file.js', 'file2.js'}
	 */
	public function renderNoHead()
	{
		if ($hasArgs=(func_num_args()>0)) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}
		$this->useHeadJs=FALSE;
		$this->renderFiles();
		if ($hasArgs) {
			$this->files=$backup;
			}
	}

	/**
	 * Generates and render link
	 *
	 * @example {control js:link 'file.js', 'file2.js'}
	 */
	public function renderLink()
	{
		if ($hasArgs=(func_num_args()>0)) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}
		$filenames=array();
		$content='';
		if (($cnt=count($this->files))>0) {
			if ($this->enableDirect && $cnt==1 && $this->files[0][1]==self::COMPACT) {
				$this->sourceUri=$this->getPresenter(FALSE)->context->httpRequest->getUrl()->getBaseUrl().'js/';
				echo $this->sourceUri.$this->files[0][0];
				}
			else {
				$dc=get_declared_classes();
				// u javascriptu zalezi na poradi
				foreach ($this->files as $file) {
					switch ($file[1]) {
						case self::COMPACT:
							$content.=$this->loadFile($file[0]);
							break;
						case self::MINIFY:
							// dean edwards packer neumi cz/sk znaky!!
							if (Strings::endsWith($file[0], '.min.js')) { // already minified ?
								$content.=$this->loadFile($file[0]);
								}
							elseif (is_file($mfile="$this->sourcePath/".substr($file[0], 0, -3).'.min.js')) { // have minified ?
								$content.=file_get_contents($mfile);
								}
							elseif (in_array('JSMin', $dc) || class_exists('JSMin')) { // minify
								$content.=\JSMin::minify($this->loadFile($file[0]));
								}
							else {
								if ($this->throwExceptions) {
									if ($this->getPresenter(FALSE)->context->params['productionMode']) {
										throw new \Nette\FileNotFoundException("Don't have JSMin class.");
										}
									else {
										Debugger::processException(new \Nette\FileNotFoundException("Don't have JSMin class"));
										}
									}
								$content.=$this->loadFile($file[0]);
								}
							break;
						case self::PACK:
							if (Strings::endsWith($file[0], '.pack.js')) { // already packed ?
								$content.=$this->loadFile($file[0]);
								}
							elseif (is_file($pfile="$this->sourcePath/".substr($file[0], 0, -3).'.pack.js')) { // have packed ?
								$content.=file_get_contents($pfile);
								}
							elseif (in_array('JavaScriptPacker', $dc) || class_exists('JavaScriptPacker')) {
								$jsp=new \JavaScriptPacker($this->loadFile($file[0]));
								$content.=$jsp->pack();
								}
							else {
								if ($this->throwExceptions) {
									if ($this->getPresenter(FALSE)->context->params['productionMode']) {
										throw new \Nette\FileNotFoundException("Don't have JavaScriptPacker class.");
										}
									else {
										Debugger::processException(new \Nette\FileNotFoundException("Don't have JavaScriptPacker class"));
										}
									}
								$content.=$this->loadFile($file[0]);
								}
							break;
						default:
							return;
						}
					$filenames[]=$file[0];
					}
				echo $this->getPresenter()->link(':WebLoader:', $this->generate($filenames, $content));
				}
			}
		if ($hasArgs) {
			$this->files=$backup;
			}
	}
}
