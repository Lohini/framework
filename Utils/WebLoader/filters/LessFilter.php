<?php // vim: ts=4 sw=4 ai:
namespace BailIff\WebLoader\Filters;

/**
 * lessphp v0.2.0
 * http://leafo.net/lessphp
 *
 * LESS Css compiler, adapted from http://lesscss.org/docs.html
 *
 * Copyright 2010, Leaf Corcoran <leafot@gmail.com>
 * Licensed under MIT or GPLv3, see LICENSE
 */
/**
 * BailIff port
 * @version 1.0.0
 * @author Lopo <lopo@losys.eu>
 */

//
// fix the alpha value with color when using a percent
//

use BailIff\WebLoader\WebLoader;

class LessFilter
{
	private $buffer;
	private $count;
	private $line;
	private $expandStack;
	private $media;
	private $indentLevel;
	private $level;
	private $inAnimations;

	private $env=array();
	private $allParsedFiles=array();

	public $vPrefix='@';
	public $mPrefix='$';
	public $imPrefix='!';
	public $selfSelector='&';

	static private $precedence=array(
		'+' => 0,
		'-' => 0,
		'*' => 1,
		'/' => 1,
		'%' => 1,
		);
	static private $operatorString; // regex string to match any of the operators

	static private $dtypes=array('expression', 'variable', 'function', 'negative'); // types with delayed computation
	/**
	 * @link http://www.w3.org/TR/css3-values/
	 */
	static private $units=array(
			'em', 'ex', 'px', 'gd', 'rem', 'vw', 'vh', 'vm', 'ch', // Relative length units
			'in', 'cm', 'mm', 'pt', 'pc', // Absolute length units
			'%', // Percentages
			'deg', 'grad', 'rad', 'turn', // Angles
			'ms', 's', // Times
			'Hz', 'kHz', //Frequencies
			);

	public $importDisabled=FALSE;
	public $importDir='';


	public function __construct($fname=NULL)
	{
		if (!self::$operatorString) {
			self::$operatorString='('.implode('|', array_map(array($this, 'preg_quote'), array_keys(self::$precedence))).')';
			}
		if ($fname) {
			if (!is_file($fname)) {
				throw new \Exception("load error: failed to find $fname");
				}
			$pi=pathinfo($fname);

			$this->fileName=$fname;
			$this->importDir=$pi['dirname'].'/';
			$this->buffer=file_get_contents($fname);
			$this->addParsedFile($fname);
			}
	}

	/**
	 * compile chunk off the head of buffer
	 * @return bool|string
	 * @throws \Exception
	 */
	private function chunk()
	{
		if (empty($this->buffer)) {
			return FALSE;
			}
		$s=$this->seek();

		// a property
		if ($this->keyword($key) && $this->assign() && $this->propertyValue($value) && $this->end()) {
			// look for important prefix
			if ($key{0}==$this->imPrefix && strlen($key)>1) {
				$key=substr($key, 1);
				if ($value[0]=='list' && $value[1]==' ') {
					$value[2][]=array('keyword', '!important');
					}
				else {
					$value=array('list', ' ', array($value, array('keyword', '!important')));
					}
				}
			$this->append($key, $value);

			if (count($this->env)==1) {
				return $this->compileProperty($key, array($value))."\n";
				}
			else {
				return TRUE;
				}
			}
		else {
			$this->seek($s);
			}

		// look for special css @ directives
		if (count($this->env)==1 && $this->count<strlen($this->buffer) && $this->buffer[$this->count]=='@') {
			// a font-face block
			if ($this->literal('@font-face') && $this->literal('{')) {
				$this->push();
				$this->set('__tags', array('@font-face'));
				$this->set('__dontsave', TRUE);
				return TRUE;
				}
			else {
				$this->seek($s);
				}

			// charset
			if ($this->literal('@charset') && $this->propertyValue($value) && $this->end()) {
				return $this->indent('@charset '.$this->compileValue($value).';');
				}
			else {
				$this->seek($s);
				}

			// media
			if ($this->literal('@media') && $this->mediaTypes($types, $rest) && $this->literal('{')) {
				$this->media=$types;
				$this->indentLevel++;
				return '@media '.join(', ', $types).(!empty($rest)? " $rest" : '' )." {\n";
				}
			else {
				$this->seek($s);
				}

			// css animations
			if ($this->match('(@(-[a-z]+-)?keyframes)', $m) && $this->propertyValue($value) && $this->literal('{')) {
				$this->indentLevel++;
				$this->inAnimations=TRUE;
				return $m[0].$this->compileValue($value)." {\n";
				}
			else {
				$this->seek($s);
				}
			}

		// see if we're in animations and handle pseudo classess
		if ($this->inAnimations && $this->match("(to|from|[0-9]+%)", $m) && $this->literal('{')) {
			$this->push();
			$this->set('__args', array($m[1]));
			return TRUE;
			}
		else {
			$this->seek($s);
			}

		// setting variable
		if ($this->variable($name) && $this->assign() && $this->propertyValue($value) && $this->end()) {
			$this->append($this->vPrefix.$name, $value);
			return TRUE;
			}
		else {
			$this->seek($s);
			}

		// opening abstract block
		if ($this->tag($tag, TRUE) && $this->argumentDef($args) && $this->literal('{')) {
			$this->push();

			// move out of variable scope
			if ($tag{0}==$this->vPrefix) {
				$tag[0]=$this->mPrefix;
				}

			$this->set('__tags', array($tag));
			if (isset($args)) {
				$this->set('__args', $args);
				}
			return TRUE;
			}
		else {
			$this->seek($s);
			}

		// opening css block
		if ($this->tags($tags) && $this->literal('{')) {
			//  move @ tags out of variable namespace!
			foreach ($tags as &$tag) {
				if ($tag{0}==$this->vPrefix) {
					$tag[0]=$this->mPrefix;
					}
				}

			$this->push();
			$this->set('__tags', $tags);	

			return TRUE;
			}
		else {
			$this->seek($s);
			}

		// closing block
		if ($this->literal('}')) {
			if ($this->level==1 && !is_null($this->media)) {
				$this->indentLevel--;
				$this->media=NULL;
				return "}\n";
				}

			if ($this->level==1 && $this->inAnimations===TRUE) {
				$this->indentLevel--;
				$this->inAnimations=FALSE;
				return "}\n";
				}

			$tags=$this->multiplyTags();
			$env=end($this->env);
			$ctags=$env['__tags'];
			unset($env['__tags']);

			// insert the default arguments
			if (isset($env['__args'])) {
				foreach ($env['__args'] as $arg) {
					if (isset($arg[1])) {
						$this->prepend($this->vPrefix.$arg[0], $arg[1]);
						}
					}
				}

			if (!empty($tags)) {
				$out=$this->compileBlock($tags, $env);
				}
			try {
				$this->pop();
				}
			catch (\Exception $e) {
				$this->seek($s);
				$this->throwParseError($e->getMessage());
				}

			// make the block(s) available in the new current scope
			if (!isset($env['__dontsave'])) {
				foreach ($ctags as $t) {
					// if the block already exists then merge
					if ($this->get($t, array(end($this->env)))) {
						$this->merge($t, $env);
						}
					else {
						$this->set($t, $env);
						}
					}
				}
			return isset($out)? $out : TRUE;
			}
		
		// import statement
		if ($this->import($url, $media)) {
			if ($this->importDisabled) {
				return "/* import is disabled */\n";
				}

			$full=$this->importDir.$url;
			if ($this->fileExists($file=$full) || $this->fileExists($file=$full.'.less')) {
				$this->addParsedFile($file);
				$loaded=ltrim($this->removeComments(file_get_contents($file).';'));
				$this->buffer=substr($this->buffer, 0, $this->count).$loaded.substr($this->buffer, $this->count);
				return TRUE;
				}
			return $this->indent('@import url("'.$url.'")'.($media ? ' '.$media : '').';');
			}

		// mixin/function expand
		if ($this->tags($tags, TRUE, '>') && ($this->argumentValues($argv) || TRUE) && $this->end()) {
			$env=$this->getEnv($tags);
			if ($env==NULL) {
				return TRUE;
				}

			// if we have arguments then insert them
			if (!empty($env['__args'])) {
				foreach ($env['__args'] as $arg) {
					$vname=$this->vPrefix.$arg[0];
					$value= is_array($argv)? array_shift($argv) : NULL;
					// copy default value if there isn't one supplied
					if ($value==NULL && isset($arg[1]))
						$value=$arg[1];

					// if ($value == null) continue; // don't define so it can search up

					// create new entry if var doesn't exist in scope
					if (isset($env[$vname])) {
						array_unshift($env[$vname], $value);
						}
					else {
						// new element
						$env[$vname]=array($value);
						}
					}
				}

			// copy all properties from tmp env to current block
			ob_start();
			$blocks=array();
			$toReduce=array();
			foreach ($env as $name => $value) {
				// skip the metatdata
				if (preg_match('/^__/', $name)) {
					continue;
					}

				// if it is a block, remember it to compile after everything
				// is mixed in
				if (!isset($value[0])) {
					$blocks[]=array($name, $value);
					}
				else if ($name{0}!=$this->vPrefix) {
					$toReduce[]=$name;
					}

				// copy the data
				// don't overwrite previous value, look in current env for name
				if ($this->get($name, array(end($this->env)))) {
					while ($tval=array_shift($value)) {
						$this->append($name, $tval);
						}
					}
				else { 
					$this->set($name, $value);
					} 
				}

			// extract the args as a temp environment, put them before top
			if (isset($env['__args'])) {
				$tmp=array();
				foreach ($env['__args'] as $arg) {
					if (isset($arg[1])) {// if there is a value
						$tmp[$this->vPrefix.$arg[0]]=array($arg[1]);
						}
					}
				$top=array_pop($this->env);
				array_push($this->env, $tmp, $top);
				}

			// reduce all values that came out of this mixin
			foreach ($toReduce as $name) {
				$reduced=array();
				foreach ($this->get($name) as $value) {
					$reduced[]=$this->reduce($value);
					}
				$this->set($name, $reduced);
				}

			if (isset($env['__args'])) {
				// get rid of tmp
				$top=array_pop($this->env);
				array_pop($this->env);
				array_push($this->env, $top);
				}

			// render sub blocks
			foreach ($blocks as $b) {
				$rtags=$this->multiplyTags(array($b[0]));
				echo $this->compileBlock($rtags, $b[1]);
				}

			return ob_get_clean();
			}
		else {
			$this->seek($s);
			}

		// spare ;
		if ($this->literal(';')) {
			return TRUE;
			}
		return FALSE; // couldn't match anything, throw error
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	private function fileExists($name)
	{
		// sym link workaround
		return file_exists($name) || file_exists(realpath(preg_replace('/\w+\/\.\.\//', '', $name)));
	}

	/**
	 * recursively find the cartesian product of all tags in stack
	 * @param array $tags
	 * @param int $d
	 * @return array
	 */
	private function multiplyTags($tags=array(' '), $d=NULL)
	{
		if ($d===NULL) {
			$d=count($this->env)-1;
			}

		$parents= $d==0? $this->env[$d]['__tags'] : $this->multiplyTags($this->env[$d]['__tags'], $d-1);

		$rtags=array();
		foreach ($parents as $p) {
			foreach ($tags as $t) {
				if ($t{0}==$this->mPrefix) {
					continue; // skip functions
					}
				$d=' ';
				if ($t{0}==':' || $t{0}==$this->selfSelector) {
					$t=ltrim($t, $this->selfSelector);
					$d='';
					}
				$rtags[]=trim($p.$d.$t);
				}
			}
		return $rtags;
	}

	/**
	 * a list of expressions
	 * @param unknown_type &$exps
	 * @return bool
	 */
	private function expressionList(&$exps)
	{
		$values=array();	

		while ($this->expression($exp)) {
			$values[]=$exp;
			}
		if (count($values)==0) {
			return FALSE;
			}

		$exps=$this->compressList($values, ' ');
		return TRUE;
	}

	/**
	 * a single expression
	 * @param unknown_type &$out
	 * @return bool
	 */
	private function expression(&$out)
	{
		$s=$this->seek();
		$needWhite=TRUE;
		if ($this->literal('(') && $this->expression($exp) && $this->literal(')')) {
			$lhs=$exp;
			$needWhite=FALSE;
			}
		elseif ($this->seek($s) && $this->value($val)) {
			$lhs=$val;
			}
		else {
			return FALSE;
			}
		$out=$this->expHelper($lhs, 0, $needWhite);
		return TRUE;
	}

	/**
	 * resursively parse infix equation with $lhs at precedence $minP
	 * @param unknown_type $lhs
	 * @param int $minP
	 * @param bool $needWhite
	 */
	private function expHelper($lhs, $minP, $needWhite=TRUE)
	{
		$ss=$this->seek();
		// try to find a valid operator
		while ($this->match(self::$operatorString.($needWhite? '\s+' : ''), $m) && self::$precedence[$m[1]]>=$minP) {
			$needWhite=TRUE;
			// get rhs
			$s=$this->seek();
			if ($this->literal('(') && $this->expression($exp) && $this->literal(')')) {
				$needWhite=FALSE;
				$rhs=$exp;
				}
			elseif ($this->seek($s) && $this->value($val)) {
				$rhs=$val;
				}
			else {
				break;
				}

			// peek for next operator to see what to do with rhs
			if ($this->peek(self::$operatorString, $next) && self::$precedence[$next[1]]>$minP) {
				$rhs=$this->expHelper($rhs, self::$precedence[$next[1]]);
				}

			// don't evaluate yet if it is dynamic
			if (in_array($rhs[0], self::$dtypes) || in_array($lhs[0], self::$dtypes)) {
				$lhs=array('expression', $m[1], $lhs, $rhs);
				}
			else {
				$lhs=$this->evaluate($m[1], $lhs, $rhs);
				}
			$ss=$this->seek();
			}
		$this->seek($ss);
		return $lhs;
	}

	/**
	 * consume a list of values for a property
	 * @param unknown_type &$value
	 * @return bool
	 */
	private function propertyValue(&$value)
	{
		$values=array();	
		$s=null;
		while ($this->expressionList($v)) {
			$values[]=$v;
			$s=$this->seek();
			if (!$this->literal(',')) {
				break;
				}
			}

		if ($s) {
			$this->seek($s);
			}
		if (!count($values)) {
			return FALSE;
			}

		$value=$this->compressList($values, ', ');
		return TRUE;
	}

	/**
	 * a single value
	 * @param array? &$value
	 * @return bool
	 */
	private function value(&$value)
	{
		// try a unit
		if ($this->unit($value)) {
			return TRUE;
			}	

		// see if there is a negation
		$s=$this->seek();
		if ($this->literal('-', FALSE) && $this->variable($vname)) {
			$value=array('negative', array('variable', $this->vPrefix.$vname));
			return TRUE;
			}
		else {
			$this->seek($s);
			}

		// accessor 
		// must be done before color
		// this needs negation too
		if ($this->accessor($a)) {
			$tmp=$this->getEnv($a[0]);
			if ($tmp && isset($tmp[$a[1]])) {
				$value=end($tmp[$a[1]]);
				}
			return TRUE;
			}
		// color
		if ($this->color($value)) {
			return TRUE;
			}
		// css function
		// must be done after color
		if ($this->func($value)) {
			return TRUE;
			}
		// string
		if ($this->string($tmp, $d)) {
			$value=array('string', $d.$tmp.$d);
			return TRUE;
			}
		// try a keyword
		if ($this->keyword($word)) {
			$value=array('keyword', $word);
			return TRUE;
			}
		// try a variable
		if ($this->variable($vname)) {
			$value=array('variable', $this->vPrefix.$vname);
			return TRUE;
			}
		return FALSE;
	}

	/**
	 * an import statement
	 * @param string &$url
	 * @param string &$media
	 * @return bool
	 */
	private function import(&$url, &$media)
	{
		$s=$this->seek();
		if (!$this->literal('@import')) {
			return FALSE;
			}

		// @import "something.css" media;
		// @import url("something.css") media;
		// @import url(something.css) media; 

		if ($this->literal('url(')) {
			$parens=TRUE;
			}
		else {
			$parens=FALSE;
			}

		if (!$this->string($url)) {
			if ($parens && $this->to(')', $url)) {
				$parens=FALSE; // got em
				}
			else {
				$this->seek($s);
				return FALSE;
				}
			}

		if ($parens && !$this->literal(')')) {
			$this->seek($s);
			return FALSE;
			}

		// now the rest is media
		return $this->to(';', $media, FALSE, TRUE);
	}

	/**
	 * a list of media types, very lenient
	 * @param array &$types
	 * @param unknown_type &$rest
	 * @return bool
	 */
	private function mediaTypes(&$types, &$rest)
	{
		$s=$this->seek();
		$types=array();
		while ($this->match('([^,{\s]+)', $m)) {
			$types[]=$m[1];
			if (!$this->literal(',')) {
				break;
				}
			}

		// get everything else
		if ($this->to('{', $rest, TRUE, TRUE)) {
			$rest=trim($rest);
			}
		return count($types)>0;
	}

	/**
	 * a scoped value accessor
	 * @example .hello > @scope1 > @scope2['value'];
	 * @param array &$var
	 * @return bool
	 */
	private function accessor(&$var)
	{
		$s=$this->seek();

		if (!$this->tags($scope, TRUE, '>') || !$this->literal('[')) {
			$this->seek($s);
			return FALSE;
			}

		// either it is a variable or a property
		// why is a property wrapped in quotes, who knows!
		if ($this->variable($name)) {
			$name=$this->vPrefix.$name;
			}
		elseif($this->literal("'") && $this->keyword($name) && $this->literal("'")) {
			// .. $this->count is messed up if we wanted to test another access type
			}
		else {
			$this->seek($s);
			return FALSE;
			}

		if (!$this->literal(']')) {
			$this->seek($s);
			return FALSE;
			}

		$var=array($scope, $name);
		return TRUE;
	}

	/**
	 * a string
	 * @param string &$string
	 * @param string &$d
	 * @return bool
	 */
	private function string(&$string, &$d=NULL)
	{
		$s=$this->seek();
		if ($this->literal('"', FALSE)) {
			$delim='"';
			}
		else if($this->literal("'", FALSE)) {
			$delim="'";
			}
		else {
			return FALSE;
			}

		if (!$this->to($delim, $string)) {
			$this->seek($s);
			return FALSE;
			}
		
		$d=$delim;
		return TRUE;
	}

	/**
	 * a numerical unit
	 * @param array? &$unit
	 * @param array $allowed
	 */
	private function unit(&$unit, $allowed=NULL)
	{
		$simpleCase= $allowed==NULL;
		if (!$allowed) {
			$allowed=self::$units;
			}

		if ($this->match('(-?[0-9]*(\.)?[0-9]+)('.implode('|', $allowed).')?', $m, !$simpleCase)) {
			if (!isset($m[3])) {
				$m[3]='number';
				}
			$unit=array($m[3], $m[1]);

			// check for size/height font unit.. should this even be here?
			if ($simpleCase) {
				$s=$this->seek();
				if ($this->literal('/', FALSE) && $this->unit($right, self::$units)) {
					$unit=array('keyword', $this->compileValue($unit).'/'.$this->compileValue($right));
					}
				else {
					// get rid of whitespace
					$this->seek($s);
					$this->match('', $_);
					}
				}
			return TRUE;
			}
		return FALSE;
	}

	/**
	 * a # color
	 * @param array $out
	 * @return bool
	 */
	private function color(&$out)
	{
		$color=array('color');

		if ($this->match('(#([0-9a-f]{6})|#([0-9a-f]{3}))', $m)) {
			if (isset($m[3])) {
				$num=$m[3];
				$width=16;
				}
			else {
				$num=$m[2];
				$width=256;
				}

			$num=hexdec($num);
			foreach (array(3,2,1) as $i) {
				$t=$num%$width;
				$num/=$width;
				$color[$i]= $t*(256/$width)+$t*floor(16/$width);
				}
			$out=$color;
			return TRUE;
			} 
		return FALSE;
	}

	/**
	 * consume a list of property values delimited by ; and wrapped in ()
	 * @param array &$args
	 * @param string $delim
	 * @return bool
	 */
	private function argumentValues(&$args, $delim=';')
	{
		$s=$this->seek();
		if (!$this->literal('(')) {
			return FALSE;
			}

		$values=array();
		while (TRUE) {
			if ($this->propertyValue($value)) {
				$values[]=$value;
				}
			if (!$this->literal($delim)) {
				break;
				}
			else {
				if ($value==NULL) {
					$values[]=NULL;
					}
				$value=NULL;
				}
			}	

		if (!$this->literal(')')) {
			$this->seek($s);
			return FALSE;
			}
		
		$args=$values;
		return TRUE;
	}

	/**
	 * consume an argument definition list surrounded by (), each argument is a variable name with optional value
	 * @param array &$args
	 * @param string $delim
	 * @return bool
	 */
	private function argumentDef(&$args, $delim=';')
	{
		$s=$this->seek();
		if (!$this->literal('(')) {
			return FALSE;
			}

		$values=array();
		while ($this->variable($vname)) {
			$arg=array($vname);
			if ($this->assign() && $this->propertyValue($value)) {
				$arg[]=$value;
				// let the : slide if there is no value
				}

			$values[]=$arg;
			if (!$this->literal($delim)) {
				break;
				}
			}

		if (!$this->literal(')')) {
			$this->seek($s);
			return FALSE;
			}

		$args=$values;
		return TRUE;
	}

	// 
	/**
	 * consume a list of tags
	 * this accepts a hanging delimiter
	 * @param array &$tags
	 * @param bool $simple
	 * @param string $delim
	 * @return bool
	 */
	private function tags(&$tags, $simple=FALSE, $delim=',')
	{
		$tags=array();
		while ($this->tag($tt, $simple)) {
			$tags[]=$tt;
			if (!$this->literal($delim)) {
				break;
				}
			}
		if (!count($tags)) {
			return FALSE;
			}
		return TRUE;
	}

	/**
	 * a bracketed value (contained within in a tag definition)
	 * @param string &$value
	 * @return bool
	 */
	private function tagBracket(&$value)
	{
		$s=$this->seek();
		if ($this->literal('[') && $this->to(']', $c, TRUE) && $this->literal(']', FALSE)) {
			$value="[$c]";
			// whitespace?
			if ($this->match('', $_)) {
				$value.=$_[0];
				}
			return TRUE;
			}
		$this->seek($s);
		return FALSE;
	}

	/**
	 * a single tag
	 * @param string &$tag
	 * @param bool $simple
	 * @return bool
	 */
	private function tag(&$tag, $simple=FALSE)
	{
		$chars= $simple? '^,:;{}\][>\(\) ' : '^,;{}[';

		$tag='';
		while ($this->tagBracket($first)) {
			$tag.=$first;
			}
		while ($this->match('(['.$chars.'0-9]['.$chars.']*)', $m)) {
			$tag.=$m[1];
			if ($simple) {
				break;
				}
			while ($this->tagBracket($brack)) {
				$tag.=$brack;
				}
			}
		$tag=trim($tag);
		if ($tag=='') {
			return FALSE;
			}
		return TRUE;
	}

	/**
	 * a css function
	 * @param callback? &$func
	 * @return bool
	 */
	private function func(&$func)
	{
		$s=$this->seek();

		if ($this->match('([\w\-_][\w\-_:\.]*)', $m) && $this->literal('(')) {
			$fname=$m[1];
			if ($fname=='url') {
				$this->to(')', $content, TRUE);
				$args=array('string', $content);
				}
			else {
				$args=array();
				while (TRUE) {
					$ss=$this->seek();
					if ($this->keyword($name) && $this->literal('=') && $this->expressionList($value)) {
						$args[]=array('list', '=', array(array('keyword', $name), $value));
						}
					else {
						$this->seek($ss);
						if ($this->expressionList($value)) {
							$args[]=$value;
							}
						}
					if (!$this->literal(',')) {
						break;
						}
					}
				$args=array('list', ',', $args);
				}

			if ($this->literal(')')) {
				$func=array('function', $fname, $args);
				return TRUE;
				}
			}

		$this->seek($s);
		return FALSE;
	}

	/**
	 * consume a less variable
	 * @param string &$name
	 * @return bool
	 */
	private function variable(&$name)
	{
		$s=$this->seek();
		if ($this->literal($this->vPrefix, FALSE) && $this->keyword($name)) {
			return TRUE;	
			}
		return FALSE;
	}

	/**
	 * consume an assignment operator
	 * @return bool
	 */
	private function assign()
	{
		return $this->literal(':') || $this->literal('=');
	}

	/**
	 * consume a keyword
	 * @param string &$word
	 * @return bool
	 */
	private function keyword(&$word)
	{
		if ($this->match('([\w_\-\*!"][\w\-_"]*)', $m)) {
			$word=$m[1];
			return TRUE;
			}
		return FALSE;
	}

	/**
	 * consume an end of statement delimiter
	 * @return bool
	 */
	private function end()
	{
		if ($this->literal(';')) {
			return TRUE;
			}
		elseif ($this->count==strlen($this->buffer) || $this->buffer{$this->count}=='}') {
			// if there is end of file or a closing block next then we don't need a ;
			return TRUE;
			}
		return FALSE;
	}

	/**
	 * @param array $items
	 * @param unknown_type $delim
	 * @return array|
	 */
	private function compressList($items, $delim)
	{
		if (count($items)==1) {
			return $items[0];
			}	
		else {
			return array('list', $delim, $items);
			}
	}

	/**
	 * @param array $rtags
	 * @param array $env
	 * @return string
	 */
	private function compileBlock($rtags, $env)
	{
		// don't render functions
		// todo: this shouldn't need to happen because multiplyTags prunes them, verify
		/*
		foreach ($rtags as $i => $tag) {
			if (preg_match('/( |^)%/', $tag))
				unset($rtags[$i]);
		}
		 */
		if (empty($rtags)) {
			return '';
			}

		$props=0;
		// print all the visible properties
		ob_start();
		foreach ($env as $name => $value) {
			// todo: change this, poor hack
			// make a better name storage system!!! (value types are fine)
			// but.. don't render special properties (blocks, vars, metadata)
			if (isset($value[0]) && $name{0}!=$this->vPrefix && $name!='__args') {
				echo $this->compileProperty($name, $value, 1)."\n";
				$props+=count($value);
				}
			}
		$list=ob_get_clean();
		if (!$props) {
			return '';
			}

		$blockDecl=implode(", ", $rtags).' {';
		if ($props>1) {
			return $this->indent($blockDecl).$list.$this->indent('}');
			}
		else {
			$list=' '.trim($list).' ';
			return $this->indent($blockDecl.$list.'}');
			}
	}

	/**
	 * write a line a the proper indent
	 * @param string $str
	 * @param int $level
	 * @return string
	 */
	private function indent($str, $level=NULL)
	{
		if (is_null($level)) {
			$level=$this->indentLevel;
			}
		return str_repeat('  ', $level)."$str\n";
	}

	/**
	 * @param string $name
	 * @param array $value
	 * @param int $level
	 * @return string
	 */
	private function compileProperty($name, $value, $level=0)
	{
		$level=$this->indentLevel+$level;
		// output all repeated properties
		foreach ($value as $v) {
			$props[]=str_repeat('  ', $level)."$name:".$this->compileValue($v).';';
			}
		return implode("\n", $props);
	}

	/**
	 * @param array $value
	 * @return string
	 */
	private function compileValue($value)
	{
		switch ($value[0]) {
			case 'list':
				// [1] - delimiter
				// [2] - array of values
				return implode($value[1], array_map(array($this, 'compileValue'), $value[2]));
			case 'keyword':
				// [1] - the keyword 
			case 'number':
				// [1] - the number 
				return $value[1];
			case 'expression':
				// [1] - operator
				// [2] - value of left hand side
				// [3] - value of right
				return $this->compileValue($this->evaluate($value[1], $value[2], $value[3]));
			case 'string':
				// [1] - contents of string (includes quotes)
				// search for inline variables to replace
				$replace=array();
				if (preg_match_all('/{('.$this->preg_quote($this->vPrefix).'[\w-_][0-9\w-_]*?)}/', $value[1], $m)) {
					foreach ($m[1] as $name) {
						if (!isset($replace[$name])) {
							$replace[$name]=$this->compileValue(array('variable', $name));
							}
						}
					}
				foreach ($replace as $var => $val) {
					// strip quotes
					if (preg_match('/^(["\']).*?(\1)$/', $val)) {
						$val=substr($val, 1, -1);
						}
					$value[1]=str_replace('{'.$var.'}', $val, $value[1]);
					}
				return $value[1];
			case 'color':
				// [1] - red component (either number for a %)
				// [2] - green component
				// [3] - blue component
				// [4] - optional alpha component
				if (count($value)==5) { // rgba
					return 'rgba('.$value[1].','.$value[2].','.$value[3].','.$value[4].')';
					}
	
				$out='#';
				foreach (range(1,3) as $i) {
					$out.=($value[$i]<16? '0' : '').dechex($value[$i]);
					}
				return $out;
			case 'variable':
				// [1] - the name of the variable including @
				$tmp=$this->compileValue($this->getVal($value[1], $this->pushName($value[1])));
				$this->popName();
				return $tmp;
			case 'negative':
				// [1] - some value that needs to become negative
				return $this->compileValue($this->reduce($value));
			case 'function':
				// [1] - function name
				// [2] - some value representing arguments
	
				// see if there is a library function for this func
				$f=array($this, 'lib_'.$value[1]);
				if (is_callable($f)) {
					return call_user_func($f, $value[2]);
					}
				return $value[1].'('.$this->compileValue($value[2]).')';
			default: // assumed to be unit	
				return $value[1].$value[0];
			}
	}

	/**
	 * @param array $arg
	 * @return string
	 */
	private function lib_quote($arg)
	{
		return '"'.$this->compileValue($arg).'"';
	}

	/**
	 * @param array $arg
	 * @return string
	 */
	private function lib_unquote($arg)
	{
		$out=$this->compileValue($arg);
		if ($this->quoted($out)) {
			$out=substr($out, 1, -1);
			}
		return $out;
	}

	/**
	 * is a string surrounded in quotes? returns the quoting char if true
	 * @param string $s
	 * @return string|boolean
	 */
	private function quoted($s)
	{
		if (preg_match('/^("|\').*?\1$/', $s, $m)) {
			return $m[1];
			}
		return FALSE;
	}

	/**
	 * convert rgb, rgba into color type suitable for math
	 * @param array $func
	 * @return bool
	 * @todo add hsl
	 */
	private function funcToColor($func)
	{
		$fname=$func[1];
		if (!preg_match('/^(rgb|rgba)$/', $fname)) {
			return FALSE;
			}
		if ($func[2][0]!='list') {
			return FALSE; // need a list of arguments
			}

		$components=array();
		$i=1;
		foreach	($func[2][2] as $c) {
			$c=$this->reduce($c);
			if ($i<4) {
				if ($c[0]=='%') {
					$components[]=255*($c[1]/100);
					}
				else {
					$components[]=floatval($c[1]);
					} 
				}
			elseif ($i==4) {
				if ($c[0]=='%') {
					$components[]=1.0*($c[1]/100);
					}
				else {
					$components[]=floatval($c[1]);
					}
				}
			else {
				break;
				}
			$i++;
			}
		while (count($components)<3) {
			$components[]=0;
			}

		array_unshift($components, 'color');
		return $this->fixColor($components);
	}

	/**
	 * reduce a delayed type to its final value
	 * dereference variables and solve equations
	 * @param array $var
	 * @param array $defaultValue
	 * @return array
	 */
	private function reduce($var, $defaultValue=array('number', 0))
	{
		$pushed=0; // number of variable names pushed

		while (in_array($var[0], self::$dtypes)) {
			if ($var[0]=='expression') {
				$var=$this->evaluate($var[1], $var[2], $var[3]);
				}
			else if ($var[0]=='variable') {
				$var=$this->getVal($var[1], $this->pushName($var[1]), $defaultValue);
				$pushed++;
				}
			else if ($var[0]=='function') {
				$color=$this->funcToColor($var);
				if ($color) {
					$var=$color;
					}
				break; // no where to go after a function
				}
			else if ($var[0]=='negative') {
				$value=$this->reduce($var[1]);
				if (is_numeric($value[1])) {
					$value[1]=-1*$value[1];
					}
				$var=$value;
				}
			}

		while ($pushed!=0) {
			$this->popName();
			$pushed--;
			}
		return $var;
	}

	/**
	 * evaluate an expression
	 * @param string $op
	 * @param array $left
	 * @param array $right
	 * @return array
	 */
	private function evaluate($op, $left, $right)
	{
		$left=$this->reduce($left);
		$right=$this->reduce($right);

		if ($left[0]=='color' && $right[0]=='color') {
			$out=$this->op_color_color($op, $left, $right);
			return $out;
			}
		if ($left[0]=='color') {
			return $this->op_color_number($op, $left, $right);
			}
		if ($right[0]=='color') {
			return $this->op_number_color($op, $left, $right);
			}

		// concatenate strings
		if ($op=='+' && $left[0]=='string') {
			$append=$this->compileValue($right);
			if ($this->quoted($append)) {
				$append=substr($append, 1, -1);
				}

			$lhs=$this->compileValue($left);
			if ($q=$this->quoted($lhs)) {
				$lhs=substr($lhs, 1, -1);
				}
			if (!$q) {
				$q='';
				}
			return array('string', $q.$lhs.$append.$q);
			}

		if ($left[0]=='keyword'
			|| $right[0]=='keyword'
			|| $left[0]=='string'
			|| $right[0]=='string'
			) {
			// look for negative op
			if ($op=='-') {
				$right[1]='-'.$right[1];
				}
			return array('keyword', $this->compileValue($left).' '.$this->compileValue($right));
			}
	
		// default to number operation
		return $this->op_number_number($op, $left, $right);
	}

	/**
	 * make sure a color's components don't go out of bounds
	 * @param array $c
	 * @return array
	 */
	private function fixColor($c)
	{
		foreach (range(1, 3) as $i) {
			if ($c[$i]<0) {
				$c[$i]=0;
				}
			if ($c[$i]>255) {
				$c[$i]=255;
				}
			$c[$i]=floor($c[$i]);
			}
		return $c;
	}

	/**
	 * @param string $op
	 * @param array $lft
	 * @param array $rgt
	 * @return array
	 */
	private function op_number_color($op, $lft, $rgt)
	{
		if ($op=='+' || $op='*') {
			return $this->op_color_number($op, $rgt, $lft);
			}
	}

	/**
	 * @param string $op
	 * @param array $lft
	 * @param array $rgt
	 * @return array
	 */
	private function op_color_number($op, $lft, $rgt)
	{
		if ($rgt[0]=='%') {
			$rgt[1]/=100;
			}
		return $this->op_color_color($op, $lft, array_fill(1, count($lft)-1, $rgt[1]));
	}

	/**
	 * @param string $op
	 * @param array $left
	 * @param array $right
	 * @return array
	 */
	private function op_color_color($op, $left, $right)
	{
		$out=array('color');
		$max= count($left)>count($right)? count($left) : count($right);
		foreach (range(1, $max-1) as $i) {
			$lval= isset($left[$i])? $left[$i] : 0;
			$rval= isset($right[$i])? $right[$i] : 0;
			switch ($op) {
				case '+':
					$out[]=$lval+$rval;
					break;
				case '-':
					$out[]=$lval-$rval;
					break;
				case '*':
					$out[]=$lval*$rval;
					break;
				case '%':
					$out[]=$lval%$rval;
					break;
				case '/':
					if ($rval==0) {
						throw new \Exception("evaluate error: can't divide by zero");
						}
					$out[]=$lval/$rval;
					break;
				default:
					throw new \Exception("evaluate error: color op number failed on op $op");
				}
			}
		return $this->fixColor($out);
	}

	/**
	 * operator on two numbers
	 * @param string $op
	 * @param array $left
	 * @param array $right
	 * @return array
	 */
	private function op_number_number($op, $left, $right)
	{
		if ($right[0]=='%') {
			$right[1]/=100;
			}

		// figure out type
		if ($right[0]=='number' || $right[0]=='%') {
			$type=$left[0];
			}
		else {
			$type=$right[0];
			}

		$value=0;
		switch($op) {
			case '+':
				$value=$left[1]+$right[1];
				break;	
			case '*':
				$value=$left[1]*$right[1];
				break;	
			case '-':
				$value=$left[1]-$right[1];
				break;	
			case '%':
				$value=$left[1]%$right[1];
				break;	
			case '/':
				if ($right[1]==0) {
					throw new \Exception('parse error: divide by zero');
					}
				$value=$left[1]/$right[1];
				break;
			default:
				throw new \Exception('parse error: unknown number operator: '.$op);	
			}
		return array($type, $value);
	}


	/* environment functions */

	/**
	 * push name on expand stack, and return its
	 * count before being pushed
	 * @param string? $name
	 * @return int
	 */
	private function pushName($name)
	{
		$count=array_count_values($this->expandStack);
		$count= isset($count[$name])? $count[$name] : 0;

		$this->expandStack[]=$name;

		return $count;
	}

	/**
	 * pop name off expand stack and return it
	 * @return mixed
	 */
	private function popName()
	{
		return array_pop($this->expandStack);
	}

	/**
	 * push a new environment
	 */
	private function push()
	{
		$this->level++;
		$this->env[]=array();
	}

	/**
	 * pop environment off the stack
	 * @return mixed
	 */
	private function pop()
	{
		if ($this->level==1) {
			throw new \Exception('parse error: unexpected end of block');
			}
		$this->level--;
		return array_pop($this->env);
	}

	/**
	 * set something in the current env
	 * @param string $name
	 * @param mixed $value
	 */
	private function set($name, $value)
	{
		$this->env[count($this->env)-1][$name]=$value;
	}

	/**
	 * append to array in the current env
	 * @param string $name
	 * @param mixed $value
	 */
	private function append($name, $value)
	{
		$this->env[count($this->env)-1][$name][]=$value;
	}

	/**
	 * put on the front of the value
	 * @param string $name
	 * @param mixed $value
	 */
	private function prepend($name, $value)
	{
		if (isset($this->env[count($this->env)-1][$name])) {
			array_unshift($this->env[count($this->env)-1][$name], $value);
			}
		else {
			$this->append($name, $value);
			}
	}

	/**
	 * get the highest occurrence of value
	 * @param string $name
	 * @param array $env
	 * @return mixed
	 */
	private function get($name, $env=NULL)
	{
		if (empty($env)) {
			$env=$this->env;
			}
		for ($i=count($env)-1; $i>=0; $i--) {
			if (isset($env[$i][$name])) {
				return $env[$i][$name];
				}
			}

		return NULL;
	}

	/**
	 * get the most recent value of a variable, return default if it isn't found
	 * @param string $name
	 * @param int $skip number of vars to skip
	 * @param array $default
	 * @return mixed
	 */
	private function getVal($name, $skip=0, $default=array('keyword', ''))
	{
		$val=$this->get($name);
		if ($val==NULL) {
			return $default;
			}

		$tmp=$this->env;
		while (!isset($tmp[count($tmp)-1][$name])) {
			array_pop($tmp);
			}
		while ($skip>0) {
			$skip--;
			if (!empty($val)) {
				array_pop($val);
				}
			if (empty($val)) {
				array_pop($tmp);
				$val=$this->get($name, $tmp);
				}
			if (empty($val)) {
				return $default;
				}
			}
		return end($val);
	}

	/**
	 * get the environment described by path, an array of env names
	 * @param array $path
	 * @return array
	 */
	private function getEnv($path)
	{
		if (!is_array($path)) {
			$path=array($path);
			}

		//  move @ tags out of variable namespace
		foreach ($path as &$tag) {
			if ($tag{0}==$this->vPrefix) {
				$tag[0]=$this->mPrefix;
				}
			}

		$env=$this->get(array_shift($path));
		while ($sub=array_shift($path)) {
			if (isset($env[$sub])) { // todo add a type check for environment
				$env=$env[$sub];
				}
			else {
				$env=NULL;
				break;
				}
			}
		return $env;
	}

	/**
	 * merge a block into the current env
	 * @param string $name
	 * @param mixed $value
	 * @return mixed|void
	 */
	private function merge($name, $value)
	{
		// if the current block isn't there then just set
		$top= &$this->env[count($this->env)-1];
		if (!isset($top[$name])) {
			return $this->set($name, $value);
			}

		// copy the block into the old one, including meta data
		foreach ($value as $k => $v) {
			// todo: merge property values instead of replacing
			// have to check type for this
			$top[$name][$k]=$v;
			}
	}

	/**
	 * @param string $what
	 * @param bool $eatWhitespace
	 * @return bool
	 */
	private function literal($what, $eatWhitespace=TRUE)
	{
		// this is here mainly prevent notice from { } string accessor 
		if ($this->count >= strlen($this->buffer)) {
			return FALSE;
			}
		// shortcut on single letter
		if (!$eatWhitespace and strlen($what)==1) {
			if ($this->buffer{$this->count}==$what) {
				$this->count++;
				return TRUE;
				}
			else {
				return FALSE;
				}
			}
		return $this->match($this->preg_quote($what), $m, $eatWhitespace);
	}

	/**
	 * @param string $what
	 * @return string
	 */
	private function preg_quote($what)
	{
		return preg_quote($what, '/');
	}

	/**
	 * advance counter to next occurrence of $what
	 * @param string $what
	 * @param string &$out
	 * @param bool $until don't include $what in advance
	 * @param bool $allowNewline
	 * @return bool
	 */
	private function to($what, &$out, $until=FALSE, $allowNewline=FALSE)
	{
		$validChars= $allowNewline? "[^\n]" : '.';
		if (!$this->match('('.$validChars.'*?)'.$this->preg_quote($what), $m, !$until)) {
			return FALSE;
			}
		if ($until) {
			$this->count-=strlen($what); // give back $what
			}
		$out=$m[1];
		return TRUE;
	}
	
	/**
	 * try to match something on head of buffer
	 * @param string $regex
	 * @param array $out
	 * @param bool $eatWhitespace
	 * @return bool
	 */
	private function match($regex, &$out, $eatWhitespace=TRUE)
	{
		$r="/$regex".($eatWhitespace? '\s*' : '').'/Ais';
		if (preg_match($r, $this->buffer, $out, NULL, $this->count)) {
			$this->count+=strlen($out[0]);
			return TRUE;
			}
		return FALSE;
	}


	/**
	 * match something without consuming it
	 * @param string $regex
	 * @param array &$out
	 * @return int
	 */
	private function peek($regex, &$out=NULL)
	{
		$r="/$regex/Ais";
		return preg_match($r, $this->buffer, $out, NULL, $this->count);
	}

	/**
	 * seek to a spot in the buffer or return where we are on no argument
	 * @param unknown_type $where
	 * @return int|TRUE
	 */
	private function seek($where=NULL)
	{
		if ($where===NULL) {
			return $this->count;
			}
		$this->count=$where;
		return TRUE;
	}

	/**
	 * parse and compile buffer
	 * @param string $str
	 * @return string
	 */
	private function parse($str=NULL)
	{
		if ($str) {
			$this->buffer=$str;
			}
		$this->env=array();
		$this->expandStack=array();
		$this->indentLevel=0;
		$this->media=NULL;
		$this->count=0;
		$this->line=1;
		$this->level=0;

		$this->buffer=$this->removeComments($this->buffer);
		$this->push(); // set up global scope
		$this->set('__tags', array('')); // equivalent to 1 in tag multiplication

		// trim whitespace on head
		if (preg_match('/^\s+/', $this->buffer, $m)) {
			$this->line+=substr_count($m[0], "\n");
			$this->buffer=ltrim($this->buffer);
			}

		$out='';
		while (FALSE!==($compiled=$this->chunk())) {
			if (is_string($compiled)) {
				$out.=$compiled;
				}
			}
		if ($this->count!=strlen($this->buffer)) {
			$this->throwParseError();
			}
		if (count($this->env)>1) {
			throw new \Exception('parse error: unclosed block');
			}

		return $out;
	}

	/**
	 * @param string $msg
	 * @throws \Exception
	 */
	private function throwParseError($msg='parse error')
	{
		$line=$this->line+substr_count(substr($this->buffer, 0, $this->count), "\n");
		if ($this->peek("(.*?)(\n|$)", $m)) {
			throw new \Exception("$msg: failed at `".$m[1]."` line: $line");
			}
	}

	/**
	 * remove comments from $text
	 * @param string $text
	 * @return string
	 * @todo make it work for all functions, not just url
	 */
	private function removeComments($text)
	{
		$look=array('url(', '//', '/*', '"', "'");

		$out='';
		$min=NULL;
		$done=FALSE;
		while (TRUE) {
			// find the next item
			foreach ($look as $token) {
				$pos=strpos($text, $token);
				if ($pos!==FALSE) {
					if (!isset($min) || $pos<$min[1]) {
						$min=array($token, $pos);
						}
					}
				}

			if (is_null($min)) {
				break;
				}

			$count=$min[1];
			$skip=0;
			$newlines=0;
			switch ($min[0]) {
				case 'url(':
					if (preg_match('/url\(.*?\)/', $text, $m, 0, $count)) {
						$count+=strlen($m[0])-strlen($min[0]);
						}
					break;
				case '"':
				case "'":
					if (preg_match('/'.$min[0].'.*?'.$min[0].'/', $text, $m, 0, $count)) {
						$count+=strlen($m[0])-1;
						}
					break;
				case '//':
					$skip=strpos($text, "\n", $count)-$count;
					break;
				case '/*': 
					if (preg_match('/\/\*.*?\*\//s', $text, $m, 0, $count)) {
						$skip=strlen($m[0]);
						$newlines=substr_count($m[0], "\n");
						}
					break;
				}

			if (!$skip) {
				$count+=strlen($min[0]);
				}

			$out.=substr($text, 0, $count).str_repeat("\n", $newlines);
			$text=substr($text, $count+$skip);
			$min=NULL;
			}
		return $out.$text;
	}

	/**
	 * @return array
	 */
	public function allParsedFiles()
	{
		return $this->allParsedFiles;
	}

	/**
	 * @param string $file
	 */
	protected function addParsedFile($file)
	{
		$this->allParsedFiles[realpath($file)]=filemtime($file);
	}

	/**
	 * Invoke filter
	 * @param string code
	 * @param WebLoader loader
	 * @param string file
	 * @return string
	 */
	public static function __invoke($code, WebLoader $loader, $file=NULL)
	{
		if ($file===NULL || substr($file, -5)!='.less')
			return $code;
		$filter=new self($loader->getSourcePath()."/$file");
		return $filter->parse($code);
	}
}
