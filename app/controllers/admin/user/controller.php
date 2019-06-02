<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/user/controller.php
 */


/*
 * コントローラ
 */
class AdminUserController extends AdminController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('User');
    $this->useValidator('UserSearch');

    /* 検索条件検証 */
    $this->app->data['user_search'] = $this->UserSearchValidator->validate($this->app->data['user_search']);

    /* リストオプション */
    $where = array();
    if($this->app->data['user_search']['text_keys'] !== null){
      foreach($this->app->data['user_search']['text_keys'] as $key){
	$or = array(
	  sprintf('INSTR([code], :%s) != 0', $key),
	  sprintf('INSTR([family_name], :%s) != 0', $key),
	  sprintf('INSTR([first_name], :%s) != 0', $key),
	  sprintf('INSTR([kana], :%s) != 0', $key),
	  sprintf('INSTR([email], :%s) != 0', $key),
	);
	$where[] = '('.implode(' OR ', $or).')';
      }
    }
    if($this->app->data['user_search']['all']){
      ;
    }else{
      $where[] = '[status] = :enabled';
    }
    /**/
    $options = array(
      'where'=>implode(' AND ', $where),
      'pageSize'=>50,
      'indexSize'=>10,
      'page'=>$this->app->data['user_search']['page'],
    );
    switch($this->app->data['user_search']['order']){
    case 'i-a':
      $options['order'] = '[code] ASC';
      break;
    case 'n-a':
      $options['order'] = '[family_name] ASC, [first_name] ASC';
      break;
    case 'n-d':
      $options['order'] = '[family_name] DESC, [first_name] DESC';
      break;
    case 'k-a':
      $options['order'] = '[kana] ASC';
      break;
    case 'k-d':
      $options['order'] = '[kana] DESC';
      break;
    }
    $parameters = $this->app->data['user_search'];
    $parameters['enabled'] = STATUS_ENABLED;

    /* ダウンロード */
    if($this->app->data['user_search']['download']){
      $this->download($options, $parameters);
    }
    
    /* 検索 */
    list($this->app->data['users'], $this->app->data['paginator']) = $this->UserModel->page($options, $parameters);
    /**/
    $this->app->data['user_search']['page'] = $this->app->data['paginator']->currentPage;
    $this->app->storeSession('user_search', $this->app->data['user_search']);

    /**/
    return 'admin/user/list';
  }

  /*
   * フォーム
   */
  protected function viewForm(){
    /**/
    $this->useModel('Project');
    
    /* 時間 */
    $this->app->data['projectChoices'] = $this->ProjectModel->collectChoices('選択してください');
    
    /**/
    return 'admin/diary/form';
  }

  /* ===== ===== */

  private function download($options, $parameters){
    /**/
    unset($options['pageSize']);
    unset($options['indexSize']);
    unset($options['page']);
    
    /* ダウンロード */
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename=users-'.$this->app->data['_now_']->format('%Y%m%d_%H%M%S').'.csv');
    echo mb_convert_encoding('"ユーザID",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"ユーザIDα",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"氏名",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"ふりがな",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"メールアドレス",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"計測日時",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"乱数",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"受付日時",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"質問紙",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"性別",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"生年月日",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"企業・大学機関",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"電話番号",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"郵便番号",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"住所",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"所属",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"案内可否"', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"状態"', 'SJIS', 'UTF-8');
    echo "\r\n";
    foreach($this->UserModel->all($options, $parameters) as $user){
      echo '"'.$user->userID.'",';
      echo '"'.$user->userIDa.'",';
      echo mb_convert_encoding('"'.$user->familyname.' '.$user->firstname.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->kana.'",', 'SJIS', 'UTF-8');
      echo '"'.$user->email.'",';
      if(isset($user->measurement->measurementDate)){
	echo mb_convert_encoding('"'.$user->measurement->measurementDate->format('%Y/%m/%d (%a) %H:%M').'",', 'SJIS', 'UTF-8');
      }else{
	echo '"",';
      }
      echo '"'.$user->randomString.'",';
      if($user->acceptanceDate !== null){
	echo mb_convert_encoding('"'.$user->acceptanceDate->format('%Y/%m/%d (%a) %H:%M').'",', 'SJIS', 'UTF-8');
      }else{
	echo '"",';
      }
      echo mb_convert_encoding('"'.$user->questionnaire_tos.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->sex_tos.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->birthdate.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->intermediationName.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->tel.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->postnumber.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->address.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->belonging.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->contacting_tos.'",', 'SJIS', 'UTF-8');
      if($user->deleted_at !== null){
	echo mb_convert_encoding('"キャンセル"', 'SJIS', 'UTF-8');
      }else{
	echo mb_convert_encoding('"有効"', 'SJIS', 'UTF-8');
      }
      echo "\r\n";
    }
    $this->direct();
  }
}
