<?php

/*
 * [Elnath PHP Web Application Framework]
 * Copyright (c) 2013 SONOSOFT Inc., All rights reserved.
 *
 * eln.model.php
 */


/*
 * モデルファクトリークラス
 */
abstract class Eln_ModelFactory extends Eln_Object {
  /*
   * プロパティ
   */
  protected $model = null;
  protected $table = null;
  protected $relations = array();
  /**/
  private $factories;
  private $hasManyFlag;
  private $from;
  private $paginator;
  /**/
  private $__fields;
  /**/
  private static $instances = array();

  /* ===== ===== */

  /*
   * インスンス
   */
  public static function getInstance($name){
    $key = strtolower($name);
    if(empty(self::$instances[$key])){
      self::$instances[$key] = new $name();
    }
    return self::$instances[$key];
  }

  /* ===== ===== */

  /*
   * 複数レコード取得
   */
  public function all($options=array(), $parameters=null){
    // データベース
    $database = Eln_Database::getCurrentDatabase();

    // SQL部品
    $from = $this->getFrom($database, $options);
    $fields = $this->getSelect($this->model);
    foreach($this->factories as $alias=>$factory){
      $fields = array_merge($fields, $factory->getSelect($alias));
    }
    $options = array_merge(array('joins'=>array()), $options);
    if(is_array($parameters) === false){
      $parameters = array();
    }

    //
    if(isset($options['page'])){
      // レコード数
      if(($distinct = $this->getDistinct())){
	$statement = 'SELECT COUNT(DISTINCT ' . $distinct . ')' . $from;
      }else{
	$statement = 'SELECT COUNT(*)' . $from;
      }
      $statement .= $this->getWhere($options);
      $result = $database->query($this->getStatement($statement), $parameters);
      $count = 0;
      if(($row = $result->fetch_row()) !== null){
	$count = intval($row[0]);
      }
      $this->paginator = new Eln_Paginator($count, $options);
      $options['limit'] = sprintf(
	'%d, %d',
	($this->paginator->currentPage - 1) * $this->paginator->pageSize,
	$this->paginator->pageSize
      );
      if($this->paginator->itemCount == 0){
	return array();
      }
      //
      if($this->hasManyFlag && $distinct){
	// ユニークレコード
	$statement  = 'SELECT DISTINCT ' . $distinct . $from;
	$statement .= $this->getWhere($options) . $this->getOrder($options) . $this->getLimit($options);
	$result = $database->query($this->getStatement($statement), $parameters);
	$ids = array();
	while(($row = $result->fetch_row()) !== null){
	  $ids[] = $row[0];
	}
	if(empty($ids)){
	  return array();
	}
	if(empty($options['where'])){
	  $options['where'] = $distinct . ' IN :__distinct_ids__';
	}else{
	  $options['where'] = '(' . $options['where'] . ') AND ' . $distinct . ' IN :__distinct_ids__';
	}
	$parameters['__distinct_ids__'] = $ids;
	unset($options['limit']);
      }
    }else if($this->hasManyFlag && isset($options['limit']) && ($distinct = $this->getDistinct())){
      // ユニークレコード
      $statement  = 'SELECT DISTINCT ' . $distinct . $from;
      $statement .= $this->getWhere($options) . $this->getOrder($options) . $this->getLimit($options);
      $result = $database->query($this->getStatement($statement), $parameters);
      $ids = array();
      while(($row = $result->fetch_row()) !== null){
	$ids[] = $row[0];
      }
      if(empty($ids)){
	return array();
      }
      if(empty($options['where'])){
	$options['where'] = $distinct . ' IN :__distinct_ids__';
      }else{
	$options['where'] = '(' . $options['where'] . ') AND ' . $distinct . ' IN :__distinct_ids__';
      }
      $parameters['__distinct_ids__'] = $ids;
      unset($options['limit']);
    }

    // SQL
    $statement  = 'SELECT ' . implode(', ', $fields) . $from;
    $statement .= $this->getWhere($options) . $this->getOrder($options) . $this->getLimit($options);
    $result = $database->query($this->getStatement($statement), $parameters);
    $models = array();
    $modelIds = array();
    while(($row = $result->fetch_assoc()) !== null){
      list($id, $attributes) = $this->fetchAttributes($row, $this->model);
      if($id !== null && isset($modelIds[$id])){
	$model = $modelIds[$id];
      }else{
	$model = $this->getModel($attributes, $options['joins']);
	$models[] = $model;
	if($id !== null){
	  $modelIds[$id] = $model;
	}
      }
      $this->fetchRelations($model, $row, null, $options['joins'], null);
    }

    //
    return $models;
  }

  /*
   * ページ分割レコード取得
   */
  public function page($options=array(), $parameters=null){
    if(isset($options['page']) === false){
      $options['page'] = 1;
    }
    $models = $this->all($options, $parameters);
    $paginator = $this->paginator;
    $this->paginator = null;
    return array($models, $paginator);
  }

  /*
   * 単一レコード取得
   */
  public function one($options=array(), $parameters=null){
    $options['limit'] = '1';
    $models = $this->all($options, $parameters);
    if(empty($models)){
      return null;
    }
    return $models[0];
  }

  /*
   * モデル
   */
  public function newModel($attributes=array(), $joins=null){
    $modelClass = $this->model . 'Model';
    $model = new $modelClass($this, $attributes, true);
    return $this->addRelationAttributes($model, $joins);
  }
  public function getModel($attributes=array(), $joins=null){
    $modelClass = $this->model . 'Model';
    $model = new $modelClass($this, $attributes, false, true);
    return $this->addRelationAttributes($model, $joins);
  }

  /*
   * フィールド名
   */
  public function getFields(){
    return array_keys($this->fields);
  }
  public function getRelationFields(){
    return array_keys($this->relations);
  }

  /*
   * モデル用SQL
   */
  public function getSQLInsertFields(){
    $fields = array();
    $values = array();
    foreach($this->fields as $name=>$attributes){
      if(empty($attributes['autoIncrement'])){
	$fields[] = '`' . $name . '`';
	$values[] = ':' . $name;
      }
    }
    if(empty($fields)){
      return '';
    }
    return ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
  }
  public function getSQLUpdateFields(){
    $fields = array();
    foreach($this->fields as $name=>$attributes){
      if(empty($attributes['primaryKey'])){
	$fields[] = '`' . $name . '` = :' . $name;
      }
    }
    if(empty($fields)){
      return '';
    }
    return ' SET ' . implode(', ', $fields);
  }
  public function getSQLUpdateKeys(){
    $fields = array();
    foreach($this->fields as $name=>$attributes){
      if(empty($attributes['primaryKey']) === false){
	$fields[] = '`' . $name . '` = :' . $name;
      }
    }
    if(empty($fields)){
      return '';
    }
    return ' WHERE ' . implode(' AND ', $fields);
  }
  public function getSQLInsertId($value){
    foreach($this->fields as $name=>$attributes){
      if(empty($attributes['autoIncrement']) === false){
	return array($name=>$value);
      }
    }
    return array();
  }
  public function getSQLUpdateTable($database){
    return $database->getTableName($this->table);
  }

  /*
   * $fields
   */
  public function __get($name){
    if(strcmp($name, 'fields') == 0){
      /* プロパティ */
      if($this->__fields !== null){
	return $this->__fields;
      }

      /* データベース */
      $database = Eln_Database::getCurrentDatabase();
      $file = $this->app->cacheFile('models/'.$database->getName().'/'.$this->table.'.php');
      if($database->isCaching() === false || file_exists($file) === false){
	/* ディレクトリ */
	$this->app->createDirectory(dirname($file));

	/* フィールド設定キャッシュファイル */
	$statement = '<?php return array(';
	$result = $database->query(sprintf('show columns from %s', $database->getTableName($this->table)));
	while(($row = $result->fetch_assoc()) !== null){
	  $attributes = array();
	  $type = strtolower(preg_replace('/\([0-9,]+\)/', '', $row['Type']));
	  if(preg_match('/int$/', $type) || preg_match('/^bool/', $type)){
	    $attributes['type'] = 'integer';
	  }else if(strcmp($type, 'integer') == 0){
	    $attributes['type'] = 'integer';
	  }else if(strcmp($type, 'float') == 0 || strcmp($type, 'double') == 0){
	    $attributes['type'] = 'real';
	  }else if(strcmp($type, 'decimal') == 0 || strcmp($type, 'numeric') == 0){
	    $attributes['type'] = 'real';
	  }else if(strcmp($type, 'datetime') == 0 || strcmp($type, 'timestamp') == 0){
	    $attributes['type'] = 'datetime';
	  }else if(strcmp($type, 'date') == 0){
	    $attributes['type'] = 'date';
	  }else if(strcmp($type, 'time') == 0){
	    $attributes['type'] = 'time';
	  }else{
	    $attributes['type'] = 'string';
	  }
	  if(strcasecmp($row['Key'], 'PRI') == 0){
	    $attributes['primaryKey'] = true;
	  }else if(strcasecmp($row['Key'], 'UNI') == 0){
	    $attributes['uniqueKey'] = true;
	  }
	  if(strcasecmp($row['Extra'], 'auto_increment') == 0){
	    $attributes['autoIncrement'] = true;
	  }
	  $statement .= '\''.$row['Field'].'\'=>array(';
	  $statement .= '\'type\'=>\''.$attributes['type'].'\',';
	  if(isset($attributes['primaryKey'])){
	    $statement .= '\'primaryKey\'=>true,';
	  }
	  if(isset($attributes['autoIncrement'])){
	    $statement .= '\'autoIncrement\'=>true,';
	  }
	  $statement .= '),';
	}
	$statement .= ');';
	file_put_contents($file, $statement);
      }

      /* テーブルレイアウト */
      $this->__fields = include_once($file);

      /**/
      return $this->__fields;
    }

    /**/
    return null;
  }

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  protected function __construct(){
    //
    parent::__construct();

    // テーブル
    $this->__fields = null;
  }

  /* ===== ===== */

  /*
   * SELECT
   */
  private function getSelect($alias){
    $fields = array();
    foreach(array_keys($this->fields) as $field){
      $fields[] = '`' . $alias . '`.`' . $field . '` AS ' . $alias . '_' . $field;
    }
    return $fields;
  }
  private function getDistinct(){
    $keys = array();
    foreach($this->fields as $name=>$attributes){
      if(empty($attributes['primaryKey']) === false){
	$keys[] = '`' . $this->model . '`.`' . $name . '`';
      }
    }
    if(empty($keys)){
      return null;
    }
    if(count($keys) == 1){
      return $keys[0];
    }
    return 'CONCAT(' . implode(', \'.\', ', $keys) . ')';
  }

  /*
   * FROM
   */
  private function getFrom($database, $options){
    //
    $this->factories = array();
    $this->hasManyFlag = false;

    // FROM
    $this->from = ' FROM ' . $this->getTable($database, $this->model);
    if(isset($options['joins'])){
      $this->getJoins($database, $this, null, $options['joins']);
    }
    $from = $this->from;
    $this->from = null;

    //
    return $from;
  }
  private function getTable($database, $alias){
    return $database->getTableName($this->table) . ' AS ' . $alias;
  }

  /*
   * JOIN
   */
  private function getJoins($database, $factory, $alias, $joins){
    if(is_array($joins)){
      // JOINS配列走査
      foreach($joins as $index=>$join){
	if(is_int($index)){
	  $this->getJoins($database, $factory, $alias, $join);
	}else{
	  $this->getJoins($database, $factory, $alias, $index);
	  if(empty($alias)){
	    $slave = $index;
	  }else{
	    $slave = $alias . '_' . $index;
	  }
	  $factory->factories[$slave]->getJoins($database, $factory, $slave, $join);
	}
      }
    }else{
      // JOIN
      if(empty($alias)){
	$master = $this->model;
	$slave = $joins;
      }else{
	$master = $alias;
	$slave = $alias . '_' . $joins;
      }
      if(isset($this->relations[$joins]) === false){
	throw new Eln_Exception(_('relation for table join "{$1}" is not defined.'), $joins);
      }

      // リレーション
      $relation = $this->relations[$joins];
      if(strcasecmp($relation['type'], 'hasMany') == 0){
	$factory->hasManyFlag = true;
      }
      $model = $relation['model'];

      // モデルファクトリー
      $this->useModel($model);
      $propertyName = $model . 'Model';
      $factory->factories[$slave] = $this->$propertyName;

      // SQL
      $factory->from .= ' LEFT OUTER JOIN ' . $factory->factories[$slave]->getTable($database, $slave);
      if(is_string($relation['conditions'])){
	$factory->from .= ' USING(' . $relation['conditions'] . ')';
      }else{
	$js1 = array();
	$js2 = array();
	$using = true;
	foreach($relation['conditions'] as $index=>$statement){
	  $js1[] = $statement;
	  $js2[] = $slave . '.' . $statement . ' = ' . $master . '.' . $index;
	  if(is_string($index)){
	    $using = false;
	  }
	}
	if($using){
	  $factory->from .= ' USING(' . implode(', ' . $js1) . ')';
	}else{
	  $factory->from .= ' ON ' . implode(' AND ', $js2);
	}
      }
    }
  }

  /*
   * WHERE
   */
  private function getWhere($options){
    if(isset($options['where']) && strlen($options['where']) > 0){
      return ' WHERE ' . $options['where'];
    }
    return ' WHERE 1';
  }

  /*
   * ORDER
   */
  private function getOrder($options){
    if(isset($options['order']) && strlen($options['order']) > 0){
      return ' ORDER BY ' . $options['order'];
    }
    return '';
  }

  /*
   * LIMIT
   */
  private function getLimit($options){
    if(isset($options['limit']) && strlen($options['limit']) > 0){
      return ' LIMIT ' . $options['limit'];
    }
    return '';
  }

  /*
   * [...]
   */
  private function getStatement($statement){
    return preg_replace('/\[([a-zA-Z][_0-9a-zA-Z]*)\]/', '`' . $this->model . '`.`$1`', $statement);
  }

  /*
   * FETCH
   */
  private function fetchAttributes($row, $alias){
    $id = null;
    $results = array();
    foreach($this->fields as $name=>$attributes){
      $field = $alias . '_' . $name;
      if(array_key_exists($field, $row)){
	if(empty($attributes['primaryKey']) === false && $row[$field] === null){
	  return null;
	}
	if($row[$field] === null){
	  $results[$name] = null;
	}else if(isset($attributes['type'])){
	  if(strcasecmp($attributes['type'], 'integer') == 0){
	    $results[$name] = intval($row[$field]);
	  }else if(strcasecmp($attributes['type'], 'real') == 0){
	    $results[$name] = floatval($row[$field]);
	  }else if(strcasecmp($attributes['type'], 'datetime') == 0 ||
		   strcasecmp($attributes['type'], 'date') == 0){
	    $results[$name] = new Eln_Date(strtotime($row[$field]));
	    if(strcasecmp($attributes['type'], 'date') == 0){
	      $results[$name]->zero();
	    }
	  }else if(strcasecmp($attributes['type'], 'time') == 0){
	    $results[$name] = new Eln_Date(strtotime($row[$field]), false);
	  }else{
	    $results[$name] = $row[$field];
	  }
	}else{
	  $results[$name] = $row[$field];
	}
	if(empty($attributes['primaryKey']) === false){
	  if($id === null){
	    $id = $results[$name];
	  }else{
	    $id .= '-' . $results[$name];
	  }
	}
      }
    }
    return array($id, $results);
  }
  private function fetchRelations($model, $row, $alias, $primary, $secondary){
    if(is_array($primary)){
      // JOINS配列走査
      foreach($primary as $index=>$entry){
	if(is_int($index)){
	  $this->fetchRelations($model, $row, $alias, $entry, null);
	}else{
	  if(($m = $this->fetchRelations($model, $row, $alias, $index, $entry)) !== null){
	    if(empty($alias)){
	      $slave = $index;
	    }else{
	      $slave = $alias . '_' . $index;
	    }
	    $this->fetchRelations($m, $row, $slave, $entry, null);
	  }
	}
      }
      return null;
    }

    // JOIN
    if(empty($alias)){
      $slave = $primary;
      $masterFactory = $this;
    }else{
      $slave = $alias . '_' . $primary;
      $masterFactory = $this->factories[$alias];
    }
    $slaveFactory = $this->factories[$slave];
    if((list($id, $attributes) = $slaveFactory->fetchAttributes($row, $slave)) !== null){
      if(($related = $model->getRelation($primary, $id)) === null){
	if(strcasecmp($masterFactory->relations[$primary]['type'], 'hasMany') == 0){
	  $related = $slaveFactory->getModel($attributes, $secondary);
	  $relatedModels = $model->$primary;
	  $relatedModels[] = $related;
	  $model->$primary = $relatedModels;
	}else{
	  $related = $slaveFactory->getModel($attributes, $secondary);
	  $model->$primary = $related;
	}
	$model->addRelation($primary, $id, $related);
      }
      return $related;
    }
    return null;
  }

  /*
   * RELATION
   */
  private function addRelationAttributes($model, $joins){
    if($joins !== null){
      if(is_array($joins)){
	foreach($joins as $index=>$join){
	  if(is_int($index)){
	    $this->addRelationAttribute($model, $join);
	  }else{
	    $this->addRelationAttribute($model, $index);
	  }
	}
      }else{
	$this->addRelationAttribute($model, $joins);
      }
    }
    return $model;
  }
  private function addRelationAttribute($model, $join){
    if(isset($this->relations[$join])){
      if(strcasecmp($this->relations[$join]['type'], 'hasMany') == 0){
	$model->$join = array();
      }else{
	$model->$join = null;
      }
    }
  }
}


/*
 * モデルクラス
 */
abstract class Eln_Model extends Eln_Object {
  /*
   * プロパティ
   */
  protected $factory;
  /**/
  private $newFlag;
  private $deleteFlag;
  private $relations;

  /* ===== ===== */

  /*
   * コンストラクタ
   */
  public function __construct($factory, $attributes, $newFlag, $fetched=false){
    //
    parent::__construct();

    // モデルファクトリー
    $this->factory = $factory;

    // 属性
    foreach($this->factory->getFields() as $field){
      $this->$field = null;
    }
    foreach($this->additionalAttributes() as $field){
      $this->$field = null;
    }
    $this->setAttributes($attributes);

    // フラグ
    $this->newFlag = $newFlag;
    $this->deleteFlag = false;

    // リレーション
    $this->relations = array();

    // コールバック
    if($fetched){
      $this->afterDatabase($this->newFlag);
    }
  }

  /* ===== ===== */

  /*
   * モデル属性を取得する
   */
  final public function getAttributes(){
    $attributes = array();
    foreach($this->factory->getFields() as $field){
      if(isset($this->$field)){
	$attributes[$field] = $this->$field;
      }else{
	$attributes[$field] = null;
      }
    }
    foreach($this->additionalAttributes() as $field){
      if(isset($this->$field)){
	$attributes[$field] = $this->$field;
      }else{
	$attributes[$field] = null;
      }
    }
    foreach($this->factory->getRelationFields() as $field){
      if(isset($this->$field)){
	$object = $this->$field;
	if(is_array($object)){
	  $objects = $object;
	  $attributes[$field] = array();
	  foreach($objects as $object){
	    if($object instanceof Eln_Model){
	      $attributes[$field][] = $object->getAttributes();
	    }
	  }
	}else if($object instanceof Eln_Model){
	  $attributes[$field] = $object->getAttributes();
	}
      }
    }
    return $attributes;
  }

  /*
   * モデル属性を設定する
   */
  final public function setAttributes($attributes){
    // 属性を設定
    foreach($this->factory->getFields() as $field){
      if(array_key_exists($field, $attributes)){
	$this->$field = $attributes[$field];
      }
    }
    foreach($this->additionalAttributes() as $field){
      if(array_key_exists($field, $attributes)){
	$this->$field = $attributes[$field];
      }
    }
  }

  /*
   * レコードを保存する
   */
  final public function save(){
    // 状態を検査
    if($this->deleteFlag){
      throw new Eln_Exception(_('failed to save record. ({$1})'), _t_('this record is already deleted.'));
    }

    // コールバック
    $this->beforeDatabase($this->newFlag);

    // 接続
    $database = Eln_Database::getCurrentDatabase();

    // SQL
    if($this->newFlag){
      $statement  = 'INSERT INTO ' . $this->factory->getSQLUpdateTable($database);
      $statement .= $this->factory->getSQLInsertFields();
    }else{
      $statement  = 'UPDATE ' . $this->factory->getSQLUpdateTable($database);
      $statement .= $this->factory->getSQLUpdateFields();
      $statement .= $this->factory->getSQLUpdateKeys();
    }
    $database->query($statement, $this->getAttributes());
    //
    if($this->newFlag){
      $this->setAttributes($this->factory->getSQLInsertId($database->getInsertId()));
    }

    // コールバック
    $this->afterDatabase($this->newFlag);

    // フラグ
    $this->newFlag = false;
  }

  /*
   * レコードを削除する
   */
  final public function delete(){
    // 状態を検査
    if($this->newFlag || $this->deleteFlag){
      throw new Eln_Exception(_('failed to delete record. ({$1})'), _('this record does not exist.'));
    }

    // 接続
    $database = Eln_Database::getCurrentDatabase();

    // SQL
    $statement  = 'DELETE FROM ' . $this->factory->getSQLUpdateTable($database);
    $statement .= $this->factory->getSQLUpdateKeys();
    $database->query($statement, $this->getAttributes());

    // フラグ
    $this->deleteFlag = true;
  }

  /*
   * リレーション
   */
  final public function getRelation($name, $id){
    if(isset($this->relations[$name][$id])){
      return $this->relations[$name][$id];
    }
    return null;
  }
  final public function addRelation($name, $id, $model){
    if(isset($this->relations[$name]) === false){
      $this->relations[$name] = array();
    }
    $this->relations[$name][$id] = $model;
  }

  /* ===== ===== */

  /*
   * データベース書き込み前処理を実行する
   */
  protected function beforeDatabase($isNew){}

  /*
   * データベース読み込み後処理を実行する
   */
  protected function afterDatabase($isNew){}

  /*
   * 追加属性
   */
  protected function additionalAttributes(){
    return array();
  }
}
