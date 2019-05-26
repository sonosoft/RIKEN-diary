<?php


/*
 * CSVファイルI/Oクラス
 */
final class Eln_CsvModule {
  /*
   * CSVを読み込む
   */
  public function read($filename, $encoding, $keys=null){
    /* ファイルを開く */
    if(($fp = fopen($filename, 'rb')) === false){
      throw new ShallotCSVException(
				    _t_(': failed to open CSV file.'),
				    ShallotCSVException::ERR_FILE_OPEN_FAILED
				    );
    }

    /**/
    $data = array();

    /* CSVファイル読み込み */
    try{
      while(feof($fp) === false){
	if(($row = $this->readRow($fp, $encoding, $keys)) !== null){
	  $data[] = $row;
	}
      }
    }catch(Exception $e){
      fclose($fp);
      throw $e;
    }

    /* ファイルを閉じる */
    fclose($fp);

    /**/
    return $data;
  }

  /*
   * CSVファイルから1レコード読み込む
   */
  public function readRow($fp, $encoding, $keys=null){
    /* CSV行取得 */
    $csvline = '';
    while(true){
      if(($tmp = fgets($fp)) === false){
	if(feof($fp) === true){
	  break;
	}
	throw new ShallotCSVException(_t_(': CSV file corrupted.'), ShallotCSVException::ERR_CSV_CORRUPTED);
      }
      if(strcasecmp($encoding, 'UTF-8') != 0){
	$tmp = mb_convert_encoding($tmp, 'UTF-8', $encoding);
      }
      $tmp = preg_replace('/(?:\r\n|[\r\n])$/', "\r\n", $tmp);
      $csvline .= $tmp;
      if(feof($fp) === true){
	break;
      }
      if(substr_count($csvline, '"') % 2 == 0){
	$csvline = preg_replace('/(?:\r\n|[\r\n])$/', ',', $csvline);
	break;
      }
    }

    /* カラム分割 */
    if(preg_match_all('/("[^"]*(?:""[^"]*)*"|[^,]*),/', $csvline, $matches, PREG_PATTERN_ORDER)){
      $row = array();
      foreach($matches[1] as $index=>$match){
	if(preg_match('/^"(.*)"$/s', $match, $ms)){
	  $match = $ms[1];
	  $match = str_replace('""', '"', $match);
	}
	$row[] = $match;
      }
      if($keys !== null){
	$row = $this->toHash($row, $keys, '', 'column_%04d');
      }
      return $row;
    }

    /**/
    return null;
  }

  /*
   * 線形配列をハッシュに変換する
   */
  public function toHash($in_array, $keys, $default_value='', $key_format=null){
    /**/
    if($key_format !== null){
      if(preg_match('/(#+)/', $key_format, $matches)){
	$key_format = str_replace($matches[1], sprintf('%%0%d', strlen($matches[1])), $key_format);
      }
    }

    /* ハッシュに変換 */
    $hash = array();
    foreach($in_array as $index=>$value){
      if($index < count($keys)){
	if($keys[$index] !== null){
	  $hash[$keys[$index]] = $value;
	}
      }else if($key_format !== null){
	$hash[sprintf($key_format, $index + 1)] = $value;
      }
    }
    for($cnt = count($in_array); $cnt < count($keys); ++ $cnt){
      if($keys[$cnt] !== null){
	$hash[$keys[$cnt]] = $default_value;
      }
    }

    /**/
    return $hash;
  }
}
