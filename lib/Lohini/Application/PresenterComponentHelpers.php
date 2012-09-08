<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Application\UI\Presenter;

/**
 */
class PresenterComponentHelpers
extends \Nette\Object
{
	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \Nette\StaticClassException('Cannot instantiate static class '.get_class($this));
	}

	/* ******************* Links ************************/
	/**
	 * @param \Nette\Application\UI\PresenterComponent $component
	 * @return array
	 */
	public static function nullLinkParams(\Nette\Application\UI\PresenterComponent $component)
	{
		$parent=$component;
		$presenter= $component instanceof Presenter? NULL : $component->lookup('Nette\Application\UI\Presenter');
		$params=array();

		do {
			if ($parent && method_exists($parent, 'getPersistentParams')) {
				$name= $parent instanceof Presenter? '' : $parent->lookupPath(get_class($presenter));

				foreach ($parent->reflection->getPersistentParams() as $param => $info) {
					$params[($name? $name.$component::NAME_SEPARATOR : NULL).$param]=$info['def'] ?: NULL;
					}
				}
			} while($parent && $parent=$parent->getParent());
		return $params;
	}
}
