<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader;

/**
 * Texyla loader
 *
 * @author Lopo <lopo@lohini.net>
 */
class TexylaLoader
extends JsLoader
{
	/**
	 * @param \Nette\ComponentModel\IContainer parent
	 * @param string name
	 */
	public function __construct(\Nette\ComponentModel\IContainer $parent=NULL, $name=NULL)
	{
		parent::__construct($parent, $name);

		$this->setGeneratedFileNamePrefix('tldr-');

		$this->addFiles(array(
			// core
			'texyla/texyla.js',
			'texyla/selection.js',
			'texyla/texy.js',
			'texyla/buttons.js',
			'texyla/dom.js',
			'texyla/view.js',
			'texyla/ajaxupload.js',
			'texyla/window.js',
			// languages
			'texyla/languages/cs.js',
			'texyla/languages/sk.js',
			'texyla/languages/en.js',
			// plugins
			'texyla/plugins/keys/keys.js',
			'texyla/plugins/resizableTextarea/resizableTextarea.js',
			'texyla/plugins/img/img.js',
			'texyla/plugins/table/table.js',
			'texyla/plugins/link/link.js',
			'texyla/plugins/emoticon/emoticon.js',
			'texyla/plugins/symbol/symbol.js',
			'texyla/plugins/files/files.js',
			'texyla/plugins/color/color.js',
			'texyla/plugins/textTransform/textTransform.js',
			'texyla/plugins/youtube/youtube.js',
			));
	}
}
