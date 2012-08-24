<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing\Tools;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * @todo cleanup files
 */
class TempClassGenerator
extends \Nette\Object
{
	/** @var string */
	private $tempDir;


	/**
	 * @param string $tempDir
	 */
	public function __construct($tempDir)
	{
		$this->tempDir=$tempDir.'/classes';
		@mkdir($this->tempDir, 0777);

		$this->clean();
	}

	/**
	 */
	public function clean()
	{
		foreach (\Nette\Utils\Finder::findFiles('*.php')->in($this->tempDir) as $file) {
			@unlink($file->getRealpath());
			}
	}

	/**
	 * @param string
	 * @return string
	 * @throws \Lohini\DirectoryNotWritableException
	 */
	public function generate($class=NULL)
	{
		// classname
		$class= $class ?: 'Entity_'.\Nette\Utils\Strings::random();

		// file & content
		$file=$this->resolveFilename($class);
		$content='<'.'?php'."\nclass $class {  } // ".(string)microtime(TRUE);

		if (!is_dir($dir=dirname($file))) {
			@mkdir($dir, 0777, TRUE);
			}

		if (!file_put_contents($file, $content)) {
			throw \Lohini\DirectoryNotWritableException::fromDir(dirname($file));
			}

		if (!class_exists($class, FALSE)) {
			\Nette\Utils\LimitedScope::load($file);
			}

		return $class;
	}

	/**
	 * @param string $class
	 * @return string
	 */
	public function resolveFilename($class)
	{
		return $this->tempDir.'/'.$class.'.tempclass.php';
	}
}
