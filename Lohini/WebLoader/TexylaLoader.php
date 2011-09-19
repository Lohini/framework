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

		$this->setSourcePath(WWW_DIR.'/texyla');
		$this->setGeneratedFileNamePrefix('tldr-');

		$this->addFiles(array(
			// core
			'js/texyla.js',
			'js/selection.js',
			'js/texy.js',
			'js/buttons.js',
			'js/dom.js',
			'js/view.js',
			'js/ajaxupload.js',
			'js/window.js',
			// languages
			'languages/cs.js',
			'languages/sk.js',
			'languages/en.js',
			// plugins
			'plugins/keys/keys.js',
			'plugins/resizableTextarea/resizableTextarea.js',
			'plugins/img/img.js',
			'plugins/table/table.js',
			'plugins/link/link.js',
			'plugins/emoticon/emoticon.js',
			'plugins/symbol/symbol.js',
			'plugins/files/files.js',
			'plugins/color/color.js',
			'plugins/textTransform/textTransform.js',
			'plugins/youtube/youtube.js',
			'plugins/gravatar/gravatar.js'
			));
	}
}
