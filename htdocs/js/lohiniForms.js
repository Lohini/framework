// vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */

/**
 * @link http://net.tutsplus.com/tutorials/javascript-ajax/how-to-submit-a-form-with-control-enter
 */
$.fn.ctrlEnter=function(btns, fn)
{
	var $this=$(this),
		$btns=$(btns);

	function cE_cH(e)
	{
		fn.call($this, e);
	}

	$this.bind('keydown', function(e) {
		if (e.keyCode===13 && e.ctrlKey) {
			cE_cH();
			e.preventDefault();
			}
		});
	$btns.bind('click', cE_cH);
}
