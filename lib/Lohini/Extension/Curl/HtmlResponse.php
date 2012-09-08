<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Curl;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Utils\Strings;

/**
 */
class HtmlResponse
extends Response
{
	/**#@+ regexp's for parsing */
	const CONTENT_TYPE='~^(?P<type>[^;]+);[\t ]*charset=(?P<charset>.+)$~i';
	/**#@- */

	/** @var \Lohini\Extension\Browser\DomDocument */
	private $document;


	/**
	 * @return \Lohini\Extension\Browser\DomDocument
	 */
	public function getDocument()
	{
		if ($this->document===NULL) {
			$this->document=\Lohini\Extension\Browser\DomDocument::fromMalformedHtml($this->getResponse());
			}

		return $this->document;
	}

	/**
	 * @param CurlWrapper $curl
	 * @return string
	 */
	public static function convertEncoding(CurlWrapper $curl)
	{
		if (Strings::checkEncoding($response=$curl->response)) {
			return Strings::normalize($response);
			}

		if ($charset=static::charsetFromContentType($curl->info['content_type'])) {
			$response=@iconv($charset, 'UTF-8', $response);
			}
		else {
			if ($contentType=Strings::match($response, '~<(?P<el>meta[^>]+Content-Type[^>]+)>~i')) {
				foreach (\Nette\Utils\Html::el($contentType['el'])->attrs as $attr => $value) {
					if (strtolower($attr)!=='content') {
						continue;
						}

					if ($charset=static::charsetFromContentType($value)) {
						$response=@iconv($charset, 'UTF-8', $response);
						$response=static::fixContentTypeMeta($response);
						break;
						}
					}
				}
			}

		return Strings::normalize($response);
	}

	/**
	 * @param string $contentType
	 * @return string
	 */
	public static function charsetFromContentType($contentType)
	{
		if ($m=Strings::match($contentType, static::CONTENT_TYPE)) {
			return $m['charset'];
			}
		return NULL;
	}

	/**
	 * Hack for DOMDocument
	 *
	 * @param string $document
	 * @param string $charset
	 * @return string
	 */
	public static function fixContentTypeMeta($document, $charset='utf-8')
	{
		return Strings::replace(
			$document,
			'~<meta[^>]+Content-Type[^>]+>~i',
			'<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />'
			);
	}
}
