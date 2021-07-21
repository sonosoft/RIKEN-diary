<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/upload_action.php
 */


class AdminUserUploadAction extends AdminUserController {
  /*
   * アクション
   */
  public function action(){
    /**/
    $this->useModel('User');
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
		    $errors[] = sprintf('%d件目(%d行目): [ID]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }else if(!preg_match('/^[-0-9a-zA-Z]+$/', $row[0])){
		    $errors[] = sprintf('%d件目(%d行目): [ID]には半角英数字とハイフンのみ使用できます', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[1]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [姓]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[2]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [名]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[3]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [セイ]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[4]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [メイ]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strtotime($row[5]) === false){
		    $errors[] = sprintf('%d件目(%d行目): [生年月日]を正しい日付の形式で入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[6]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [性別]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }else if($row[6] != '男' && $row[6] != '女'){
		    $errors[] = sprintf('%d件目(%d行目): [性別]は「男」または「女」と入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[7]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [メールアドレス]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }else if(!preg_match('/^[0-9a-zA-Z]+[-_\.0-9a-zA-Z]*@[-_0-9a-zA-Z0-9]+(?:\.[-_0-9a-zA-Z]+)+$/', $row[7])){
		    $errors[] = sprintf('%d件目(%d行目): [メールアドレス]を正しい形式で入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[8]) > 0){
		    if(!preg_match('/^[0-9a-zA-Z]+[-_\.0-9a-zA-Z]*@[-_0-9a-zA-Z0-9]+(?:\.[-_0-9a-zA-Z]+)+$/', $row[8])){
		      $errors[] = sprintf('%d件目(%d行目): [メールアドレス（予備）]を正しい形式で入力してください', $lno - 1, $lno);
		      $validity = false;
		    }
		  }
		  if($validity){
		    /* 被験者 */
		    if(($user = $this->UserModel->findByCode($row[0])) === null){
		      $user = $this->UserModel->newModel();
		      $user->code = $row[0];
		      $user->created_at = $this->app->data['_now_'];
		      $label = '[新規]';
		    }else{
		      $label = '[更新]';
		    }
		    $user->token = $this->UserModel->generateToken();
		    $user->family_name = $row[1];
		    $user->first_name = $row[2];
		    $user->kana = $row[3].' '.$row[4];
		    $user->email = $row[7];
		    if(empty($row[8]) === false){
		      $user->email_alt = $row[8];
		    }else{
		      $user->email_alt = null;
		    }
		    if($row[6] == '男'){
		      $user->sex = SEX_MALE;
		    }else{
		      $user->sex = SEX_FEMALE;
		    }
		    $user->birthday = $row[5];
		    $user->status = 1;
		    $user->updated_at = $this->app->data['_now_'];
		    $user->save();
		    /**/
		    $users[] = array(
		      'label'=>$label,
		      'code'=>$user->code,
		      'name'=>$user->family_name.' '.$user->first_name,
		    );
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
      $this->app->writeLog('admin/user/upload', $e->getMessage());
      $this->redirect('default:admin/error.unexpected');
    }

    /**/
    if(empty($errors) === false){
      $this->app->storeSession('upload_errors', $errors);
      $this->redirect('default:admin/user.error');
    }
    $this->app->storeSession('uploaded_users', $users);
    $this->redirect('default:admin/user.receipt');
  }
}
