<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\DI;

class Configurator
extends \Nette\DI\Configurator
{
	/**
	 * Merges 2nd config into 1st
	 *
	 * @param unknown_type $c1
	 * @param unknown_type $c2
	 * @return Config
	 * @todo move to own Configurator
	 */
	public static function mergeConfigs($c1, $c2)
	{
		foreach ($c2 as $k => $v) {
			if (array_key_exists($k, $c1) && $v!==NULL && (!is_scalar($v) || is_array($v))) {
				$c1[$k]=self::mergeConfigs($c1->$k, $c2->$k);
				}
			else {
				$c1[$k]=$v;
				}
			}
		return $c1;
	}
}
