<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Utils\Translator;

/**
 * @author Lopo <lopo@losys.eu>
 * @see http://www.gnu.org/software/hello/manual/gettext/Plural-forms.html
 */
class PluralForms
{
	/** @var array */
	private static $forms=array(
		1 => array(
			'nplurals' => 1,
			'name' => 'Only one form',
			'rule' => 'plural=0',
			'languages' => array('ja', 'vi', 'ko') //Asian
			),
		2 => array(
			'nplurals' => 2,
			'name' => 'Two forms, singular used for one only',
			'rule' => 'plural=n != 1',
			'languages' => array(
				'en', 'de', 'nl', 'sv', 'da', 'no', 'fo', //Germanic
				'es', 'pt', 'it', 'bg', //Romanic
				'el', //Latin/Greek
				'fi', 'et', 'hu', //Finno-Ugric
				'he', //Semitic
				'eo', //Artificial
				'tr', //Turkic/Altaic
				)
			),
		3 => array(
			'nplurals' => 2,
			'name' => 'Two forms, singular used for zero and one',
			'rule' => 'plural=n>1',
			'languages' => array(
				'pt-br', 'fr' //Romanic
				)
			),
		4 => array(
			'nplurals' => 3,
			'name' => 'Three forms, special case for zero',
			'rule' => 'plural=n%10==1 && n%100!=11 ? 0 : n != 0 ? 1 : 2',
			'languages' => array('lv') //Baltic
			),
		5 => array(
			'nplurals' => 3,
			'name' => 'Three forms, special cases for one and two',
			'rule' => 'plural=n==1 ? 0 : n==2 ? 1 : 2',
			'languages' => array('gd') //Celtic
			),
		6 => array(
			'nplurals' => 3,
			'name' => 'Three forms, special case for numbers ending in 00 or [2-9][0-9]',
			'rule' => 'plural=n==1 ? 0 : (n==0 || (n%100 > 0 && n%100 < 20)) ? 1 : 2',
			'languages' => array('ro') //Romanic
			),
		7 => array(
			'nplurals' => 3,
			'name' => 'Three forms, special case for numbers ending in 1[2-9]',
			'rule' => 'plural=n%10==1 && n%100!=11 ? 0 : n%10>=2 && (n%100<10 || n%100>=20) ? 1 : 2',
			'languages' => array('lt') //Baltic
			),
		8 => array(
			'nplurals' => 3,
			'name' => 'Three forms, special cases for numbers ending in 1 and 2, 3, 4, except those ending in 1[1-4]',
			'rule' => 'plural=n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2',
			'languages' => array('ru', 'uk', 'sr', 'hr') //Slavic
			),
		9 => array(
			'nplurals' => 3,
			'name' => 'Three forms, special cases for 1 and 2, 3, 4',
			'rule' => 'plural=(n==1) ? 0 : (n>=2 && n<=4) ? 1 : 2',
			'languages' => array('cs', 'sk') //Slavic
			),
		10 => array(
			'nplurals' => 3,
			'name' => 'Three forms, special case for one and some numbers ending in 2, 3, or 4',
			'rule' => 'plural=n==1 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2',
			'languages' => array('pl') //Slavic
			),
		11 => array(
			'nplurals' => 4,
			'name' => 'Four forms, special case for one and all numbers ending in 02, 03, or 04',
			'rule' => 'plural=n%100==1 ? 0 : n%100==2 ? 1 : n%100==3 || n%100==4 ? 2 : 3',
			'languages' => array('ls') //Slavic
			)
		);

	/**
	 * @param string $lng
	 * @return array
	 */
	public static function ruleByLanguage($lng)
	{
		foreach (self::$forms as $k => $rule) {
			if (in_array($lng, $rule['languages'])) {
				return $rule;
				}
			}
		return self::$forms[1];
	}
}
