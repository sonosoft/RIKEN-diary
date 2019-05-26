<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * admin/applicant/controller.php
 */


/*
 * コントローラ
 */
class AdminApplicantController extends AdminController {
  /*
   * 一覧
   */
  protected function viewList(){
    /**/
    $this->useModel('Applicant');
    $this->useValidator('ApplicantSearch');

    /* 検索条件検証 */
    $this->app->data['applicant_search'] = $this->ApplicantSearchValidator->validate($this->app->data['applicant_search']);

    /* リストオプション */
    $where = array();
    if($this->app->data['applicant_search']['text_keys'] !== null){
      foreach($this->app->data['applicant_search']['text_keys'] as $key){
	$or = array(
	  sprintf('INSTR([userID], :%s) != 0', $key),
	  sprintf('INSTR([familyname], :%s) != 0', $key),
	  sprintf('INSTR([firstname], :%s) != 0', $key),
	  sprintf('INSTR([email], :%s) != 0', $key),
	  sprintf('INSTR([kana], :%s) != 0', $key),
	);
	$where[] = '('.implode(' OR ', $or).')';
      }
    }
    if($this->app->data['applicant_search']['from'] !== null){
      $where[] = 'DATE(measurement.measurementDate) >= :from';
    }
    if($this->app->data['applicant_search']['to'] !== null){
      $where[] = 'DATE(measurement.measurementDate) <= :to';
    }
    if($this->app->data['applicant_search']['all']){
      ;
    }else{
      $where[] = '[deleted_at] IS NULL';
    }
    /**/
    $options = array(
      'joins'=>'measurement',
      'where'=>implode(' AND ', $where),
      'pageSize'=>50,
      'indexSize'=>10,
      'page'=>$this->app->data['applicant_search']['page'],
    );
    switch($this->app->data['applicant_search']['order']){
    case 'i-a':
      $options['order'] = '[userID] ASC';
      break;
    case 'i-d':
      $options['order'] = 'SUBSTRING([userID], 1, 7) DESC, [userID] ASC';
      break;
    case 'n-a':
      $options['order'] = '[familyname] ASC, [firstname] ASC';
      break;
    case 'n-d':
      $options['order'] = '[familyname] DESC, [firstname] DESC';
      break;
    case 'k-a':
      $options['order'] = '[kana] ASC';
      break;
    case 'k-d':
      $options['order'] = '[kana] DESC';
      break;
    case 'm-a':
      $options['order'] = 'measurement.measurementDate ASC';
      break;
    case 'm-d':
      $options['order'] = 'DATE(measurement.measurementDate) DESC, TIME(measurement.measurementDate) ASC';
      break;
    }
    $parameters = $this->app->data['applicant_search'];

    /* ダウンロード */
    if($this->app->data['applicant_search']['download']){
      $this->download($options, $parameters);
    }
    
    /* 検索 */
    list($this->app->data['applicants'], $this->app->data['paginator']) = $this->ApplicantModel->page($options, $parameters);
    /**/
    $this->app->data['applicant_search']['page'] = $this->app->data['paginator']->currentPage;
    $this->app->storeSession('applicant_search', $this->app->data['applicant_search']);

    /**/
    return 'admin/applicant/list';
  }

  /* ===== ===== */

  private function download($options, $parameters){
    /**/
    unset($options['pageSize']);
    unset($options['indexSize']);
    unset($options['page']);
    
    /* ダウンロード */
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename=applicants-'.$this->app->data['_now_']->format('%Y%m%d_%H%M%S').'.csv');
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
    foreach($this->ApplicantModel->all($options, $parameters) as $applicant){
      echo '"'.$applicant->userID.'",';
      echo '"'.$applicant->userIDa.'",';
      echo mb_convert_encoding('"'.$applicant->familyname.' '.$applicant->firstname.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$applicant->kana.'",', 'SJIS', 'UTF-8');
      echo '"'.$applicant->email.'",';
      if(isset($applicant->measurement->measurementDate)){
	echo mb_convert_encoding('"'.$applicant->measurement->measurementDate->format('%Y/%m/%d (%a) %H:%M').'",', 'SJIS', 'UTF-8');
      }else{
	echo '"",';
      }
      echo '"'.$applicant->randomString.'",';
      if($applicant->acceptanceDate !== null){
	echo mb_convert_encoding('"'.$applicant->acceptanceDate->format('%Y/%m/%d (%a) %H:%M').'",', 'SJIS', 'UTF-8');
      }else{
	echo '"",';
      }
      echo mb_convert_encoding('"'.$applicant->questionnaire_tos.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$applicant->sex_tos.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$applicant->birthdate.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$applicant->intermediationName.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$applicant->tel.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$applicant->postnumber.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$applicant->address.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$applicant->belonging.'",', 'SJIS', 'UTF-8');
      echo mb_convert_encoding('"'.$applicant->contacting_tos.'",', 'SJIS', 'UTF-8');
      if($applicant->deleted_at !== null){
	echo mb_convert_encoding('"キャンセル"', 'SJIS', 'UTF-8');
      }else{
	echo mb_convert_encoding('"有効"', 'SJIS', 'UTF-8');
      }
      echo "\r\n";
    }
    $this->direct();
  }
}
