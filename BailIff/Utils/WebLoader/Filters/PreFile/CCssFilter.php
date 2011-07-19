<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters;
/*
 * File:        c-css.php
 * CVS:         $Id$
 * Description: Conditional CSS parser
 * Author:      Allan Jardine
 * Created:     Sun May 20 14:05:46 GMT 2007
 * Modified:    $Date$ by $Author$
 * Language:    PHP
 * License:     CDDL v1.0
 * Project:     COnditional-CSS
 *
 * Copyright 2007-2008 Allan Jardine, all rights reserved.
 *
 * This source file is free software, under the U4EA Common Development and
 * Distribution License (U4EA CDDL) v1.0 only, as supplied with this software.
 * This license is also available online:
 *   http://www.sprymedia.co.uk/license/u4ea_cddl
 *
 * This source file is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the CDDL for more details.
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * DESCRIPTION
 *
 * c-css is a program which allows IE style conditional comments to be
 * inserted inline with CSS statements, and then be parsed out as required
 * for individual web browsers. This allows easy targeting of styles to
 * different browsers, and different versions of browsers as required by the
 * developer, such that browser CSS bugs can be easily over come.
 *
 * The bowsers which are currently supported are:
 *   Internet Explorer (v2 up) - IE
 *   Internet Explorer Mac - IEMac
 *   Gecko (Firefox etc) - Gecko
 *   Webkit (Safari etc) - Webkit
 *   Opera - Opera
 *   Konqueror - Konq
 *   Safari Mobile (iPhone, iPod) - SafMob
 *   IE Mobile - IEmob
 *   PSP Web browser - PSP
 *   NetFront - NetF
 *
 * The syntax used for the conditional comments is:
 *   [if {!} {browser}]
 *   [if {!} {browser_group}]
 *   [if {!} {browser} {version}]
 *   [if {!} {condition} {browser} {version}]
 *
 * Examples:
 *   [if ! Gecko]#column_right {
 *     [if cssA]float:left;
 *     width:250px;
 *     [if Webkit] opacity: 0.8;
 *     [if IE 6] ie6: 100%;
 *     [if lt IE 6] lt-ie6: 100%;
 *     [if lte IE 6] lte-ie6: 100%;
 *     [if eq IE 6] eq-ie6: 100%;
 *     [if gte IE 6] gte-ie6: 100%;
 *     [if gt IE 6] gt-ie6: 100%;
 *     [if ! lte IE 6] not-lte-ie6: 100%;
 *   }
 *
 * As can be seen from above a conditional comment can be applied to either
 * a whole CSS block, or to individual rules.
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 * @filesource http://www.conditional-css.com/media/src/Conditional-CSS.zip
 */

use Nette\Utils\Strings,
	Nette\Environment as NEnvironment;

class CCssFilter
extends PreFileFilter
{
	/**
	 * Store the target browser
	 * @var string
	 */
	private $userBrowser='';
	/**
	 * Store the target browser version
	 * @var string
	 */
	private $userVersion=0;
	/**
	 * Browsers can be groups together such that a single conditional
	 *   statement can refer to multiple browsers. For example 'cssA' might be
	 *   top level css support
	 *   The sub arrays must have the following information:
	 *      string:grade - CSS group name for easy groupng of statements
	 *      string:engine - Engine name (used in the conditional statements)
	 *      int:greaterOrEqual - whether the statements should apply for the browser
	 *        version greater (1) or less (0) that the given version
	 *      float:version - The engine version for the condition of this group
	 * @var array
	 */
	public $groups=array(
		array(
			'grade' => 'cssA',
			'engine' => 'IE',
			'greaterOrEqual' => 1,
			'version' => 6
			), /* IE 6 and up */
		array(
			'grade' => 'cssA',
			'engine' => 'Gecko',
			'greaterOrEqual' => 1,
			'version' => 1.0
			), /* Mozilla 1.0 and up */
		array(
			'grade' => 'cssA',
			'engine' => 'Webkit',
			'greaterOrEqual' => 1,
			'version' => 312
			), /* Safari 1.3 and up  */
		array(
			'grade' => 'cssA',
			'engine' => 'SafMob',
			'greaterOrEqual' => 1,
			'version' => 312
			), /* All Mobile Safari  */
		array(
			'grade' => 'cssA',
			'engine' => 'Opera',
			'greaterOrEqual' => 1,
			'version' => 7
			), /* Opera 7 and up */
		array(
			'grade' => 'cssA',
			'engine' => 'Konq',
			'greaterOrEqual' => 1,
			'version' => 3.3
			), /* Konqueror 3.3 and up  */
		array(
			'grade' => 'cssX',
			'engine' => 'IE',
			'greaterOrEqual' => 0,
			'version' => 4
			), /* IE 4 and down */
		array(
			'grade' => 'cssX',
			'engine' => 'IEMac',
			'greaterOrEqual' => 0,
			'version' => 4.5
			) /* IE Mac 4 and down */
		);
	/**
	 * @var string
	 */
	private $output='';
	/**
	 * Store the target group
	 * @var string
	 */
	private $userGroup='';
	/**
	 * css buffer
	 * @var string
	 */
	private $css='';
	/**
	 * @var string
	 */
	public $basePath=NULL;
	/**
	 * @var string
	 */
	public $file=NULL;


	/**
	 * @param array $browser
	 * @param array $groups
	 * @param string $basePath
	 */
	public function __construct($browser=NULL, $groups=NULL, $basePath=NULL)
	{
		if ($browser===NULL) {
			$browser=$this->getUserBrowser();
			}
		$this->userBrowser=$browser['userBrowser'];
		$this->userVersion=$browser['userVersion'];
		if ($groups===NULL) {
			$groups=$this->groups;
			}
		$this->setBrowserGroup($groups);
		$this->outputHeader();
		$this->basePath= $basePath===NULL? WWW_DIR.'/css' : $basePath;
	}

	/**
	 * @see PreFileFilter::__invoke()
	 * @throws FileNotFoundException
	 */
	public static function __invoke($code, \BailIff\WebLoader\WebLoader $loader=NULL, $file=NULL)
	{
		if ($file===NULL || strtolower(pathinfo($file, PATHINFO_EXTENSION))!='ccss') {
			return $code;
			}
		$key=Strings::webalize("ccss-$file");
		$cache=self::getCache();
		$browser=self::getUserBrowser();
		if (($cached=$cache[$key])!==NULL) {
			if (isset($cached[$browser['userBrowser']])) {
				$cachedB=$cached[$browser['userBrowser']];
				if (isset($cachedB[$browser['userVersion']])) {
					return "{[of#CCss#$key#cf]}";
					}
				}
			}
		if (realpath($file)===FALSE) {
			throw new \Nette\FileNotFoundException("Source file '$file' doesn't exist.");
			}

		$filter=new self;
		$content=$filter->complete($file);
		$cached[PreFileFilter::FILE]=$file;
		$cached[$browser['userBrowser']][$browser['userVersion']]=$content;
		self::save($key, $file, $cached);
		return "{[of#CCss#$key#cf]}";
	}

	/**
	 * @param string $file
	 * @return string
	 */
	public function complete($file)
	{
		// X grade CSS means the browser doesn't see the CSS at all
		if ($this->userGroup=='cssX') {
			return $this->output;
			}
		$this->file=$file;
		$this->css=$this->readCssFile($file);
		$this->includes();
		$this->process();
		$this->output();
		return $this->output;
	}

	/**
	 * Get the user's browser information
	 * @return array
	 */
	public static function getUserBrowser()
	{
		$browser=array(
			'userBrowser' => '',
			'userVersion' => 0
			);
		$userAgent=NEnvironment::getHttpRequest()->getHeader('user-agent');
		// MSIE
		if (preg_match('/mozilla.*?MSIE ([0-9a-z\+\-\.]+).*/si', $userAgent, $match)) {
			$browser['userBrowser']='IE';
			$browser['userVersion']=$match[1];
			}
		// Gecko (Firefox, Mozilla, Camino etc)
		elseif (preg_match('/mozilla.*rv:([0-9a-z\+\-\.]+).*gecko.*/si', $userAgent, $match)) {
			$browser['userBrowser']='Gecko';
			$browser['userVersion']=$match[1];
			}
		// Webkit (Safari, Shiira etc)
		elseif (preg_match('/mozilla.*applewebkit\/([0-9a-z\+\-\.]+).*/si', $userAgent, $match)) {
			$browser['userBrowser']='Webkit';
			$browser['userVersion']=$match[1];
			}
		// Opera
		elseif (preg_match('/mozilla.*opera ([0-9a-z\+\-\.]+).*/si', $userAgent, $match)
				|| preg_match('/^opera\/([0-9a-z\+\-\.]+).*/si', $userAgent, $match)) {
			$browser['userBrowser']='Opera';
			$browser['userVersion']=$match[1];
			}
		// Safari Mobile
		elseif (preg_match('/mozilla.*applewebkit\/([0-9a-z\+\-\.]+).*mobile.*/si', $userAgent, $match)) {
			$browser['userBrowser']='SafMob';
			$browser['userVersion']=$match[1];
			}
		// IE Mac
		elseif (preg_match('/mozilla.*MSIE ([0-9a-z\+\-\.]+).*Mac.*/si', $userAgent, $match)) {
			$browser['userBrowser']='IEMac';
			$browser['userVersion']=$match[1];
			}
		// MS mobile
		elseif (preg_match('/PPC.*IEMobile ([0-9a-z\+\-\.]+).*/si', $userAgent, $match)) {
			$browser['userBrowser']='IEMob';
			$browser['userVersion']='1.0';
			}
		// Konqueror
		elseif (preg_match('/mozilla.*konqueror\/([0-9a-z\+\-\.]+).*/si', $userAgent, $match)) {
			$browser['userBrowser']='Konq';
			$browser['userVersion']=$match[1];
			}
		// PSP
		elseif (preg_match('/mozilla.*PSP.*; ([0-9a-z\+\-\.]+).*/si', $userAgent, $match)) {
			$browser['userBrowser']='PSP';
			$browser['userVersion']=$match[1];
			}
		// NetFront
		elseif (preg_match('/mozilla.*NetFront\/([0-9a-z\+\-\.]+).*/si', $userAgent, $match)) {
			$browser['userBrowser']='NetF';
			$browser['userVersion']=$match[1];
			}
		// Round the version number to one decimal place
		if (($iDot=strpos($browser['userVersion'], '.'))>0) {
			$browser['userVersion']=substr($browser['userVersion'], 0, $iDot+2);
			}
		return $browser;
	}

	/**
	 * Based on the browser grouping we set a short hand method for access
	 * @param array $groups group information
	 */
	private function setBrowserGroup($groups)
	{
		for ($i=0; $i<count($groups); $i++) {
			if ($groups[$i]['engine']==$this->userBrowser) {
				if ($groups[$i]['greaterOrEqual']==1 && $groups[$i]['version']<=$this->userVersion) {
					$this->userGroup=$groups[$i]['grade'];
					break;
					}
				else if ($groups[$i]['greaterOrEqual']==0 && $groups[$i]['version']>=$this->userVersion) {
					$this->userGroup=$groups[$i]['grade'];
					break;
					}
				}
			}
	}

	/**
	 * Header output with information
	 */
	private function outputHeader()
	{
		// Add comment to output
		$this->output="/*\n"
					." * Browser:       $this->userBrowser $this->userVersion\n"
					." * Browser group: $this->userGroup\n"
					." */\n";
	}

	/**
	 * Read a CCSS file
	 * @param string $file the file name and path to be read
	 * @return string
	 */
	private function readCssFile($file)
	{
/*
		// We use output buffering here to read the required file using 'readfile'
		// as this allows us to over come some of the problems when safe mode is
		// turned on
		ob_start();
		readfile($file);
		$sCSS=ob_get_contents();
		ob_end_clean();
*/
		$Css=file_get_contents($file);
		// If there is a hash-bang line - strip it out for compatability with C
		$Css=preg_replace('/^(#!.*?\n)/', '', $Css, 1);
		return $Css;
	}

	/**
	 * Check the input for @import statements and include files found
	 */
	private function includes()
	{
		// First remove any comments as they could get in the way
		$this->stripComments();
		// Find all conditional @import statements
		while (preg_match('/\[if .*?\]\s*?@import .*?;/s', $this->css, $match)) {
			preg_match('/\[if .*?\]/', $match[0], $CCBlock);
			$import=trim(preg_replace('/\[if .*?\]/', '', $match[0]));
			$this->cssImport($import, $this->checkCC($CCBlock[0]), $match[0]);
			unset($match);
			}
		// Find all non-conditional @import statements
		while (preg_match('/@import .*?;/s', $this->css, $match)) {
			$this->cssImport($match[0], TRUE, $match[0]);
			unset($match);
			}
	}

	/**
	 * Strip multi-line comments from the target css
	 */
	private function stripComments()
	{
		$this->css=preg_replace('/\/\*.*?\*\//s', '', $this->css);
	}

	/**
	 * Deal with an import CSS file
	 * @param string $importStatement @import...
	 * @param bool $import include the file or not
	 * @param string $fullImport The full string to remove
	 */
	private function cssImport($importStatement, $import, $fullImport)
	{
		if ($import) { // XXX
			$tmpCSS=$this->parseImport($importStatement);
			if (strtolower(substr($tmpCSS, -4))!='.css' && strtolower(substr($tmpCSS, -5))!='.ccss') { // import only raw css and ccss
				$this->css=str_replace($fullImport, '', $this->css);
				return;
				}
			// Save it back into the main css string
			$this->css=str_replace($fullImport, $this->readCSSFile($tmpCSS[0]=='/'? WWW_DIR.$tmpCSS : "$this->basePath/$tmpCSS"), $this->css);
			// Remove comments to ease parsing
			$this->stripComments();
			}
		else {
			// Remove the import statement
			$this->css=str_replace($fullImport, '', $this->css);
			}
	}

	/**
	 * Get the import URI from the import statement
	 * @param string $import @import CSS statement
	 * @return string
	 */
	private function parseImport($import)
	{
		$aImport=explode(' ', $import);
		$url=trim($aImport[1]);

		if (substr($url, 0, 3)=='url') {
			$url=substr($url, 3);
			}
		$url=str_replace('(', '', $url);
		$url=str_replace(')', '', $url);
		$url=str_replace("'", '', $url);
		$url=str_replace('"', '', $url);
		$url=str_replace(';', '', $url);
		return $url;
	}

	/**
	 * See if a conditional comment should be processed
	 * Notes:
	 * The browser conditions are:
	 *  [if {!} {browser}]
	 *  [if {!} {browser} {version}]
	 *  [if {!} {condition} {browser} {version}]
	 * @param string $sCC the conditional comment
	 * @return bool
	 */
	private function checkCC($sCC)
	{
		// Strip brackets from the CC
		$sCC=str_replace('[', '', $sCC);
		$sCC=str_replace(']', '', $sCC);

		$aCC=explode(' ', $sCC);

		$bNegate=FALSE;
		if (isset($aCC[1]) && $aCC[1]=='!') {
			$bNegate=TRUE;
			// Remove the negation operator so all the other operators are in place
			array_splice($aCC, 1, 1);
			}
		//
		// Do the logic checking
		//
		$bInclude=FALSE;
		// If the CC is an integer, then we drop the minor version number from the
		// users browser. This means that if the user is using v5.5, and the
		// statement is for v5, then it matches. To stop this a CC with v5.0 would
		// have to be used
		$sLocalUserVersion=$this->userVersion;
		if (count($aCC)==3 && !strpos($aCC[2], '.')) { /* if {browser} {version} */
			$sLocalUserVersion=intval($sLocalUserVersion);
			}
		else if (count($aCC)==4 && !strpos($aCC[3], '.')) { /* if {condition} {browser} {version} */
			$sLocalUserVersion=intval($sLocalUserVersion);
			}

		// Just the browser
		if (count($aCC)==2) {
			if ($this->userBrowser==$aCC[1] || $this->userGroup==$aCC[1]) {
				$bInclude=TRUE;
				}
			}
		// Browser and version
		elseif (count($aCC)==3) {
			if ($this->userBrowser==$aCC[1] && (float)$sLocalUserVersion==(float)$aCC[2]) {
				$bInclude=TRUE;
				}
			}
		// Borwser and version with operator
		elseif (count($aCC)==4) {
			if (strlen($aCC[1])==3) { // lte, gte -> le, ge
				$op=$aCC[1][0].'e';
				}
			if ($this->userBrowser==$aCC[2] && version_compare((float)$sLocalUserVersion, (float)$aCC[3], $op)) {
				$bInclude=TRUE;
				}
			}
		// Perform negation if required
		return $bNegate? !$bInclude : $bInclude;
	}

	/**
	 * Strip multi-line comments from the target css
	 */
	private function process()
	{
		// Break the CSS down into blocks
		// Match all blacks - with or without nested blocks
		preg_match_all('/.*?\{((?>[^{}]*)|(?R))*\}/s', $this->css, $CSSBlock);
		for ($i=0; $i<count($CSSBlock[0]); $i++) {
			$processBlock=TRUE;
			$block=$CSSBlock[0][$i];
			// Find if the block has a conditional comment
			if (preg_match('/\[if .*?\].*?\{/s', $block)) {
				preg_match('/\[if .*?\]/', $block, $CCBlock);
				// Find out if the block should be included or not
				if (!$this->checkCC($CCBlock[0])) {
					$processBlock=FALSE;
					// Drop the block from the output string
					$this->css=str_replace($CSSBlock[0][$i], '', $this->css);
					}
				// If it should be then remove the conditional comment from the start
				// of the block
				else {
					$block=preg_replace('/\[if .*?\]/', '', $block, 1);
					}
				}
			// If the block should be processed
			if ($processBlock) {
				// Loop over the block looking for conditional comment statements
				while (preg_match('/\[if .*?\]/', $block, $CSSRule)) {
					// See if statement should be included or not
					if (!$this->checkCC($CSSRule[0])) {
						// Remove statement - note that this might remove the trailing
						// } of the block! This is valid css as the last statement is
						// implicitly closed by the }. So we moke sure there is one at the
						// end later on
						$block=preg_replace('/\[if .*?\].*?(;|\})/s', '', $block, 1);
					}
					// Include statement
					else {
						// Remove CC
						$block=preg_replace('/\[if .*?\]/', '', $block, 1);
						}
					}
				// Ensure the block has a closing }
				if (!preg_match('/\}$/', $block)/*==0*/) {
					$block.='}';
					}
				// Write the modifed block back into the CSS string
				$this->css=str_replace($CSSBlock[0][$i], $block, $this->css);
				}
			}
	}

	/**
	 * Remove extra white space and output
	 */
	private function output()
	{
		// Remove the white space in the css - while preserving the needed spaces
		$this->css=preg_replace('/\s/s', ' ', trim($this->css));
		while (preg_match('/  /', $this->css)) {
			$this->css=preg_replace('/  /', ' ', $this->css);
			}
		// Add new lines for basic legibility
		$this->css=preg_replace('/} /', "}\n", $this->css);
		// Phew - we finally got there...
		$this->output.="$this->css\n";
	}

	/**
	 * @see PreFileFilter::getItem()
	 */
	public static function getItem($key)
	{
		$cache=self::getCache();
		if (($cached=$cache[$key])!==NULL) {
			$browser=self::getUserBrowser();
			if (isset($cached[$browser['userBrowser']])) {
				$cachedB=$cached[$browser['userBrowser']];
				if (isset($cachedB[$browser['userVersion']])) {
					return $cachedB[$browser['userVersion']];
					}
				}
			self::__invoke('', NULL, $cached[PreFileFilter::FILE]);
			return self::getItem($key);
			}
		return '';
	}
}
