<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets\Resolver;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class PackagePathResolver
extends \Nette\Object
implements \Lohini\Extension\Assets\IResourceResolver
{
	/**
	 * @var \Lohini\Packages\PackageManager
	 */
	private $packageManager;


	/**
	 * @param \Lohini\Packages\PackageManager $packageManager
	 */
	public function __construct(\Lohini\Packages\PackageManager $packageManager)
	{
		$this->packageManager=$packageManager;
	}

	/**
	 * @param string $input
	 * @param array $options
	 * @return string|bool
	 */
	public function locateResource($input, array &$options)
	{
		// expand bundle notation
		if ('@'!==$input[0] || strpos($input, '/')===FALSE) {
			return FALSE;
			}

		list($packageName)=explode('/', substr($input, 1), 2);
		$packagePath=$this->packageManager->getPackage($packageName)->getPath();

		// use the bundle path as this asset's root
		$options['root']=array($packagePath.'/Resources/public');

		// canonicalize the input
		if (FALSE!==($pos=strpos($input, '*'))) {
			list($before, $after)=explode('*', $input, 2);
			$input=$this->packageManager->locateResource($before).'*'.$after;
			}
		else {
			$input=$this->packageManager->locateResource($input);
			}

		return $input;
	}
}
