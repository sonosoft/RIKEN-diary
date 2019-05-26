<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/import/upload_action.php
 */


class AdminImportUploadAction extends AdminController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Applicant', 'Measurement');
    $this->useModule('Csv');

    /**/
    $errors = array();
    $users = array();
    $status = 0;
    
    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* ファイル */
      if(isset($_FILES['file']['error']) && $_FILES['file']['error'] == UPLOAD_ERR_OK){
	/* CSV検証 */
	if(($fp = fopen($_FILES['file']['tmp_name'], 'r')) !== false){
	  $lno = 1;
	  while(feof($fp) === false){
	    if(($row = $this->CsvModule->readRow($fp, 'SJIS-win')) !== null){
	      if($lno > 1){
		if(empty($row[0]) === false){
		  $validity = true;
		  if(strlen($row[0]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [姓]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[1]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [名]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[2]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [ふりがな]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[3]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [メールアドレス]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }else if(!preg_match('/^[0-9a-zA-Z]+[-_\.0-9a-zA-Z]*@[-_0-9a-zA-Z0-9]+(?:\.[-_0-9a-zA-Z]+)+$/', $row[3])){
		    $errors[] = sprintf('%d件目(%d行目): [メールアドレス]を正しい形式で入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  /*
		  if(strlen($row[4]) > 0 && intval($row[4]) != 1){
		    $errors[] = sprintf('%d件目(%d行目): [性別]は「男」または「女」と入力してください', $lno - 1, $lno);
		  }
		  */
		  if(strtotime($row[5]) === false){
		    $errors[] = sprintf('%d件目(%d行目): [生年月日]を正しい日付の形式で入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(!preg_match('/^[0-9]+(?:-[0-9]+)*$/', $row[6])){
		    $errors[] = sprintf('%d件目(%d行目): [電話番号]を正しい形式で入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(!preg_match('/^[0-9]{3}-[0-9]{4}$/', $row[7])){
		    $errors[] = sprintf('%d件目(%d行目): [郵便番号]を正しい形式で入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[8]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [住所]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }
		  /*
		  if(in_array($row[11], array('YES', 'NO')) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [連絡可否]は「YES」または「NO」と入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  */
		  if($validity){
		    /* 計測日 */
		    $d1 = '2019/'.str_replace('日', '', str_replace('月', '/', $row[12]));
		    $date = new Eln_Date(strtotime($d1.' '.$row[13]));
		    $measurement = $this->MeasurementModel->one(
		      array('where'=>'[measurementDate] = :date'),
		      array('date'=>$date)
		    );
		    if($measurement === null){
		      $limitDate = new Eln_Date($date->getTime() - 14);
		      $index = $this->MeasurementModel->getNextIndex();
		      $measurement = $this->MeasurementModel->newModel();
		      $measurement->manageIndex = $index;
		      $measurement->isDisplay = 0;
		      $measurement->measurementDate = $date;
		      $measurement->currentCount = 0;
		      $measurement->min = 60;
		      $measurement->reservationLimitDay = intval(($measurement->measurementDate->getTime() - $limitDate->getTime()) / 86400);
		      $measurement->limitDate = $limitDate;
		      $measurement->created_at = $this->app->data['_now_'];
		      $measurement->updated_at = $this->app->data['_now_'];
		      $measurement->save();
		      ++ $index;
		    }
		    $codes = $this->ApplicantModel->getCodes($measurement->id);
		    if(count($codes) >= 10){
		      $errors[] = sprintf('%d件目(%d行目): [%s %s]が定員に達しています', $lno - 1, $lno, $row[12], $row[13]);
		      $validity = false;
		    }else{
		      $index = $measurement->startID;
		      while(true){
			$code = $this->ApplicantModel->generateCode($measurement->measurementDate, $index);
			if(in_array($code, $codes) === false){
			  break;
			}
			++ $index;
		      }
		    }
		    if($validity){
		      /* 被験者 */
		      $applicant = $this->ApplicantModel->newModel();
		      $applicant->userID = $code;
		      $applicant->randomString = $this->ApplicantModel->generateRandom();
		      $applicant->familyname = $row[0];
		      $applicant->firstname = $row[1];
		      $applicant->kana = $row[2];
		      $applicant->email = $row[3];
		      $applicant->isMan = intval($row[4]);
		      $applicant->birthdate = $row[5];
		      $applicant->tel = $row[6];
		      $applicant->postnumber = $row[7];
		      $applicant->address = $row[8];
		      if(empty($row[9]) === false){
			$applicant->belonging = $row[9];
		      }
		      if(empty($row[10]) === false){
			$applicant->intermediationName = $row[10];
		      }
		      $applicant->isContactOk = intval($row[11]);
		      $applicant->acceptanceDate = $this->app->data['_now_'];
		      $applicant->remindCount = 0;
		      $applicant->belongMeasurementDateID = $measurement->id;
		      $applicant->belongMeasurementDateIndex = $index;
		      $applicant->created_at = $this->app->data['_now_'];
		      $applicant->updated_at = $this->app->data['_now_'];
		      $applicant->save();
		      /**/
		      $measurement->currentCount = count($codes) + 1;
		      $measurement->save();

		      /**/
		      $users[] = array(
			'code'=>$applicant->id.'-'.$applicant->userID,
			'name'=>$applicant->familyname.' '.$applicant->firstname,
			'date'=>$measurement->measurementDate->format('%Y/%m/%d %H:%M'),
		      );
		    }
		  }
		}
	      }
	      ++ $lno;
	    }
	  }
	  $status = 1;
	  fclose($fp);
	}
      }
      if($status == 0){
	$errors[] = 'ファイルが正常にアップロードされませんでした';
      }

      /**/
      if(empty($errors)){
	/* コミット */
	$this->db->commit();
      }else{
	/* ロールバック */
	$this->db->rollback();
      }
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      $this->app->writeLog('admin/import/upload', $e->getMessage());
      $this->redirect('default:admin/error.error');
    }

    /**/
    if(empty($errors) === false){
      $this->app->storeSession('upload_errors', $errors);
      $this->redirect('default:admin/import.error');
    }
    $this->app->storeSession('uploaded_users', $users);
    $this->redirect('default:admin/import.receipt');
  }
}
