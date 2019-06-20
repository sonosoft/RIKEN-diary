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
    $this->useModel('User', 'Project');
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
    if($this->app->data['user_search']['project_id'] !== null){
      if($this->app->data['user_search']['project_id'] > 0){
	$where[] = 'projects.project_id = :project_id';
      }else{
	$where[] = 'projects.project_id IS NULL';
      }
    }
    /**/
    $options = array(
      'joins'=>array('projects'=>'project'),
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
    foreach($this->app->data['users'] as $index=>$user){
      foreach($user->projects as $project){
	if($project->project->status == STATUS_ENABLED){
	  $this->app->data['users'][$index]->linkedProject = $project->project;
	}
      }
    }
    /**/
    $this->app->data['user_search']['page'] = $this->app->data['paginator']->currentPage;
    $this->app->storeSession('user_search', $this->app->data['user_search']);

    /* 選択肢 */
    $this->app->data['projectChoices1'] = $this->ProjectModel->collectChoices('全て', true);
    $this->app->data['projectChoices1'][] = array('value'=>0, 'label'=>'（未登録）');
    $this->app->data['projectChoices2'] = $this->ProjectModel->collectChoices('選択してください');
    
    /**/
    return 'admin/user/list';
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
    echo mb_convert_encoding('"氏名",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"ふりがな",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"メールアドレス",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"乱数",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"性別",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"生年月日",', 'SJIS', 'UTF-8');
    echo mb_convert_encoding('"状態"', 'SJIS', 'UTF-8');
    echo "\r\n";
    foreach($this->UserModel->all($options, $parameters) as $user){
      echo '"'.$user->code.'",';
      echo mb_convert_encoding('"'.$user->family_name.' '.$user->first_name.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->kana.'",', 'SJIS', 'UTF-8');
      echo '"'.$user->email.'",';
      echo '"'.$user->token.'",';
      echo mb_convert_encoding('"'.$user->sex_tos.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$user->birthday->format('%Y/%m/%d').'",', 'SJIS', 'UTF-8');
      if($user->status == STATUS_ENABLED){
	echo mb_convert_encoding('"有効"', 'SJIS', 'UTF-8');
      }else{
	echo mb_convert_encoding('"削除"', 'SJIS', 'UTF-8');
      }
      echo "\r\n";
    }
    $this->direct();
  }
}
