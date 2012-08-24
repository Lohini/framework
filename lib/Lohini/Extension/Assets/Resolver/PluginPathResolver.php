<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets\Resolver;

/**
 */
class PluginPathResolver
extends \Nette\Object
implements \Lohini\Extension\Assets\IResourceResolver
{
	/**
	 * @var \Lohini\Packages\PackageManager
	 */
	private $pluginManager;


	/**
	 * @param \Lohini\Packages\PackageManager $pluginManager
	 */
	public function __construct(\Lohini\Package\Plugins\Manager $pluginManager)
	{
		$this->pluginManager=$pluginManager;
	}

	/**
	 * @param string $input
	 * @param array $options
	 * @return string|bool
	 */
	public function locateResource($input, array &$options)
	{
		// expand bundle notation
		if ('#'!==$input[0] || strpos($input, '/')===FALSE) {
			return FALSE;
			}

		list($pluginName)=explode('/', substr($input, 1), 2);
		$pluginPath=$this->pluginManager->getPlugin($pluginName)->getPath();

		// use the bundle path as this asset's root
		$options['root']=array($pluginPath.'/Resources/public');

		// canonicalize the input
		if (FALSE!==($pos=strpos($input, '*'))) {
			list($before, $after)=explode('*', $input, 2);
			$input=$this->pluginManager->locateResource($before).'*'.$after;
			}
		else {
			$input=$this->pluginManager->locateResource($input);
			}

		return $input;
	}
}
