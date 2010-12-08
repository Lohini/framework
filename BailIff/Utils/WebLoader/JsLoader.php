<?php  // vim: ts=4 sw=4 ai:
namespace BailIff\WebLoader;

use Nette\IComponentContainer,
	Nette\Environment as NEnvironment,
	Nette\Web\Html,
	Nette\String,
	BailIff\WebLoader\Filters\JSMin,
	BailIff\WebLoader\Filters\JavaScriptPacker,
	Nette\Debug;

/**
 * JsLoader
 *
 * @author Jan Marek
 * @license MIT
 * @author Lopo <lopo@losys.eu>
 */
class JsLoader
extends WebLoader
{
	/**#@+ cache content */
	const COMPACT='c';
	const MINIFY='m';
	const PACK='p';
	/**#@-*/
	/** @var array */
	public $codes=array();

	/**
	 * Construct
	 * @param IComponentContainer parent
	 * @param string name
	 */
	public function __construct(IComponentContainer $parent=NULL, $name=NULL)
	{
		parent::__construct($parent, $name);
		$this->setGeneratedFileNamePrefix('jsldr-');
		$this->setGeneratedFileNameSuffix('.js');
		$this->sourcePath=WWW_DIR.'/js';
		$this->sourceUri=NEnvironment::getVariable('baseUri').'js/';
		$this->contentType='text/javascript';
	}

	/**
	 * (non-PHPdoc)
	 * @see BailIff\WebLoader.WebLoader::addFile()
	 */
	public function addFile($file, $processing=self::COMPACT)
	{
		foreach ($this->files as $f)
			if ($f[0]==$file)
				return;
		if (!file_exists("$this->sourcePath/$file")) {
			if ($this->throwExceptions) {
				if (NEnvironment::isProduction())
					throw new \FileNotFoundException("File '$this->sourcePath/$file' doesn't exist.");
				else {
					Debug::processException(new \FileNotFoundException("File '$this->sourcePath/$file' doesn't exist."));
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
	 * <script type="text/javascript">BASE_IMAGES={$baseUri}'design/images/';</script>
	 */
	public function addCode($code)
	{
		$this->codes[]=$code;
	}

	/**
	 * (non-PHPdoc)
	 * @see BailIff\WebLoader.WebLoader::renderFiles()
	 */
	public function renderFiles()
	{
		$filenames=array();
		$content='';
		if (($cnt=count($this->files))>0) {
			if ($cnt==1 && $this->files[0][1]==self::COMPACT) {
				echo $this->getElement($this->sourceUri.$this->files[0][0]);
				}
			else {
				// u javascriptu zalezi na poradi
				foreach ($this->files as $file) {
					switch ($file[1]) {
						case self::COMPACT:
							$content.=$this->loadFile($file[0]);
							break;
						case self::MINIFY:
							// dean edwards packer neumi cz/sk znaky!!
							if (String::endsWith($file[0], '.min.js')) { // already minified ?
								$content.=$this->loadFile($file[0]);
								}
							elseif (is_file($mfile="$this->sourcePath/".substr($file[0], 0, strlen($file[0])-3).'.min.js')) { // have minified ?
								$content.=file_get_contents($mfile);
								}
							else { // minify
								$content.=JSMin::minify($this->loadFile($file[0]));
								}
							break;
						case self::PACK:
							$jsp=new JavaScriptPacker($this->loadFile($file[0]), 'Normal', TRUE, FALSE);
							$content.=$jsp->pack();
							break;
						default:
							return;
						}
					$filenames[]=$file[0];
					}
				echo $this->getElement($this->getPresenter()->link('WebLoader', $this->generate($filenames, $content)));
				}
			}
		// raw code az nakonec
		foreach ($this->codes as $code)
			echo $this->getCodeElement($code);
	}

	/**
	 * (non-PHPdoc)
	 * @see BailIff\WebLoader.WebLoader::getElement()
	 */
	public function getElement($source)
	{
		return Html::el('script')
				->type('text/javascript')
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
				->type('text/javascript')
				->setHtml($code);
	}
}
