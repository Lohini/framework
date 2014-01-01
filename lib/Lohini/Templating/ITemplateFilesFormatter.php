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
 * ITemplateFactoryFilesFormatter
 *
 * @author Lopo <lopo@lohini.net>
 */
interface ITemplateFilesFormatter
{
	/**
	 * Formats layout template file names
	 *
	 * @param string $name presenter name
	 * @param string $layout
	 * @return array
	 */
	public function formatLayoutTemplateFiles($name, $layout='layout');

	/**
	 * Formats view template file names
	 *
	 * @param string $name
	 * @param string $view
	 * @return array
	 */
	public function formatTemplateFiles($name, $view);
}
