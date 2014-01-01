<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Templating;
/**
 * @author Patrik Votoček
 */


/**
 * Template files paths formatter
 *
 * @author Lopo <lopo@lohini.net>
 */
class TemplateFilesFormatter
extends \Nette\Object
implements ITemplateFilesFormatter
{
	const MODULE_SUFFIX='Module';

	/** @var \SplPriorityQueue */
	private $dirs;
	/** @var string */
	public $skin=NULL;


	public function __construct()
	{
		$this->dirs=new \SplPriorityQueue;
	}

	/**
	 * @param string $dir
	 * @param int $priority
	 * @return self
	 */
	public function addDir($dir, $priority=5)
	{
		$this->dirs->insert($dir, $priority);
		return $this;
	}

	/**
	 * Formats layout template file names
	 *
	 * @param string $name presenter name
	 * @param string $layout
	 * @return array
	 */
	public function formatLayoutTemplateFiles($name, $layout='layout')
	{
		if ($path=str_replace(':', '/', substr($name, 0, $pos=strrpos($name, ':')))) {
			$path.='/';
			}
		$subPath= substr($name, $pos!==FALSE? $pos+1 : 0);

		if ($path) {
			$path=str_replace('/', self::MODULE_SUFFIX.'/', $path);
			}

		$generator=function ($base, $dir) use ($pos, $path, $subPath, $layout) {
			$files=[];
			// classic templates
			$files[]=$base."/$dir/$path$subPath/@$layout.latte";
			$files[]=$base."/$dir/$path$subPath.@$layout.latte";
			$files[]=$base."/$dir/$path@$layout.latte";
			$files[]=$base."/$dir/@$layout.latte";
			// classic modules templates
			if ($pos!==FALSE) {
				$files[]=$base."/{$path}$dir/$subPath/@$layout.latte";
				$files[]=$base."/{$path}$dir/$subPath.@$layout.latte";
				$files[]=$base."/{$path}$dir/@$layout.latte";
				}

			return $files;
			};

		$files=[];
		$dirs=clone $this->dirs;
		if ($this->skin) {
			foreach ($dirs as $dir) {
				$files=array_merge($files, $generator($dir, 'skins/'.$this->skin));
				}
			$dirs=clone $this->dirs;
			}
		foreach ($dirs as $dir) {
			$files=array_merge($files, $generator($dir, 'templates'));
			}

		return $files;
	}

	/**
	 * Formats view template file names
	 *
	 * @param string $name presenter name
	 * @param string $view
	 * @return array
	 */
	public function formatTemplateFiles($name, $view)
	{
		if ($path=str_replace(':', '/', substr($name, 0, $pos=strrpos($name, ':')))) {
			$path.='/';
			}
		$subPath= substr($name, $pos!==FALSE? $pos+1 : 0);

		if ($path) {
			$path=str_replace('/', self::MODULE_SUFFIX.'/', $path);
			}

		$generator=function ($base, $dir) use ($pos, $path, $subPath, $view) {
			$files=[];
			// classic modules templates
			if ($pos!==FALSE) {
				$files[]=$base."/{$path}$dir/$subPath/$view.latte";
				$files[]=$base."/{$path}$dir/$subPath.$view.latte";
				}
			// classic templates
			$files[]=$base."/$dir/{$path}$subPath/$view.latte";
			$files[]=$base."/$dir/{$path}$subPath.$view.latte";

			return $files;
			};

		$files=[];
		$dirs=clone $this->dirs;
		if ($this->skin) {
			foreach ($dirs as $dir) {
				$files=array_merge($files, $generator($dir, 'skins/'.$this->skin));
				}
			$dirs=clone $this->dirs;
			}
		foreach ($dirs as $dir) {
			$files=array_merge($files, $generator($dir, 'templates'));
			}

		return $files;
	}
}
