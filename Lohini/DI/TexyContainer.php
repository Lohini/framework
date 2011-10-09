<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\DI;

use Nette\Utils\Strings;

/**
 * Texy container
 *
 * @author Lopo <lopo@lohini.net>
 *
 * @property-read \Texy $texy
 */
class TexyContainer
extends Container
{
	public function __construct(\Nette\DI\Container $context)
	{
		$this->addService('context', $context);
	}

	/**
	 * @return \Texy
	 */
	public function createServiceTexy()
	{
		$texy=new \Texy;
		$texy->encoding='utf-8';
//		$texy->allowedTags=\Texy::NONE;
//		$texy->allowedStyles=\Texy::NONE;

		// output
		$texy->setOutputMode(\Texy::HTML5);
		$texy->htmlOutputModule->removeOptional=FALSE;
		$texy::$advertisingNotice=FALSE;

		// headings
		$texy->headingModule->balancing=\TexyHeadingModule::FIXED;

		// phrases
		$texy->allowed['phrase/ins']=TRUE; // ++inserted++
		$texy->allowed['phrase/del']=TRUE; // --deleted--
		$texy->allowed['phrase/sup']=TRUE; // ^^superscript^^
		$texy->allowed['phrase/sub']=TRUE; // __subscript__
		$texy->allowed['phrase/cite']=TRUE; // ~~cite~~
		$texy->allowed['deprecated/codeswitch']=TRUE; // `=code

		// images
		$texy->imageModule->fileRoot=WWW_DIR.'/var/storage/texyla';
		$url=$this->context->router->constructUrl(
				new \Nette\Application\Request('Texyla', 'GET', array('action' => 'file')),
				$this->context->httpRequest->url
				);
		$texy->imageModule->root=$url;

		// flash, youtube.com, stream.cz handlers
		$texy->addHandler('image', array($this, 'youtubeHandler'));
		$texy->addHandler('image', array($this, 'streamHandler'));
		$texy->addHandler('image', array($this, 'flashHandler'));
		$texy->addHandler('phrase', array($this, 'netteLink'));
		$texy->addHandler('block', array($this, 'blockHandler'));
		$texy->addHandler('image', array($this, 'gravatarHandler'));

		return $texy;
	}

	/**
	 * Template factory
	 * @return Template
	 */
	private function createTemplate()
	{
		$template=new \Nette\Templating\FileTemplate;
		$template->registerFilter(new \Nette\Latte\Engine);
		return $template;
	}

	/**
	 * @param \TexyHandlerInvocation $invocation
	 * @param string $phrase
	 * @param string $content
	 * @param \TexyModifier $modifier
	 * @param \TexyLink $link
	 * @return \TexyHtml|string|FALSE
	 */
	public function netteLink($invocation, $phrase, $content, $modifier, $link)
	{
		// is there link?
		if (!$link) {
			return $invocation->proceed();
			}

		$url=$link->URL;

		if (Strings::startsWith($url, 'plink://')) {
			$url=substr($url, 8);
			list($presenter, $params)=explode('?', $url, 2);

			$arr=array();

			if ($params) {
				parse_str($params, $arr);
				}

			$link->URL=$this->getService('context')->application->getPresenter()->link($presenter, $arr);
			}

		return $invocation->proceed();
	}

	/**
	 * YouTube handler for images
	 *
	 * @example [* youtube:JG7I5IF6 *]
	 *
	 * @param \TexyHandlerInvocation $invocation
	 * @param \TexyImage $image
	 * @param \TexyLink $link
	 * @return \TexyHtml|string|FALSE
	 */
	public function youtubeHandler($invocation, $image, $link)
	{
		$parts=explode(':', $image->URL, 2);

		if (count($parts)!==2 || $parts[0]!=='youtube') {
			return $invocation->proceed();
			}

		$template=$this->createTemplate()->setFile(__DIR__.'/templates/Texy/@youtube.latte');
		$template->id=$parts[1];
		if ($image->width) {
			$template->width=$image->width;
			}
		if ($image->height) {
			$template->height=$image->height;
			}

		return $this->texy->protect((string)$template, \Texy::CONTENT_BLOCK);
	}

	/**
	 * Flash handler for images
	 *
	 * @example [* flash.swf 200x150 .(alternative content) *]
	 *
	 * @param \TexyHandlerInvocation $invocation
	 * @param \TexyImage $image
	 * @param \TexyLink $link
	 * @return \TexyHtml|string|FALSE
	 */
	public function flashHandler($invocation, $image, $link)
	{
		if (!Strings::endsWith($image->URL, '.swf')) {
			return $invocation->proceed();
			}

		$template=$this->createTemplate()->setFile(__DIR__.'/templates/Texy/@flash.latte');
		$template->url=\Texy::prependRoot($image->URL, $this->texy->imageModule->root);
		$template->width=$image->width;
		$template->height=$image->height;
		if ($image->modifier->title) {
			$template->title=$image->modifier->title;
			}

		return $this->texy->protect((string)$template, \Texy::CONTENT_BLOCK);
	}

	/**
	 * User handler for images
	 *
	 * @example [* stream:98GDAS675G *]
	 *
	 * @param \TexyHandlerInvocation $invocation
	 * @param \TexyImage $image
	 * @param \TexyLink $link
	 * @return \TexyHtml|string|FALSE
	 */
	public function streamHandler($invocation, $image, $link)
	{
		$parts=explode(':', $image->URL, 2);

		if (count($parts)!==2 || $parts[0]!=='stream') {
			return $invocation->proceed();
			}

		$template=$this->createTemplate()->setFile(__DIR__.'/templates/Texy/@stream.latte');
		$template->id=$parts[1];
		if ($image->width) {
			$template->width=$image->width;
			}
		if ($image->height) {
			$template->height=$image->height;
			}

		return $this->texy->protect((string)$template, \Texy::CONTENT_BLOCK);
	}

	/**
	 * User handler for code block
	 *
	 * @param \TexyHandlerInvocation $invocation
	 * @param string $blocktype
	 * @param string $content
	 * @param string $lang
	 * @param string $modifier
	 * @return \TexyHtml 
	 */
	public function blockHandler($invocation, $blocktype, $content, $lang, $modifier)
	{
		list(, $highlighter)=explode('/', Strings::lower($blocktype));
		if (!in_array($highlighter, array('code', 'fshl', 'geshi'))) {
			return $invocation->proceed();
			}
		if ($lang===NULL) {
			$lang='minimal';
			$highlighter='fshl';
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
				$geshi->set_line_style("font: normal normal 95% 'Courier New', Courier, monospace; color: #003030;", 'font-weight: bold; color: #006060;', TRUE);
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

	/**
	 * YouTube handler for images
	 *
	 * @example [* youtube:JG7I5IF6 *]
	 *
	 * @param \TexyHandlerInvocation $invocation
	 * @param \TexyImage $image
	 * @param \TexyLink $link
	 * @return \TexyHtml|string|FALSE
	 */
	public function gravatarHandler($invocation, $image, $link)
	{
		$parts=explode(':', $image->URL, 2);

		if (count($parts)!==2 || $parts[0]!=='gravatar') {
			return $invocation->proceed();
			}

		$template=$this->createTemplate()->setFile(__DIR__.'/templates/Texy/@gravatar.latte');
		$template->email=$parts[1];
		if ($image->width) {
			$template->width=$image->width;
			}
		if ($image->height) {
			$template->height=$image->height;
			}

		return $this->texy->protect((string)$template, \Texy::CONTENT_BLOCK);
	}
}
