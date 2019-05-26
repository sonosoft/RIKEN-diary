<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/measurement/reserve_action.php
 */


class AdminMeasurementReserveAction extends AdminMeasurementController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('Applicant', 'Measurement');
    $this->useModule('Csv');
    
    /* トランザクション */
    $this->db->begin();

    /**/
    try{
      /* 計測日 */
      if(($measurement = $this->MeasurementModel->findById($this->app->readRequest('id'))) === null){
	$this->redirect('default:admin/error.error');
      }

      /* データ */
      $left = 10 - $measurement->currentCount;
      $data = array();
      $errors = array();
      $status = 0;

      /* ファイル */
      if(isset($_FILES['file']['error']) && $_FILES['file']['error'] == UPLOAD_ERR_OK){
	/* CSV検証 */
	if(($fp = fopen($_FILES['file']['tmp_name'], 'r')) !== false){
	  $lno = 1;
	  while(feof($fp) === false){
	    if(($row = $this->CsvModule->readRow($fp, 'SJIS-win')) !== null){
	      if($lno > 1){
		if(strlen($row[0]) == 0){
		  $errors[] = sprintf('%d件目(%d行目): [姓]が指定されていません', $lno - 1, $lno);
		}
		if(strlen($row[1]) == 0){
		  $errors[] = sprintf('%d件目(%d行目): [名]が指定されていません', $lno - 1, $lno);
		}
		if(strlen($row[2]) == 0){
		  $errors[] = sprintf('%d件目(%d行目): [ふりがな]が指定されていません', $lno - 1, $lno);
		}
		if(in_array($row[3], array('男', '女')) === false){
		  $errors[] = sprintf('%d件目(%d行目): [性別]は「男」または「女」と入力してください', $lno - 1, $lno);
		}
		if(strtotime($row[4]) === false){
		  $errors[] = sprintf('%d件目(%d行目): [生年月日]を正しい日付の形式で入力してください', $lno - 1, $lno);
		}
		if(!preg_match('/^[0-9a-zA-Z]+[-_\.0-9a-zA-Z]*@[-_0-9a-zA-Z0-9]+(?:\.[-_0-9a-zA-Z]+)+$/', $row[5])){
		  $errors[] = sprintf('%d件目(%d行目): [メールアドレス]を正しい形式で入力してください', $lno - 1, $lno);
		}
		if(!preg_match('/^[0-9]+(?:-[0-9]+)*$/', $row[6])){
		  $errors[] = sprintf('%d件目(%d行目): [電話番号]を正しい形式で入力してください', $lno - 1, $lno);
		}
		if(!preg_match('/^[0-9]{3}-[0-9]{4}$/', $row[7])){
		  $errors[] = sprintf('%d件目(%d行目): [郵便番号]を正しい形式で入力してください', $lno - 1, $lno);
		}
		if(strlen($row[8]) == 0){
		  $errors[] = sprintf('%d件目(%d行目): [住所]が指定されていません', $lno - 1, $lno);
		}
		if(in_array($row[11], array('YES', 'NO')) == 0){
		  $errors[] = sprintf('%d件目(%d行目): [連絡可否]は「YES」または「NO」と入力してください', $lno - 1, $lno);
		}
		$data[] = array(
		  'familyname'=>$row[0],
		  'firstname'=>$row[1],
		  'kana'=>$row[2],
		  'sex'=>$row[3],
		  'birthdate'=>$row[4],
		  'email'=>$row[5],
		  'tel'=>$row[6],
		  'postnumber'=>$row[7],
		  'address'=>$row[8],
		  'intermediationName'=>(empty($row[9]) ? null: $row[9]),
		  'belonging'=>(empty($row[10]) ? null : $row[10]),
		  'isContactOk'=>((strcmp($row[11], 'YES') == 0) ? 1 : 0),
		);
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
      }else if(count($data) > $left){
	$errors[] = '予約枠以上の被験者を登録しようとしています';
      }
      /**/
      if(empty($errors) === false){
	throw new Eln_Validation(array(), array('file'=>$errors));
      }

      /**/
      $codes = $this->ApplicantModel->getCodes($measurement->id);
      $index = $measurement->startID;
      
      /* 保存 */
      foreach($data as $entry){
	while(true){
	  $code = $this->ApplicantModel->generateCode($measurement->measurementDate, $index);
	  if(in_array($code, $codes) === false){
	    $codes[] = $code;
	    break;
	  }
	  ++ $index;
	}
	$applicant = $this->ApplicantModel->newModel($entry);
	$applicant->userID = $code;
	$applicant->randomString = $this->ApplicantModel->generateRandom();
	$applicant->acceptanceDate = $this->app->data['_now_'];
	$applicant->remindCount = 0;
	$applicant->isMan = ((strcmp($entry['sex'], '男') == 0) ? 1: 0);
	$applicant->belongMeasurementDateID = $measurement->id;
	$applicant->belongMeasurementDateIndex = $index;
	$applicant->created_at = $this->app->data['_now_'];
	$applicant->updated_at = $this->app->data['_now_'];
	$applicant->save();
	/**/
	++ $measurement->currentCount;
      }
      $measurement->updated_at = $this->app->data['_now_'];
      $measurement->save();

      /* コミット */
      $this->db->commit();
    }catch(Exception $e){
      /* ロールバック */
      $this->db->rollback();

      /**/
      if($e instanceof Eln_Validation){
	/* エラー */
	$this->app->errors = $e->getErrors();
	return 'admin/measurement/error';
      }else if($e instanceof Eln_Redirection){
	/* リダイレクト */
	throw $e;
      }else{
	/* 例外 */
	$this->app->writeLog('admin/measurement/reserve', $e->getMessage());
	$this->redirect('default:admin/error.error');
      }
    }

    /**/
    $this->redirect('default:admin/measurement.search');
  }
}
