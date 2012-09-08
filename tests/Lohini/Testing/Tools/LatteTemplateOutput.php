<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing\Tools;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class LatteTemplateOutput
extends \Nette\Object
{
	/** @var string */
	public $prolog;
	/** @var string */
	public $epilog;
	/** @var string */
	public $macro;
	/** @var \Nette\Latte\Engine */
	private $latte;
	/** @var string */
	private $tempDir;


	/**
	 * @param \Nette\Latte\Engine $engine
	 */
	public function __construct($engine, $tempDir)
	{
		$this->latte=$engine;
		$this->tempDir=$tempDir;
		$this->prolog=array();
		$this->macro=array();
		$this->epilog=array();
	}

	/**
	 * @param string $source
	 * @return LatteTemplateOutput
	 */
	public function parse($source)
	{
		$template=new \Nette\Templating\Template();
		$template->registerFilter($this->latte);
		$template->setSource($source);

		try {
			$output=$template->compile();
			}
		catch (\Nette\Latte\CompileException $e) {
			$tmpFile=$this->tempDir.'/'.md5($source).'.latte';
			file_put_contents($tmpFile, $source);
			$e->setSourceFile($tmpFile);
			$this->epilog= $this->macro= $this->prolog= NULL;
			throw $e;
			}

		$lines=array_filter(
				explode("\n", $output),
				function($line) { return $line!=='//'; }
				);
		$part=NULL;
		foreach ($lines as $line) {
			if (strpos($line, '// prolog')===0) {
				$part='prolog';
				continue;
				}
			if (strpos($line, '// main template')===0) {
				$part='macro';
				continue;
				}
			if (strpos($line, '// epilog')===0) {
				$part='epilog';
				continue;
				}

			if ($part!==NULL) {
				$this->{$part}[]=$line;
				}
			}

		if (!$this->prolog && !$this->macro && !$this->epilog) {
			$this->macro=$output;
			}
		else {
			$this->prolog=implode("\n", $this->prolog);
			$this->macro=implode("\n", $this->macro);
			$this->epilog=implode("\n", $this->epilog);
			}

		return $this;
	}
}
