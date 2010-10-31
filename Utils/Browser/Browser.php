<?php // vim: ts=4 sw=4 ai:
namespace BailIff\Browser;

use Nette\String,
	Nette\Environment as NEnvironment;

/**
 * BailIff Browser
 *
 * @author Lopo <lopo@losys.eu>
 */
class Browser
{
	public static function getLanguagesPriority()
	{
		if (!$header=NEnvironment::getHttpRequest()->getHeader('accept-language'))
			return NULL;
		$prefered_languages=array();
		if (preg_match_all("#([^;,]+)(;[^,0-9]*([0-9\.]+)[^,]*)?#i", $header, $matches, PREG_SET_ORDER)) {
			$priority=1.0;
			foreach ($matches as $match) {
				if (!isset($match[3])) {
					$pr=$priority;
					$priority-=0.001;
					}
				else {
					$pr=floatval($match[3]);
					}
				$prefered_languages[$match[1]]=$pr;
				}
			arsort($prefered_languages, SORT_NUMERIC);
			return $prefered_languages;
			}
	}

	public static function getLanguageName($lng)
	{
		// pack abbreviation/language array
		// important note: you must have the default language as the last item in each major language, after all the
		// en-ca type entries, so en would be last in that case
		$a_languages=array(
			'af' => 'Afrikaans',
			'sq' => 'Albanian',
			'ar-dz' => 'Arabic (Algeria)',
			'ar-bh' => 'Arabic (Bahrain)',
			'ar-eg' => 'Arabic (Egypt)',
			'ar-iq' => 'Arabic (Iraq)',
			'ar-jo' => 'Arabic (Jordan)',
			'ar-kw' => 'Arabic (Kuwait)',
			'ar-lb' => 'Arabic (Lebanon)',
			'ar-ly' => 'Arabic (libya)',
			'ar-ma' => 'Arabic (Morocco)',
			'ar-om' => 'Arabic (Oman)',
			'ar-qa' => 'Arabic (Qatar)',
			'ar-sa' => 'Arabic (Saudi Arabia)',
			'ar-sy' => 'Arabic (Syria)',
			'ar-tn' => 'Arabic (Tunisia)',
			'ar-ae' => 'Arabic (U.A.E.)',
			'ar-ye' => 'Arabic (Yemen)',
			'ar' => 'Arabic',
			'hy' => 'Armenian',
			'as' => 'Assamese',
			'az' => 'Azeri',
			'eu' => 'Basque',
			'be' => 'Belarusian',
			'bn' => 'Bengali',
			'bg' => 'Bulgarian',
			'ca' => 'Catalan',
			'zh-cn' => 'Chinese (China)',
			'zh-hk' => 'Chinese (Hong Kong SAR)',
			'zh-mo' => 'Chinese (Macau SAR)',
			'zh-sg' => 'Chinese (Singapore)',
			'zh-tw' => 'Chinese (Taiwan)',
			'zh' => 'Chinese',
			'hr' => 'Croatian',
			'cs' => 'Czech',
			'da' => 'Danish',
			'div' => 'Divehi',
			'nl-be' => 'Dutch (Belgium)',
			'nl' => 'Dutch (Netherlands)',
			'en-au' => 'English (Australia)',
			'en-bz' => 'English (Belize)',
			'en-ca' => 'English (Canada)',
			'en-ie' => 'English (Ireland)',
			'en-jm' => 'English (Jamaica)',
			'en-nz' => 'English (New Zealand)',
			'en-ph' => 'English (Philippines)',
			'en-za' => 'English (South Africa)',
			'en-tt' => 'English (Trinidad)',
			'en-gb' => 'English (United Kingdom)',
			'en-us' => 'English (United States)',
			'en-zw' => 'English (Zimbabwe)',
			'en' => 'English',
			'us' => 'English (United States)',
			'et' => 'Estonian',
			'fo' => 'Faeroese',
			'fa' => 'Farsi',
			'fi' => 'Finnish',
			'fr-be' => 'French (Belgium)',
			'fr-ca' => 'French (Canada)',
			'fr-lu' => 'French (Luxembourg)',
			'fr-mc' => 'French (Monaco)',
			'fr-ch' => 'French (Switzerland)',
			'fr' => 'French (France)',
			'mk' => 'FYRO Macedonian',
			'gd' => 'Gaelic',
			'ka' => 'Georgian',
			'de-at' => 'German (Austria)',
			'de-li' => 'German (Liechtenstein)',
			'de-lu' => 'German (Luxembourg)',
			'de-ch' => 'German (Switzerland)',
			'de' => 'German (Germany)',
			'el' => 'Greek',
			'gu' => 'Gujarati',
			'he' => 'Hebrew',
			'hi' => 'Hindi',
			'hu' => 'Hungarian',
			'is' => 'Icelandic',
			'id' => 'Indonesian',
			'it-ch' => 'Italian (Switzerland)',
			'it' => 'Italian (Italy)',
			'ja' => 'Japanese',
			'kn' => 'Kannada',
			'kk' => 'Kazakh',
			'kok' => 'Konkani',
			'ko' => 'Korean',
			'kz' => 'Kyrgyz',
			'lv' => 'Latvian',
			'lt' => 'Lithuanian',
			'ms' => 'Malay',
			'ml' => 'Malayalam',
			'mt' => 'Maltese',
			'mr' => 'Marathi',
			'mn' => 'Mongolian (Cyrillic)',
			'ne' => 'Nepali (India)',
			'nb-no' => 'Norwegian (Bokmal)',
			'nn-no' => 'Norwegian (Nynorsk)',
			'no' => 'Norwegian (Bokmal)',
			'or' => 'Oriya',
			'pl' => 'Polish',
			'pt-br' => 'Portuguese (Brazil)',
			'pt' => 'Portuguese (Portugal)',
			'pa' => 'Punjabi',
			'rm' => 'Rhaeto-Romanic',
			'ro-md' => 'Romanian (Moldova)',
			'ro' => 'Romanian',
			'ru-md' => 'Russian (Moldova)',
			'ru' => 'Russian',
			'sa' => 'Sanskrit',
			'sr' => 'Serbian',
			'sk' => 'Slovak',
			'ls' => 'Slovenian',
			'sb' => 'Sorbian',
			'es-ar' => 'Spanish (Argentina)',
			'es-bo' => 'Spanish (Bolivia)',
			'es-cl' => 'Spanish (Chile)',
			'es-co' => 'Spanish (Colombia)',
			'es-cr' => 'Spanish (Costa Rica)',
			'es-do' => 'Spanish (Dominican Republic)',
			'es-ec' => 'Spanish (Ecuador)',
			'es-sv' => 'Spanish (El Salvador)',
			'es-gt' => 'Spanish (Guatemala)',
			'es-hn' => 'Spanish (Honduras)',
			'es-mx' => 'Spanish (Mexico)',
			'es-ni' => 'Spanish (Nicaragua)',
			'es-pa' => 'Spanish (Panama)',
			'es-py' => 'Spanish (Paraguay)',
			'es-pe' => 'Spanish (Peru)',
			'es-pr' => 'Spanish (Puerto Rico)',
			'es-us' => 'Spanish (United States)',
			'es-uy' => 'Spanish (Uruguay)',
			'es-ve' => 'Spanish (Venezuela)',
			'es' => 'Spanish (Traditional Sort)',
			'sx' => 'Sutu',
			'sw' => 'Swahili',
			'sv-fi' => 'Swedish (Finland)',
			'sv' => 'Swedish',
			'syr' => 'Syriac',
			'ta' => 'Tamil',
			'tt' => 'Tatar',
			'te' => 'Telugu',
			'th' => 'Thai',
			'ts' => 'Tsonga',
			'tn' => 'Tswana',
			'tr' => 'Turkish',
			'uk' => 'Ukrainian',
			'ur' => 'Urdu',
			'uz' => 'Uzbek',
			'vi' => 'Vietnamese',
			'xh' => 'Xhosa',
			'yi' => 'Yiddish',
			'zu' => 'Zulu'
			);
		if (!array_key_exists($lng=String::lower($lng), $a_languages))
			return FALSE;
		return $a_languages[$lng];
	}
}
