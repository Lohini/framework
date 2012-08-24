<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Browser\Diagnostics;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Extension\Browser\DomException;

/**
 */
class Panel
extends \Nette\Object
{
	/**
	 * @param \Exception $e
	 * @return array
	 */
	public function renderException($e)
	{
		if ($e instanceof DomException) {
			return array(
				'tab' => 'DomDocument',
				'panel' => '<pre>'.$this->dumpException($e).'</pre>',
				);
			}
	}

	/**
	 * @param DomException $e
	 * @return string
	 */
	private function dumpException(DomException $e)
	{
		return static::highlightCode($e->getSource(), $e->getDocumentLine());
	}

	/**
	 * Returns syntax highlighted source code.
	 *
	 * @copyright David Grudl
	 * @see https://github.com/nette/nette/blob/master/Nette/Diagnostics/BlueScreen.php#L59
	 *
	 * @param string $source
	 * @param int $line
	 * @param int $count
	 * @param array $vars
	 * @return string
	 */
	private static function highlightCode($source, $line, $count=15, $vars=array())
	{
		if (function_exists('ini_set')) {
			ini_set('highlight.comment', '#998; font-style: italic');
			ini_set('highlight.default', '#000');
			ini_set('highlight.html', '#06B');
			ini_set('highlight.keyword', '#D24; font-weight: bold');
			ini_set('highlight.string', '#080');
			}

		$start=max(1, $line-floor($count*2/3));
		$source=explode("\n", highlight_string($source, TRUE));
		$spans=1;
		$out=$source[0]; // <code><span color=highlight.html>
		$source=explode('<br />', $source[1]);
		array_unshift($source, NULL);

		$i=$start; // find last highlighted block
		while (--$i>=1) {
			if (preg_match('#.*(</?span[^>]*>)#', $source[$i], $m)) {
				if ($m[1]!=='</span>') {
					$spans++;
					$out.=$m[1];
					}
				break;
				}
			}

		$source=array_slice($source, $start, $count, TRUE);
		end($source);
		$numWidth=strlen((string)key($source));

		foreach ($source as $n => $s) {
			$spans+=substr_count($s, '<span')-substr_count($s, '</span');
			$s=str_replace(array("\r", "\n"), array('', ''), $s);
			preg_match_all('#<[^>]+>#', $s, $tags);
			if ($n===$line) {
				$out.=sprintf(
					"<span class='highlight'>%{$numWidth}s:    %s\n</span>%s",
					$n,
					strip_tags($s),
					implode('', $tags[0])
					);
				}
			else {
				$out.=sprintf("<span class='line'>%{$numWidth}s:</span>    %s\n", $n, $s);
				}
			}
		$out.=str_repeat('</span>', $spans).'</code>';

		$out=preg_replace_callback(
			'#">\$(\w+)(&nbsp;)?</span>#',
			function($m) use ($vars) {
				return isset($vars[$m[1]])
					? '" title="'.str_replace('"', '&quot;', strip_tags(\Nette\Diagnostics\Helpers::htmlDump($vars[$m[1]]))).$m[0]
					: $m[0];
				},
			$out
			);

		return $out;
	}

	/**
	 * @return Panel
	 */
	public static function register()
	{
		\Nette\Diagnostics\Debugger::$blueScreen
			->addPanel(array($panel=new static(), 'renderException'));
		return $panel;
	}
}
