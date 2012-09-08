<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Utils;
/**
 * @link https://github.com/paulgb/simplediff
 * @author Paul Butler 2007 <http://www.paulbutler.org/>
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Class of the SimpleDiff PHP library by Paul Butler
 */
class SimpleDiff
extends \Nette\Object
{
	/**
	 * @param array $old
	 * @param array $new
	 * @return array
	 */
	public function diff(array $old, array $new)
	{
		$maxlen=0;
		foreach ($old as $oindex => $ovalue) {
			$nkeys=array_keys($new, $ovalue);
			foreach ($nkeys as $nindex) {
				$matrix[$oindex][$nindex]= isset($matrix[$oindex-1][$nindex-1])
					? $matrix[$oindex-1][$nindex-1]+1
					: 1;
				if ($matrix[$oindex][$nindex]>$maxlen) {
					$maxlen=$matrix[$oindex][$nindex];
					$omax=$oindex+1-$maxlen;
					$nmax=$nindex+1-$maxlen;
					}
				}
			}
		if ($maxlen==0) {
			return array(array('d' => $old, 'i' => $new));
			}
		return array_merge(
			$this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
			array_slice($new, $nmax, $maxlen),
			$this->diff(array_slice($old, $omax+$maxlen), array_slice($new, $nmax+$maxlen))
			);
	}

	/**
	 * @param $old
	 * @param $new
	 * @return string
	 */
	public function htmlDiff($old, $new)
	{
		$ret='';
		$diff=$this->diff(explode(' ', $old), explode(' ', $new));
		foreach ($diff as $k) {
			if (is_array($k)) {
				$ret.=(!empty($k['d'])? '<del>'.implode(' ', $k['d']).'</del> ' : '')
					.(!empty($k['i'])? '<ins>'.implode(' ', $k['i']).'</ins> ' : '');
				}
			else {
				$ret.=$k.' ';
				}
			 }
		return $ret;
	}
}
