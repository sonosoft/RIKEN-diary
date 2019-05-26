<?php


/*
 * メール送信モジュールクラス
 */
final class Eln_MailModule {
  /*
   * プロパティ
   */
  private $app;
  private $charset;
  private $headerEncoding;
  private $sender;
  private $recipients;
  private $subject;
  private $body;
  private $headers;
  private $attachments;
  private $parameters;

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  public function __construct(){
    /* アプリケーション */
    $this->app = Eln_Application::getInstance();

    /* プロパティ初期化 */
    $this->reset();
  }

  /* ===== ===== */

  /*
   * 初期化
   */
  public function reset(){
    /* プロパティ初期化 */
    $this->charset = 'ISO-2022-JP';
    $this->headerEncoding = 'B';
    $this->sender = null;
    $this->recipients = array('To'=>array(), 'Cc'=>array(), 'Bcc'=>array());
    $this->subject = '';
    $this->body = '';
    $this->headers = array();
    $this->attachments = array();
    $this->parameters = array();
  }

  /*
   * テンプレートファイルの存在を調べる
   */
  public function getTemplateFile($template){
    return $this->app->projectFile(sprintf('messages/%s', $template));
  }

  /*
   * テンプレートからメールを作成
   */
  public function read($template){
    /* ファイルを変換 */
    $path = $this->getTemplateFile($template);
    if(file_exists($path) === false){
      throw new Eln_Exception(_('message template "{$1}" does not exist.'), $filename);
    }
    $message = file_get_contents($path);
    $message = preg_replace('/(?:\r\n|[\r\n])/', PHP_EOL, $message);

    /* ヘッダとボディ */
    list($this->subject, $this->body) = explode(PHP_EOL, $message, 2);
  }

  /*
   * メール送信文字コードを設定する
   */
  public function setCharset($charset){
    $this->charset = $charset;
  }
  public function setHeaderEncoding($encoding){
    $this->headerEncoding = $encoding;
  }

  /*
   * 送信元を設定する
   */
  public function setSender($address, $name=null){
    $this->sender = array($address, $name);
  }

  /*
   * 宛先を追加する
   */
  public function setRecipient($type, $address, $name=null){
    foreach(array_keys($this->recipients) as $key){
      if(strcasecmp($key, $type) == 0){
	$this->recipients[$key][] = array($address, $name);
	return;
      }
    }
    throw new Eln_Exception(_('unknown mail recipient type "{$1}".'), $type);
  }

  /*
   * 表題を設定する
   */
  public function setSubject($subject){
    $this->subject = $subject;
  }

  /*
   * 本文を設定する
   */
  public function setBody($body){
    $this->body = $body;
  }

  /*
   * ヘッダを追加する
   */
  public function setHeader($name, $value){
    $this->headers[$name] = $value;
  }

  /*
   * 添付ファイルを追加する
   */
  public function setAttachment($filename, $mimeType, $name){
    $this->attachments[] = array($filename, $mimeType, $name);
  }

  /*
   * パラメータを設定する
   */
  public function assign($name, $value){
    $this->parameters[$name] = $value;
  }

  /*
   * メールを送信する
   */
  public function send(){
    /**/
    list($to, $subject, $body, $headers) = $this->build();
    /**/
    mail($to, $subject, $body, $headers);
  }

  /* ===== ===== */

  /*
   * メールを構築する
   */
  private function build(){
    /* ヘッダ */
    $headers = $this->headers;
    /**/
    if($this->sender === null){
      throw new Eln_Exception(_('mail source is not specified.'), $name);
    }
    $headers['From'] = $this->encodeAddressHeader('6', $this->sender[0], $this->sender[1]);
    /**/
    $to = array();
    foreach(array_keys($this->recipients) as $key){
      if(empty($this->recipients[$key]) === false){
	$recipients = array();
	foreach($this->recipients[$key] as $index=>$recipient){
	  if($index == 0){
	    $recipients[] = $this->encodeAddressHeader(strlen($key) + 2, $recipient[0], $recipient[1]);
	  }else{
	    $recipients[] = $this->encodeAddressHeader(1, $recipient[0], $recipient[1]);
	  }
	}
	if(strcasecmp($key, 'To') == 0){
	  $to[] = $this->encodeAddressHeader(0, $recipient[0], $recipient[1]);
	}else{
	  $headers[$key] = implode(',' . PHP_EOL . ' ', $recipients);
	}
	$recipientFlag = true;
      }
    }
    if(empty($to)){
      throw new Eln_Exception(_('mail recipient(s) is not specified.'));
    }
    $to = str_replace(PHP_EOL, '', implode(', ', $to));

    /* 表題 */
    $subject = $this->compileText($this->subject);
    if(preg_match('/[\x80-\xFF]/', $subject)){
      $subject = mb_encode_mimeheader(
				      $subject,
				      $this->charset,
				      $this->headerEncoding,
				      PHP_EOL,
				      9
				      );
    }

    /* その他のヘッダ */
    if($this->hasHeader('date') === false){
      $headers['Date'] = date('D, d M Y H:i:s O');
    }
    if($this->hasHeader('mime-version') === false){
      $headers['Mime-Version'] = '1.0';
    }

    /* 本文 */
    $textBody = $this->compileText($this->body);
    $textBody = mb_convert_encoding($textBody, $this->charset);
    $textBody = preg_replace('/(?:\r\n|[\r\n])/', PHP_EOL, $textBody);
    $textBody = str_replace(PHP_EOL . '.', PHP_EOL . '..', $textBody);
    if(strcmp(mb_substr($textBody, 0, 1), '.') == 0){
      $textBody = '.' . $textBody;
    }
    /**/
    if(count($this->attachments)){
      $boundary = sprintf('Elnath__boundary__%d', time());
      $headers['Content-Type'] = sprintf('multipart/mixed; boundary="%s"', $boundary);
      /**/
      $body  = PHP_EOL;
      $body .= sprintf('--%s', $boundary);
      $body .= PHP_EOL;
      $body .= sprintf('Content-Type: text/plain; charset="%s"', $this->charset);
      $body .= PHP_EOL;
      $body .= PHP_EOL;
      $body .= $textBody;
      $body .= PHP_EOL;
      $body .= PHP_EOL;
      foreach($this->attachments as $attachment){
        $body .= sprintf('--%s', $boundary);
	$body .= PHP_EOL;
        $body .= $this->encode_attachment($attachment[0], $attachment[1], $attachment[2]);
	$body .= PHP_EOL;
	$body .= PHP_EOL;
      }
      $body .= sprintf('--%s--', $boundary);
      $body .= PHP_EOL;
    }else{
      $headers['Content-Type'] = sprintf('text/plain; charset="%s"', $this->charset);
      $body = $textBody;
    }

    /**/
    $additional_headers = array();
    foreach($headers as $label=>$value){
      $additional_headers[] = $label . ': ' . $value;
    }
    return array($to, $subject, $body, implode(PHP_EOL, $additional_headers));
  }

  /*
   * アドレスヘッダをエンコードする
   */
  private function encodeAddressHeader($offset, $address, $name){
    /* ニックネームを検査 */
    if($name === null){
      return $address;
    }

    /* ニックネームをエンコード */
    if(preg_match('/[\x80-\xFF]/', $name)){
      $nn = mb_encode_mimeheader($name, $this->charset, $this->headerEncoding, PHP_EOL, $offset);
    }else{
      $nn = $name;
    }

    /**/
    return $nn . PHP_EOL . sprintf(' <%s>', $address);
  }

  /*
   * パラメータを変換する
   */
  private function compileText($src){
    if(preg_match_all('/\{\$([^\}]+)\}/', $src, $matches)){
      foreach($matches[1] as $index=>$statement){
	$result = null;
	foreach(explode('.', $statement) as $segment){
	  if($result === null){
	    if(isset($this->parameters[$segment]) === false){
	      break;
	    }
	    $result = $this->parameters[$segment];
	  }else if(is_object($result) && isset($result->$segment)){
	    $result = $result->$segment;
	  }else if(is_array($result) && isset($result[$segment])){
	    $result = $result[$segment];
	  }else{
	    $result = null;
	    break;
	  }
	}
	if($result !== null){
	  $src = str_replace($matches[0][$index], $result, $src);
	}else{
	  $src = str_replace($matches[0][$index], '', $src);
	}
      }
    }
    return $src;
  }

  /*
   * 添付ファイルをエンコードする
   */
  private function encodeAttachment($filename, $mimeType, $name){
    /* ファイルを検査 */
    if(file_exists($filename) === false){
      throw new ShallotInternalServerErrorException(_t_(': mail attachment file not found'), $filename);
    }

    /* ファイル名をエンコードする */
    if(preg_match('/[\x80-\xFF]/', $name)){
      $attname = mb_convert_encoding($name, $this->charset, $this->internal_encoding);
      if(strcasecmp($this->encoding, 'Q') == 0){
        $attname = sprintf('=?%s?Q?%s?=', $this->charset, quoted_printable_encode($attname));
      }else{
        $attname = sprintf('=?%s?B?%s?=', $this->charset, base64_encode($attname));
      }
    }else{
      $attname = $name;
    }

    /* ヘッダ */
    $body  = sprintf('Content-Type: %s; name="%s"', $mimeType, $attname);
    $body .= PHP_EOL;
    $body .= sprintf('Content-Disposition: attachment; filename="%s"', $attname);
    $body .= PHP_EOL;
    $body .= 'Content-Transfer-Encoding: base64';
    $body .= PHP_EOL;
    $body .= PHP_EOL;

    /* 本文 */
    $body .= chunk_split(base64_encode(file_get_contents($filename)));

    /**/
    return $body;
  }

  /*
   * ヘッダを検査する
   */
  private function hasHeader($name){
    foreach(array_keys($this->headers) as $key){
      if(strcasecmp($key, $name) == 0){
        return true;
      }
    }
    return false;
  }
}
