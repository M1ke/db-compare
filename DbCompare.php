<?php
class DbCompare {
	protected $sql_server;
	protected $sql_user;
	protected $sql_pass;
	protected $dbs=[];

	function __construct($sql_server,$sql_user,$sql_pass){
		$this->sql_server=$sql_server;
		$this->sql_user=$sql_user;
		$this->sql_pass=$sql_pass;
	}

	public function addDb($db_name){
		$this->dbs[$db_name]=new PDO('mysql:host='.$this->sql_server.';dbname='.$db_name,$this->sql_user,$this->sql_pass);
		return $this;
	}

	public function compare($table,Array $keys,$primary_key='id'){
		$db_rows=$this->getRows($table,$keys,$primary_key);

		$key_items=[];
		foreach ($db_rows as $db_name =>$rows){
			foreach ($rows as $row){
				foreach ($keys as $key){
					$key_items[$row[$primary_key]][$key][$db_name]=$row[$key];
				}
			}
		}

		$diff_as_table=$diff_as_array=$new_rows=[];
		foreach ($key_items as $row_id => $row){
			foreach ($row as $key => $vals){
				if (count($vals)>1){
					if (!self::_array_same_values($vals)){
						foreach ($vals as $db_name => $val){
							$diff_as_table[$row_id][$key.'-'.$db_name]=$val;
						}
						$diff_as_array[$row_id][$key]=$vals;
					}
				}
				else {
					$new_rows[$row_id]=true;
				}
			}
		}
		foreach ($diff_as_table as $row_id => $row){
			$row = array_reverse($row, true); 
		    $row[$primary_key] = $row_id; 
		    $diff_as_table[$row_id]=array_reverse($row, true); 
		}
		return ['table'=>$diff_as_table,'array'=>$diff_as_array,'new'=>$new_rows];
	}

	protected function getRows($table,Array $keys,$primary_key='id'){
		$query_keys=!empty($keys) ? ',`'.implode('`,`',$keys).'`' : '';
		foreach ($this->dbs as $db_name => $db){
			$statement=$db->query("SELECT `$primary_key` $query_keys FROM `$table`");
			$db_rows[$db_name]=$statement->fetchAll(PDO::FETCH_ASSOC);
			echo $db_name.': '.count($db_rows[$db_name]).PHP_EOL;
		}
		return $db_rows;
	}

	final public static function _array_same_values(Array $arr){
		foreach ($arr as $key => $val){
			if (isset($last_val) and $val!=$last_val){
				return false;
			}
			$last_val=$val;
		}
		return true;
	}
}
