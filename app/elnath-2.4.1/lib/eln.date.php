<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.date.php
 */


/*
 * 日時クラス
 */
final class Eln_Date{
  /*
   * プロパティ
   */
  public $year;
  public $month;
  public $day;
  public $hour;
  public $minute;
  public $second;
  /**/
  private $dateFlag;
  private $timeFlag;

  /* ===== ===== */

  /*
   * 本日午前0時
   */
  public static function today(){
    $today = new Eln_Date();
    $today->zero();
    /**/
    return $today;
  }
  public static function sToday(){
    $today = new Eln_Date();
    $today->zero();
    /**/
    return $today->unixtime();
  }

  /*
   * 現在
   */
  public static function now(){
    return new Eln_Date();
  }
  public static function sNow(){
    return time();
  }

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  public function __construct($time=null, $dateFlag=true, $timeFlag=true){
    /* フラグ */
    $this->dateFlag = $dateFlag;
    $this->timeFlag = $timeFlag;

    /* 日時 */
    if($time !== null){
      $this->setTime($time);
    }else{
      $this->setTime(time());
    }
  }

  /* ===== ===== */

  /*
   * 日時を取得する
   */
  public function getTime(){
    return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
  }

  /*
   * 日時を設定する
   */
  public function setTime($time){
    $ts = explode(',', date('Y,n,j,G,i,s', intval($time)));
    if($this->dateFlag === false){
      $ds = explode(',', date('Y,n,j'));
      $ts[0] = $ds[0];
      $ts[1] = $ds[1];
      $ts[2] = $ds[2];
    }
    /**/
    $this->year = intval($ts[0]);
    $this->month = intval($ts[1]);
    $this->day = intval($ts[2]);
    $this->hour = intval($ts[3]);
    $this->minute = intval($ts[4]);
    $this->second = intval($ts[5]);
  }

  /*
   * 曜日を取得する(0[日曜]～6[土曜])
   */
  public function getDayOfWeek(){
    return intval(date('w', $this->getTime()));
  }

  /*
   * 月の日数を取得する
   */
  public function getMonthLength(){
    return intval(date('t', $this->getTime()));
  }

  /*
   * 時刻を午前0時に設定する
   */
  public function zero(){
    $this->hour = $this->minute = $this->second = 0;
  }

  /*
   * 時刻を正午に設定する
   */
  public function noon(){
    $this->hour = 12;
    $this->minute = $this->second = 0;
  }

  /*
   * 比較する
   */
  public function compare($date){
    if($date instanceof Eln_Date){
      if($this->getTime() < $date->getTime()){
	return -1;
      }else if($this->getTime() > $date->getTime()){
	return 1;
      }
      return 0;
    }
    return false;
  }

  /*
   * 日時を文字列に変換する
   */
  public function format($format=null){
    if($format === null){
      if($this->dateFlag && $this->timeFlag){
	$format = '%Y-%m-%d %H:%M:%S';
      }else if($this->dateFlag){
	$format = '%Y-%m-%d';
      }else{
	$format = '%H:%M:%S';
      }
    }
    return strftime($format, $this->getTime());
  }
}
