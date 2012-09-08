<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\Common\Collections\Collection;

/**
 */
final class Helpers
extends \Nette\Object
{
	/**
	 * Static class cannot be instantiated
	 *
	 * @throws \Nette\StaticClassException
	 */
	final public function __construct()
	{
		throw new \Nette\StaticClassException("Can't instantiate static class ".get_class($this));
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $col
	 * @param object|int $element
	 * @param string $primary
	 * @return bool
	 */
	public static function collectionRemove(Collection $col, $element, $primary='id')
	{
		if (is_object($element)) {
			return $col->removeElement($element);
			}

		$removed=FALSE;
		foreach ($col as $item) {
			if ($item->{'get'.ucFirst($primary)}()===$element) {
				$col->remove($item);
				$removed = TRUE;
				}
			}

		return $removed;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $col
	 * @param object|int $element
	 * @param string $primary
	 * @return bool
	 */
	public static function collectionHas(Collection $col, $element, $primary='id')
	{
		if (is_object($element)) {
			return $col->contains($element);
			}

		foreach ($col as $item) {
			if ($item->{'get'.ucFirst($primary)}()===$element) {
				return TRUE;
				}
			}

		return FALSE;
	}
}
