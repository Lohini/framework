<?php // vim: set ts=4 sw=4 ai:
namespace BailIff;

use Nette\Object;

/**
 * BailIff Core
 *
 * @author Lopo <lopo@losys.eu>
 */
final class Core
{
	/**#@+ BailIff version ID's */
	const NAME='BailIff';
	const VERSION='0.0.3-dev';
	const REVISION='$WCREV$ released on $WCDATE$';
	const DEVELOPMENT=TRUE;
	/**#@-*/

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \LogicException("Can't instantiate static class ".get_class($this));
	}
}
