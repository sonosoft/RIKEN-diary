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
		  }else if(preg_match('/^[0-9a-zA-Z]{8}$/', $row[0])){
		    $errors[] = sprintf('%d件目(%d行目): [ID]は英数字8文字で入力してください', $lno - 1, $lno);
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
		    $errors[] = sprintf('%d件目(%d行目): [ふりがな]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[4]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [メールアドレス]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }else if(!preg_match('/^[0-9a-zA-Z]+[-_\.0-9a-zA-Z]*@[-_0-9a-zA-Z0-9]+(?:\.[-_0-9a-zA-Z]+)+$/', $row[4])){
		    $errors[] = sprintf('%d件目(%d行目): [メールアドレス]を正しい形式で入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strlen($row[5]) == 0){
		    $errors[] = sprintf('%d件目(%d行目): [性別]が指定されていません', $lno - 1, $lno);
		    $validity = false;
		  }else if(!preg_match('/^[MF]$/', $row[5])){
		    $errors[] = sprintf('%d件目(%d行目): [性別]は「M」または「F」と入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  if(strtotime($row[6]) === false){
		    $errors[] = sprintf('%d件目(%d行目): [生年月日]を正しい日付の形式で入力してください', $lno - 1, $lno);
		    $validity = false;
		  }
		  if($validity){
		    /* 被験者 */
		    if(($user = $this->UserModel->findByCode($row[0])) === null){
		      $user = $this->UserModel->newModel();
		      $user->code = $row[0];
		    }
		    $user->token = $this->UserModel->generateToken();
		    $user->family_name = $row[1];
		    $user->first_name = $row[2];
		    $user->kana = $row[3];
		    $user->email = $row[4];
		    if($row[5] == 'M'){
		      $user->sex = 1;
		    }else{
		      $user->sex = 2;
		    }
		    $user->birthday = $row[6];
		    $user->status = 1;
		    $user->created_at = $this->app->data['_now_'];
		    $user->updated_at = $this->app->data['_now_'];
		    $user->save();
		    /**/
		    $users[] = array(
		      'code'=>$user->id.'-'.$user->code,
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
