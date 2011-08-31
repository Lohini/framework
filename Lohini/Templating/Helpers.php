<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Templating;

use Nette\Utils\Strings;

/**
 * Lohini template run-time telpers
 *
 * @author Lopo <lopo@lohini.net>
 */
final class Helpers
{
	public static $datetimeFormat='j.n.Y H:i:s';


	/** @var array */
	private static $helpers=array(
		'gravatar' => 'Lohini\Components\Gravatar::helper'
		);


	/**
	 * Try to load the requested helper.
	 * @param string $helper name
	 * @return callback
	 */
	public static function loader($helper)
	{
		if (method_exists(__CLASS__, $helper)) {
			return callback(__CLASS__, $helper);
			}
		if (isset(self::$helpers[$helper])) {
			return self::$helpers[$helper];
			}
		// fallback
		return \Nette\Templating\DefaultHelpers::loader($helper);
	}

	/**
	 * Office XML Date formatting
	 * @param string|int|DateTime $date
	 * @return string|NULL
	 */
	public static function oxmlDate($date)
	{
		if ($date==NULL) {
			return NULL;
			}
		return \Nette\DateTime::from($date)->format('Y-m-d')."T00:00:00.000";
	}

	/**
	 * Office XML DateTime formatting
	 * @param string|int|DateTime $date
	 * @return string|NULL
	 */
	public static function oxmlDateTime($date)
	{
		if ($date==NULL) {
			return NULL;
			}
		return \Nette\DateTime::from($date)->format('Y-m-d\TH:i:s').".000";
	}

	/**
	 * Converts to human readable file size, extends/overrides Nette version
	 * @param int $bytes value
	 * @param int $precision number of decimal digits
	 * @param int $k value of 1k
	 * @param bool $iec use ISO/IEC unit names
	 * @see http://www.iso.org/iso/iso_catalogue/catalogue_tc/catalogue_detail.htm?csnumber=31898
	 * @return string formated value
	 */
	public static function bytes($bytes, $precision=2, $kilo=1024, $iec=FALSE)
	{
		$bytes=round($bytes);
		if ($iec && $kilo==1024) {// only if ISO/IEC forced
			$units=array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
			}
		elseif ($kilo==1000 || $kilo==1024) {// 1024 breaks ISO/IEC
			$units=array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
			}
		else {// invalid $k value
			return $bytes;
			}
		foreach ($units as $unit) {
			if (abs($bytes)<$kilo || $unit===end($units)) {
				break;
				}
			$bytes=$bytes/$kilo;
			}
		return round($bytes, $precision)." $unit";
	}

	/**
	 * @param string|int|DateTime $time
	 * @param string $format
	 * @return string
	 */
	public static function datetime($time, $format=NULL)
	{
		if ($time==NULL) { // intentionally ==
			return NULL;
			}
		if (!isset($format)) {
			$format=self::$datetimeFormat;
			}
		return date($format, \Nette\DateTime::from($time)->format('U'));
	}

	public static function texy($text)
	{
		$texy=new \Texy;
		$texy->encoding='utf-8';
		$texy->allowedTags=\Texy::NONE;
		$texy->allowedStyles=\Texy::NONE;
		$texy->setOutputMode(\Texy::HTML5);
		$texy->addHandler('block', array(__CLASS__, 'texyBlockHandler'));

		return $texy->process($text).\Nette\Utils\Html::el('style', $texy->styleSheet);
	}

	public static function texyBlockHandler($invocation, $blocktype, $content, $lang, $modifier)
	{
		list(, $highlighter)=explode('/', Strings::lower($blocktype));
		if (!in_array($highlighter, array('code', 'fshl', 'geshi'))) {
			return $invocation->proceed();
			}
		
		$texy=$invocation->getTexy();
		$content=\Texy::outdent($content);

		switch ($highlighter) {
			case 'geshi':
				if (!class_exists('GeSHi')) {
					return $invocation->proceed();
					}
				if ($lang=='html') {
					$lang='html5';
					}
				$geshi=new \GeSHi($content, $lang, LIBS_DIR.'/GeSHi/geshi');
				if ($geshi->error) {
					return $invocation->proceed();
					}

				$geshi->enable_classes();
				$geshi->set_case_keywords(GESHI_CAPS_NO_CHANGE);
				$geshi->set_tab_width(4);
				$geshi->enable_keyword_links(FALSE);

				$geshi->set_overall_style('color: #000066; border: 1px solid #d0d0d0; background-color: #f0f0f0;', TRUE);
				$geshi->set_line_style('font: normal normal 95% \'Courier New\', Courier, monospace; color: #003030;', 'font-weight: bold; color: #006060;', TRUE);
				$geshi->set_code_style('color: #000020;', 'color: #000020;');
				$geshi->set_link_styles(GESHI_LINK, 'color: #000060;');
				$geshi->set_link_styles(GESHI_HOVER, 'background-color: #f0f000;');
				$texy->styleSheet.=$geshi->get_stylesheet();

				$content=$geshi->parse_code();
				return \TexyHtml::el(NULL, $texy->protect(iconv('UTF-8', 'UTF-8//IGNORE', $content), \Texy::CONTENT_BLOCK));
			case 'fshl':
			case 'code':
				if (!class_exists('\FSHL\Highlighter')) {
					return $invocation->proceed();
					}
				$fshl=new \FSHL\Highlighter(new \FSHL\Output\Html, \FSHL\Highlighter::OPTION_TAB_INDENT);
				$lc='\FSHL\Lexer\\'.Strings::firstUpper(Strings::lower($lang));
				$content=$texy->protect($fshl->highlight($content, new $lc), \Texy::CONTENT_BLOCK);
				$elPre=\TexyHtml::el('pre');
				if ($modifier) {
					$modifier->decorate($texy, $elPre);
					}
				$elPre->attrs['class']=strtolower($lang);
				$elPre->create('code', $content);
				return $elPre;
			}
	}
}
