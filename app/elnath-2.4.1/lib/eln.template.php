<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.template.php
 */


/*
 * テンプレートコンバータクラス
 */
class Eln_Template {
  /*
   * プロパティ
   */
  private $htmlFile;
  private $phpFile;
  private $tagStack;
  private $localIndex;
  private $singleTags;
  /**/
  private $output;

  /*
   * 定数
   */
  const TAG_NAME = 0;
  const TAG_LINE = 1;
  const ATTR_NOTAG = 2;
  const ATTR_FOREACH = 3;
  const ATTR_IF = 4;
  const ATTR_UNLESS = 5;
  const ATTR_CONTENT = 6;
  const ATTR_BLOCK = 7;
  const FILE_OFFSET = 8;
  /**/
  const SEGMENT_STRING = 1;
  const SEGMENT_ESCAPED = 2;
  const SEGMENT_RAW = 3;

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  public function __construct($htmlFile, $phpFile){
    $this->htmlFile = $htmlFile;
    $this->phpFile = $phpFile;
    $this->tagStack = array();
    $this->localIndex = 1;
    $this->singleTags = array(
			      'meta', 'link',
			      'hr', 'br', 'img', 'input',
			      'embed', 'area', 'base', 'col', 'keygen', 'param', 'source'
			      );
  }

  /*
   * 変換
   */
  public function convert(){
    /* 入力HTMLソースファイル */
    if(($in = fopen($this->htmlFile, 'r')) === false){
      throw new Eln_Exception(_('failed to open template file "{$1}".'), $this->htmlFile);
    }

    /* 出力PHPキャッシュファイル */
    if(($dir = opendir(dirname($this->phpFile))) !== false){
      $dirname = dirname($this->phpFile);
      $basename = basename($this->phpFile, '.php');
      while(($file = readdir($dir)) !== false){
	if(preg_match('/^'.str_replace('^', '\^', $basename).'(?:~.+)?\.php$/', $file)){
	  unlink($dirname.DIRECTORY_SEPARATOR.$file);
	}
      }
    }
    if(($this->output = fopen($this->phpFile, 'w')) === false){
      fclose($in);
      throw new Eln_Exception(_('failed to open PHP cache file "{$1}".'), $this->phpFile);
    }

    /* 変換 */
    try{
      $lineno = $this->parse($in);
      while(($last = array_pop($this->tagStack)) !== null){
	if(in_array($last[0], $this->singleTags) === false){
	  $this->closeTag($lineno, $last);
	  fwrite($this->output, PHP_EOL);
	}
      }
    }catch(Exception $e){
      fclose($in);
      fclose($this->output);
      if(file_exists($this->phpFile)){
	unlink($this->phpFile);
      }
      throw $e;
    }

    /**/
    fclose($in);
    fclose($this->output);
  }

  /* ===== ===== */

  /*
   * HTML解析
   */
  private function parse($in){
    /**/
    $startLine = $currLine = 1;
    $buffer = '';
    while(true){
      /* タグ開始位置を検索 */
      while(($found = mb_strpos($buffer, '<')) === false){
	if(($line = fgets($in)) === false){
	  if(feof($in)){
	    if(strlen($buffer)){
	      $this->handleText($startLine, $buffer);
	    }
	    return $currLine;
	  }else{
	    throw new Eln_Exception(_('failed to read template file "{$1}".'), $this->htmlFile);
	  }
	}
	++ $currLine;
	$buffer .= $line;
      }
      if($found > 0){
	$this->handleText($startLine, mb_substr($buffer, 0, $found));
      }

      /* タグ終了位置を検索 */
      $startLine = $currLine;
      $buffer = mb_substr($buffer, $found);
      if(strcmp(substr($buffer, 0, 9), '<![CDATA[') == 0){
	$delimiter = ']]>';
      }else if(strcmp(substr($buffer, 0, 8), '<![endif') == 0){
	$delimiter = ']-->';
      }else if(strcmp(substr($buffer, 0, 7), '<!--[if') == 0){
	$delimiter = ']>';
      }else if(strcmp(substr($buffer, 0, 4), '<!--') == 0){
	$delimiter = '-->';
      }else{
	$delimiter = '>';
      }
      while(($found = mb_strpos($buffer, $delimiter)) === false){
	if(($line = fgets($in)) === false){
	  if(feof($in)){
	    throw new Eln_Exception(
				    _('{$1}: the tag "{$2}" started at line {$3} is not closed.'),
				    $this->htmlFile,
				    substr($buffer, 0, 7),
				    $startLine
				    );
	  }else{
	    throw new Eln_Exception(_('failed to read template file "{$1}".'), $this->htmlFile);
	  }
	}
	++ $currLine;
	$buffer .= $line;
      }

      /* タグを変換 */
      $this->handleTag($startLine, mb_substr($buffer, 0, $found + strlen($delimiter)));
      $startLine = $currLine;
      $buffer = mb_substr($buffer, $found + strlen($delimiter));
    }

    /**/
    return $currLine;
  }

  /*
   * タグ
   */
  private function handleTag($lineno, $str){
    if(strcmp(substr($str, 0, 2), '<!') == 0){
      /* 特殊タグ */
      fwrite($this->output, $this->compileStatement($lineno, $str));
    }else if(strcmp(substr($str, 0, 2), '</') == 0){
      /* 終了タグ */
      $tag = strtolower(trim(substr($str, 2, -1)));
      if(in_array($tag, $this->singleTags) === false){
	if(!($last = array_pop($this->tagStack)) || strcmp($tag, $last[0]) != 0){
	  throw new Eln_Exception(
				  _('{$1}: unbalanced end tag "{$2}" expected "{$3}" at line {$4}.'),
				  $this->htmlFile,
				  $tag,
				  $last[0],
				  $lineno
				  );
	}
	$this->closeTag($lineno, $last);
      }
    }else{
      /* 開始タグ/単一タグ */
      list($name, $single, $attrs) = $this->parseTag($lineno, $str);
      /* (タグ名, 行番号, notag, foreach, if, unless, content, <offset>) */
      $tag = array($name, $lineno, null, null, null, null, null, null, -1);
      $evaluate = true;
      $checked = false;
      $options = null;
      $default = null;
      $format = null;
      $elnAttributes = array();
      $attributes = array();
      foreach($attrs as $name=>$value){
	if(strcmp($name, 'eln:notag') == 0){
	  $tag[self::ATTR_NOTAG] = $value;
	}else if(strcmp($name, 'eln:foreach') == 0){
	  $tag[self::ATTR_FOREACH] = $value;
	}else if(strcmp($name, 'eln:if') == 0){
	  $tag[self::ATTR_IF] = $value;
	}else if(strcmp($name, 'eln:unless') == 0){
	  $tag[self::ATTR_UNLESS] = $value;
	}else if(strcmp($name, 'eln:content') == 0){
	  $tag[self::ATTR_CONTENT] = $this->compileStatement($lineno, $value);
	}else if(strcmp($name, 'eln:include') == 0){
	  $expression = $this->compileExpression($lineno, $value);
	  $tag[self::ATTR_CONTENT] = '<?php $this->render('.$expression.'); ?>';
	  $tag[self::ATTR_NOTAG] = 'true';
	}else if(strcmp($name, 'eln:block') == 0){
	  $tag[self::ATTR_BLOCK] = $value;
	}else if(strcmp($name, 'eln:noeval') == 0){
	  $evaluate = false;
	}else if(strcmp($name, 'eln:options') == 0){
	  $options = $value;
	}else if(strcmp($name, 'eln:default') == 0){
	  $default = $value;
	}else if(strcmp($name, 'eln:format') == 0){
	  $format = $value;
	}else if(preg_match('/^eln:(.+)$/', $name, $matches)){
	  $elnAttributes[$matches[1]] = $value;
	}else if($value !== null){
	  $attributes[$name] = $value;
	}else{
	  $attributes[$name] = $name;
	}
      }
      foreach($elnAttributes as $name=>$value){
	$attributes[$name] = $value;
      }
      if(strcmp($tag[self::TAG_NAME], 'eln:tag') == 0){
	$tag[self::ATTR_NOTAG] = 'true';
      }
      /**/
      if(strcmp($tag[self::TAG_NAME], 'input') == 0){
	if(isset($attributes['type']) && in_array($attributes['type'], array('checkbox', 'radio'))){
	  /* <input type="checkbox"> | <input type="radio"> */
	  if(isset($attributes['name']) && isset($attributes['value']) && $evaluate){
	    $name = $this->compileExpression($lineno, $attributes['name']);
	    if(isset($attributes['checked'])){
	      unset($attributes['checked']);
	    }
	    $value = $this->compileExpression($lineno, $attributes['value']);
	    if(empty($value)){
	      $value = '\'\'';
	    }
	    $attributes['-']  = '<?php if($this->tIsChecked('.$name.', '.$value.')){ ?>';
	    $attributes['-'] .= 'checked="checked"';
	    $attributes['-'] .= '<?php } ?>';
	  }
	}else if(isset($attributes['type']) && (
						strcasecmp($attributes['type'], 'text') == 0 ||
						strcasecmp($attributes['type'], 'password') == 0 ||
						strcasecmp($attributes['type'], 'hidden') == 0
						)){
	  /* <input type="text"> | <input type="hidden"> */
	  if(isset($attributes['name']) && $evaluate){
	    $name = $this->compileExpression($lineno, $attributes['name']);
	    if(isset($attributes['value'])){
	      unset($attributes['value']);
	    }
	    if($format === null){
	      $out = 'echo $this->tAsString($this->tEvaluate('.$name.'), \'%Y/%m/%d\');';
	    }else{
	      $out = 'echo $this->tAsString($this->tEvaluate('.$name.'), \''.$format.'\');';
	    }
	    $attributes['-'] = 'value="<?php '.$out.'?>"';
	  }
	}else{
	  /* <input /> */
	}
      }else if(strcmp($tag[self::TAG_NAME], 'textarea') == 0){
	/* <textarea></textarea> */
	if(isset($attributes['name']) && $evaluate){
	  $name = $this->compileExpression($lineno, $attributes['name']);
	  if($format === null){
	    $out = 'echo $this->tAsString($this->tEvaluate('.$name.'));';
	  }else{
	    $out = 'echo $this->tAsString($this->tEvaluate('.$name.'), \''.$format.'\');';
	  }
	  $tag[self::ATTR_CONTENT] = '<?php '.$out.'?>';
	}
      }else if(strcmp($tag[self::TAG_NAME], 'select') == 0){
	/* <select></select> */
	if($options !== null){
	  $multiple = isset($attributes['multiple']);
	  if(isset($attributes['name'])){
	    $name = $this->compileExpression($lineno, $attributes['name']);
	  }else{
	    $name = null;
	    $evaluate = false;
	  }
	  $tag[self::ATTR_CONTENT] = $this->compileOptions($lineno, $options, $name, $default, $multiple, $evaluate);
	}
      }
      /**/
      if($tag[self::ATTR_BLOCK] !== null){
	$filename = preg_replace('/\.php$/', '~'.$tag[self::ATTR_BLOCK].'.php', $this->phpFile);
	$tag[self::ATTR_BLOCK] = $this->output;
	if(($this->output = fopen($filename, 'w')) === false){
	  $this->output = $tag[self::ATTR_BLOCK];
	  throw new Eln_Exception(_('failed to open PHP cache file "{$1}".'), $filename);
	}
      }
      if($tag[self::ATTR_FOREACH]){
	fwrite($this->output, $this->compileForeach($lineno, $tag[self::ATTR_FOREACH]));
	$tag[self::ATTR_FOREACH] = true;
      }
      if($tag[self::ATTR_IF]){
	fwrite($this->output, $this->compileIf($lineno, $tag[self::ATTR_IF]));
	$tag[self::ATTR_IF] = true;
      }
      if($tag[self::ATTR_UNLESS]){
	fwrite($this->output, $this->compileUnless($lineno, $tag[self::ATTR_UNLESS]));
	$tag[self::ATTR_UNLESS] = true;
      }
      if($tag[self::ATTR_NOTAG]){
	fwrite($this->output, $this->compileUnless($lineno, $tag[self::ATTR_NOTAG]));
      }
      $statement = '<'.$tag[self::TAG_NAME];
      foreach($attributes as $name=>$value){
	if(strcmp($name, '-') == 0){
	  $statement .= ' '.$value;
	}else{
	  $statement .= ' '.$name.'="'.$this->compileStatement($lineno, $value).'"';
	}
      }
      $statement .= '>';
      fwrite($this->output, $statement);
      if($tag[self::ATTR_NOTAG]){
	fwrite($this->output, '<?php'.PHP_EOL.'}'.PHP_EOL.'?>');
      }
      if($tag[self::ATTR_CONTENT] !== null){
	$tag[self::FILE_OFFSET] = ftell($this->output);
      }
      if($single || in_array($tag[self::TAG_NAME], $this->singleTags)){
	$tag[self::TAG_NAME] = null;	// タグは非表示。閉じ処理のみ。
	$this->closeTag($lineno, $tag);
      }else{
	$this->tagStack[] = $tag;
      }
    }
  }

  /*
   * タグを閉じる
   */
  private function closeTag($lineno, $tag){
    if($tag[self::ATTR_CONTENT] !== null){
      ftruncate($this->output, $tag[self::FILE_OFFSET]);
      fseek($this->output, $tag[self::FILE_OFFSET], SEEK_SET);
      fwrite($this->output, $tag[self::ATTR_CONTENT]);
    }
    if($tag[self::ATTR_NOTAG]){
      fwrite($this->output, $this->compileUnless($lineno, $tag[self::ATTR_NOTAG]));
    }
    if($tag[self::TAG_NAME] !== null){
      fwrite($this->output, '</'.$tag[self::TAG_NAME].'>');
    }
    if($tag[self::ATTR_NOTAG]){
      fwrite($this->output, '<?php'.PHP_EOL.'}'.PHP_EOL.'?>');
    }
    if($tag[self::ATTR_UNLESS]){
      fwrite($this->output, '<?php'.PHP_EOL.'}'.PHP_EOL.'?>');
    }
    if($tag[self::ATTR_IF]){
      fwrite($this->output, '<?php'.PHP_EOL.'}'.PHP_EOL.'?>');
    }
    if($tag[self::ATTR_FOREACH]){
      fwrite($this->output, '<?php'.PHP_EOL.'}'.PHP_EOL.'?>');
    }
    if($tag[self::ATTR_BLOCK] !== null){
      fclose($this->output);
      $this->output = $tag[self::ATTR_BLOCK];
    }
  }

  /*
   * タグ解析
   */
  private function parseTag($lineno, $str){
    /**/
    $stmt = $str;
    $tag = null;
    $single = false;

    /* タグ名 */
    if(preg_match('/^<([-_0-9a-zA-Z:]+)\s*(.*)\/\s*>$/', $str, $matches)){
      /* <tag ..... /> */
      $tag = $matches[1];
      $str = $matches[2];
      $single = true;
    }else if(preg_match('/^<([-_0-9a-zA-Z:]+)\s*(.*)>$/', $str, $matches)){
      /* <tag .....> */
      $tag = $matches[1];
      $str = $matches[2];
    }else{
      throw new Eln_Exception(
			      _('{$1}: invalid tag syntax "{$2}" at line {$3}.'),
			      $this->htmlFile,
			      $stmt,
			      $lineno
			      );
    }

    /* タグ属性 */
    $attributes = array();
    while(strlen($str) > 0){
      if(preg_match('/^([-_0-9a-zA-Z:]+)\s*/', $str, $matches)){
	$name = strtolower($matches[1]);
	$str = substr($str, strlen($matches[0]));
	if(strcmp(substr($str, 0, 1), '=') == 0){
	  $str = substr($str, 1);
	  if(preg_match('/^"((?:\\\\.|[^"])*)"\s*/', $str, $matches)){
	    /* 二重引用符 */
	    $attributes[$name] = $matches[1];
	    $str = substr($str, strlen($matches[0]));
	  }else if(preg_match('/^\'((?:\\\\.|[^\'])*)\'\s*/', $str, $matches)){
	    /* 引用符 */
	    $attributes[$name] = $matches[1];
	    $str = substr($str, strlen($matches[0]));
	  }else if(preg_match('/^(\S*)\s*/', $str, $matches)){
	    /* 文字列 */
	    $attributes[$name] = $matches[1];
	    $str = substr($str, strlen($matches[0]));
	  }
	}else{
	  /* 属性名のみ属性値なし */
	  $attributes[$name] = null;
	}
      }else{
	throw new Eln_Exception(
				_('{$1}: invalid tag syntax "{$2}" at line {$3}.'),
				$this->htmlFile,
				$stmt,
				$lineno
				);
      }
    }
    return array($tag, $single, $attributes);
  }

  /*
   * テキスト
   */
  private function handleText($lineno, $str){
    fwrite($this->output, $this->compileStatement($lineno, $str));
  }

  /*
   * テキスト解析
   */
  private function parseText($lineno, $str){
    $segments = array();
    $offset = 0;
    while(strlen($str) > 0){
      if(($left = mb_strpos($str, '{', $offset)) === false){
	$segments[] = array($str, self::SEGMENT_STRING);
	break;
      }
      $c = mb_substr($str, $left + 1, 1);
      if(strpos("\r\n\t ", $c) === false){
	if($left > 0){
	  $segments[] = array(mb_substr($str, 0, $left), self::SEGMENT_STRING);
	}
	$pos = $left + 1;
	while(true){
	  if(($right = mb_strpos($str, '}', $pos)) === false){
	    throw new Eln_Exception(
				    _('{$1}: unbalanced parentheses "{$2}" at line {$3}.'),
				    $this->htmlFile,
				    $str,
				    $lineno
				    );
	  }
	  if(($inner = mb_strpos(mb_substr($str, $pos, $right - $pos), '{')) === false){
	    break;
	  }
	  $pos = $right + 1;
	}
	if($right - $left > 5 && strcasecmp(mb_substr($str, $right - 4, 4), '|raw') == 0){
	  $segments[] = array(mb_substr($str, $left + 1, ($right - 4) - ($left + 1)), self::SEGMENT_RAW);
	}else{
	  $segments[] = array(mb_substr($str, $left + 1, $right - ($left + 1)), self::SEGMENT_ESCAPED);
	}
	$str = mb_substr($str, $right + 1);
	$offset = 0;
      }else{
	$offset = $left + 1;
      }
    }
    return $segments;
  }

  /*
   * コンパイル
   */
  private function compileStatement($lineno, $str){
    $segments = $this->parseText($lineno, $str);
    $result = '';
    foreach($segments as $segment){
      if($segment[1] == self::SEGMENT_ESCAPED){
	$result .= '<?php echo $this->tAsString('.$this->element($lineno, $segment[0]).'); ?>';
      }else if($segment[1] == self::SEGMENT_RAW){
	$result .= '<?php echo $this->tAsHTML($this->tAsString(';
	$result .= $this->element($lineno, $segment[0]);
	$result .= ')); ?>';
      }else{
	$result .= $segment[0];
      }
    }
    return $result;
  }
  private function compileExpression($lineno, $str, $quotation='\''){
    $segments = $this->parseText($lineno, $str);
    $result = array();
    foreach($segments as $segment){
      if($segment[1] == self::SEGMENT_ESCAPED){
	$result[] = '$this->tAsString('.$this->element($lineno, $segment[0]).')';
      }else if($segment[1] == self::SEGMENT_RAW){
	$result[] = '$this->tAsHTML($this->tAsString('.$this->element($lineno, $segment[0]).'))';
      }else{
	$result[] = $quotation.$segment[0].$quotation;
      }
    }
    if(empty($result)){
      return $quotation.$quotation;
    }
    return implode('.', $result);
  }
  private function compileForeach($lineno, $str){
    if(!preg_match('/^\$([_0-9a-zA-Z]+)\s*:\s*\{(.+)\}$/', trim($str), $matches)){
      throw new Eln_Exception(
			      _('{$1}: invalid foreach syntax "{$2}" at line {$3}.'),
			      $this->htmlFile,
			      $str,
			      $lineno
			      );
    }
    $result = <<<EOS
<?php
\$__elnArray{$this->localIndex} = \$this->tAsArray({$this->element($lineno, $matches[2])});
\$__elnIndex{$this->localIndex} = 0;
\$this->tAssign('_.{$matches[1]}.count', count(\$__elnArray{$this->localIndex}));
foreach(\$__elnArray{$this->localIndex} as \$__elnKey{$this->localIndex}=>\$__elnValue{$this->localIndex}){
  \$this->tAssign('_.{$matches[1]}.index', \$__elnIndex{$this->localIndex});
  \$this->tAssign('_.{$matches[1]}.key', \$__elnKey{$this->localIndex});
  \$this->tAssign('{$matches[1]}', \$__elnValue{$this->localIndex});
  ++ \$__elnIndex{$this->localIndex};
?>
EOS;
    ++ $this->localIndex;
    return trim($result);
  }
  private function compileIf($lineno, $str){
    if(preg_match('/^\{(.+)\}$/', trim($str), $matches)){
      return '<?php'.PHP_EOL.'if('.$this->element($lineno, $matches[1]).'){'.PHP_EOL.'?>';
    }
    return '<?php'.PHP_EOL.'if('.$this->element($lineno, $str).'){'.PHP_EOL.'?>';
  }
  private function compileUnless($lineno, $str){
    if(preg_match('/^\{(.+)\}$/', trim($str), $matches)){
      return '<?php'.PHP_EOL.'if(!('.$this->element($lineno, $matches[1]).')){'.PHP_EOL.'?>';
    }
    return '<?php'.PHP_EOL.'if(!('.$this->element($lineno, $str).')){'.PHP_EOL.'?>';
  }
  private function compileOptions($lineno, $str, $name, $default, $multiple, $evaluate){
    if(preg_match('/^\{(.+)\}$/', trim($str), $matches)){
      $options = $this->element($lineno, $matches[1]);
    }else{
      $options = $this->element($lineno, $str);
    }
    if($default !== null){
      $default = $this->compileExpression($lineno, $default);
    }
    $result = <<<EOS
<?php
\$__elnOptions{$this->localIndex} = array();
\$__elnSelected{$this->localIndex} = array();
\$__elnIndex{$this->localIndex} = 0;
\$__elnDefault{$this->localIndex} = -1;
EOS;
    if($default !== null){
      $result .= <<<EOS
\$__elnOptions{$this->localIndex}[] = array('value'=>'', 'label'=>\$this->tAsString({$default}));
\$__elnDefault{$this->localIndex} = \$__elnIndex{$this->localIndex};
++ \$__elnIndex{$this->localIndex};
EOS;
    }
    $result .= <<<EOS
foreach(\$this->tAsOptions({$options}) as \$__elnItem{$this->localIndex}){
  if(isset(\$__elnItem{$this->localIndex}['selected'])){
    \$__elnDefault{$this->localIndex} = \$__elnIndex{$this->localIndex};
  }
EOS;
    if($evaluate){
      $result .= <<<EOS
  if(isset(\$__elnItem{$this->localIndex}['value'])){
    if(\$this->tIsChecked({$name}, \$__elnItem{$this->localIndex}['value'])){
      \$__elnSelected{$this->localIndex}[] = \$__elnIndex{$this->localIndex};
    }
  }
EOS;
    }
    $result .= <<<EOS
  \$__elnData{$this->localIndex} = array();
  if(isset(\$__elnItem{$this->localIndex}['value'])){
    \$__elnData{$this->localIndex}['value'] = \$__elnItem{$this->localIndex}['value'];
  }
  if(isset(\$__elnItem{$this->localIndex}['label'])){
    \$__elnData{$this->localIndex}['label'] = \$__elnItem{$this->localIndex}['label'];
  }
  \$__elnOptions{$this->localIndex}[] = \$__elnData{$this->localIndex};
  ++ \$__elnIndex{$this->localIndex};
}
EOS;
    if(!$multiple){
      $result .= <<<EOS
if(empty(\$__elnSelected{$this->localIndex}) === false){
  \$__elnSelected{$this->localIndex} = array_slice(\$__elnSelected{$this->localIndex}, 0, 1);
}
EOS;
    }
    $result .= <<<EOS
if(empty(\$__elnSelected{$this->localIndex}) === false){
  foreach(\$__elnSelected{$this->localIndex} as \$__elnIndex{$this->localIndex}){
    \$__elnOptions{$this->localIndex}[\$__elnIndex{$this->localIndex}]['selected'] = true;
  }
}else if(\$__elnDefault{$this->localIndex} >= 0){
  \$__elnOptions{$this->localIndex}[\$__elnDefault{$this->localIndex}]['selected'] = true;
}
foreach(\$__elnOptions{$this->localIndex} as \$__elnItem{$this->localIndex}){
  if(isset(\$__elnItem{$this->localIndex}['value'])){
?>
<option value="<?php
    echo \$__elnItem{$this->localIndex}['value'];
?>"<?php
    if(isset(\$__elnItem{$this->localIndex}['selected'])){
?> selected="selected"<?php
    }
?>><?php
    if(isset(\$__elnItem{$this->localIndex}['label'])){
      echo \$__elnItem{$this->localIndex}['label'];
    }
?></option>
<?php
  }else if(isset(\$__elnItem{$this->localIndex}['label'])){
?>
<optgroup label="<?php echo \$__elnItem{$this->localIndex}['label']; ?>">
<?php
  }else{
?>
</optgroup>
<?php
  }
}
?>
EOS;
    ++ $this->localIndex;
    return trim($result);
  }

  /*
   * 要素
   */
  private function element($lineno, $str){
    /**/
    if(strcasecmp(trim($str), 'open') == 0){
      return '{';
    }else if(strcasecmp(trim($str), 'close') == 0){
      return '}';
    }

    /**/
    $result = '';

    /* 変換 */
    while(true){
      $str = ltrim($str);
      if(strlen($str) == 0){
	break;
      }
      $c = substr($str, 0, 1);
      if(strcmp($c, '$') == 0){
	/* パラメータ */
	if(preg_match('/\$([_0-9a-z\.]+)\s*\(\s*\)/i', $str, $matches)){
	  $result .= 'call_user_func($this->tMethod(\''.$matches[1].'\'))';
	}else if(preg_match('/\$([_0-9a-z\.]+)\s*\(/i', $str, $matches)){
	  $result .= 'call_user_func($this->tMethod(\''.$matches[1].'\'),';
	}else if(preg_match('/\$([_0-9a-z\.]+)/i', $str, $matches)){
	  $result .= '$this->tEvaluate(\''.$matches[1].'\')';
	}else{
	  throw new Eln_Exception(
				  _('{$1}: invalid expression syntax "{$2}" at line {$3}.'),
				  $this->htmlFile,
				  $str,
				  $lineno
				  );
	}
	$str = substr($str, strlen($matches[0]));
      }else if(strcmp($c, '\'') == 0){
	/* 引用符 */
	if(preg_match('/^\'((?:\\\\.|[^\'])*)\'/', $str, $matches)){
	  $result .= 'htmlspecialchars_decode(';
	  $result .= $this->compileExpression($lineno, $matches[1]);
	  $result .= ')';
	}else{
	  throw new Eln_Exception(
				  _('{$1}: invalid expression syntax "{$3}" at line {$3}.'),
				  $this->htmlFile,
				  $str,
				  $lineno
				  );
	}
	$str = substr($str, strlen($matches[0]));
      }else if(strcmp($c, '"') == 0){
	/* 二重引用符 */
	if(preg_match('/^"((?:\\\\.|[^"])*)"/', $str, $matches)){
	  $result .= 'htmlspecialchars_decode(';
	  $result .= $this->compileExpression($lineno, $matches[1], '"');
	  $result .= ')';
	}else{
	  throw new Eln_Exception(
				  _('{$1}: invalid expression syntax "{$2}" at line {$3}.'),
				  $this->htmlFile,
				  $str,
				  $lineno
				  );
	}
	$str = substr($str, strlen($matches[0]));
      }else if(preg_match('/^eln:([_0-9a-z]+)\s*\(/i', $str, $matches)){
	/* elnath関数 */
	$result .= '$this->template_'.$matches[1].'(';
	$str = substr($str, strlen($matches[0]));
      }else if(preg_match('/^([_0-9a-z]+)\s*\(/i', $str, $matches)){
	/* PHP関数 */
	$result .= $matches[1].'(';
	$str = substr($str, strlen($matches[0]));
      }else if(preg_match('/^([_0-9a-z]+)/i', $str, $matches)){
	/* 特殊演算子/数値リテラル/定数 */
	if(strcasecmp($matches[1], 'EQ') == 0){
	  $result .= '==';
	}else if(strcasecmp($matches[1], 'NE') == 0){
	  $result .= '!=';
	}else if(strcasecmp($matches[1], 'GT') == 0){
	  $result .= '>';
	}else if(strcasecmp($matches[1], 'GE') == 0){
	  $result .= '>=';
	}else if(strcasecmp($matches[1], 'LT') == 0){
	  $result .= '<';
	}else if(strcasecmp($matches[1], 'LE') == 0){
	  $result .= '<=';
	}else if(strcasecmp($matches[1], 'AND') == 0){
	  $result .= '&&';
	}else if(strcasecmp($matches[1], 'OR') == 0){
	  $result .= '||';
	}else if(strcasecmp($matches[1], 'NOT') == 0){
	  $result .= '!';
	}else{
	  $result .= $matches[0];
	}
	$str = substr($str, strlen($matches[0]));
      }else{
	/* その他 */
	$result .= mb_substr($str, 0, 1);
	$str = mb_substr($str, 1);
      }
    }

    /**/
    return $result;
  }
}
