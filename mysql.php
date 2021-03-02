<?php
include_once 'init.php';

//mysql工具类
class MySqlTool{
	public $conn;
	/**
	*连接数据库 
	*/
	public function __construct($sqlpath){	
		@$this->conn= mysqli_connect ($GLOBALS['servername'], 
									$GLOBALS['username'], 
									$GLOBALS['password']);
		mysqli_options($this->conn,MYSQLI_OPT_INT_AND_FLOAT_NATIVE,true);
		if ($this->conn->connect_error) {
			$this->conn=null;
		}else{
			$result = mysqli_query($this->conn,"SHOW DATABASES LIKE '".$GLOBALS['mydata']."'");
			$row = mysqli_fetch_assoc($result);
			if($row == null || sizeof($row) == 0){
				$sql = "CREATE DATABASE ".$GLOBALS['mydata'];
				if ($this->conn->query($sql)) {
					mysqli_select_db( $this->conn ,$GLOBALS['mydata']);
					$_sql = file_get_contents($sqlpath);
					$_arr = explode(';', $_sql);
					foreach ($_arr as $_value){
						if($_value != "" && $_value!=null && $_value != " " && $_value != ";" ){
							if(!$this->conn->query($_value.';'))
								print("<br><br>数据库执行失败--->".$_value);
						}
					}     
				} else exit( "Error creating database: " .$this->conn->error);
			}else mysqli_select_db( $this->conn ,$GLOBALS['mydata']);
			
		}
	}
	
	
	// 获取内容 + 背景图片
	public function get_content($type){
		
		if($this->conn == null)
			return false;
		/* 
		$result_1 = mysqli_query($this->conn,'SELECT * 
									FROM text AS t1 JOIN (SELECT ROUND(RAND() * (SELECT MAX(id) FROM text)) AS id) AS t2  
									WHERE t1.id >= t2.id 
									AND t1.type = '.$type.' 
									ORDER BY t1.id ASC LIMIT 1;'); */
		$result_1 = mysqli_query($this->conn,'SELECT * FROM text WHERE type = '.$type);
		//$row_1 =mysqli_fetch_assoc($result_1);//查询
		$arr_1 = array();
		while( $row_1 = mysqli_fetch_assoc($result_1) ){
			array_push($arr_1,$row_1);
		}
		/* 
		print_r($arr_1);
		return; */
		
		
		
		
		/* $result_2 = mysqli_query($this->conn,'SELECT * 
									FROM image AS t1 JOIN (SELECT ROUND(RAND() * (SELECT MAX(id) FROM image)) AS id) AS t2 
									WHERE t1.id >= t2.id 
									AND t1.type = '.$type.' 
									ORDER BY t1.id ASC LIMIT 1;'); */
		$result_2 = mysqli_query($this->conn,'SELECT * FROM image WHERE type = '.$type);
		//$row_2 =mysqli_fetch_assoc($result_2);//查询 
		$arr_2 = array();
		while( $row_2 = mysqli_fetch_assoc($result_2) ){
			array_push($arr_2,$row_2);
		}
		
		
		//print_r( $row_1);
		$this->set_sqlend();
		
		$trun_1 = $arr_1[rand(0,sizeof($arr_1))]['content'];
		if ($arr_1==null||$arr_1==""||sizeof($arr_1)<=0) {
			$trun = null;
		}
		$trun_2 = $GLOBALS['image_path'].$arr_2[rand(0,sizeof($arr_2))]['image'];
		if($arr_2==null||$arr_2==""||sizeof($arr_2)<=0){
			$trun_2 = null;
		}
		return array('context'=>$trun_1,'path'=>$trun_2);
	}
	
	
	/**
	 * 判断是否有这张图片
	 * @param {Object} $type 类型
	 * @param {Object} $md5 MD5值加后缀
	 * return 0 = 数据库连接失败   1 = 有图片但类型不同（更新数据） 2 = 有图片,类型也相同（不导入）   3 = 没有（插入） 
	 */
	public function is_image($type,$md5){
		if($this->conn == null) return 0;
		$result = mysqli_query($this->conn,"SELECT type,image FROM image WHERE image='".$md5."'");
		$row = mysqli_fetch_assoc($result);//查询
		if( $row != null && sizeof($row) >= 1 ){
			$rs = $row['type'] == null ? 0 : $row['type'];
			if($type != $rs)
				return 1;
			return 2;
		}/* else if($row != null && sizeof($row) > 1 ){
			return 2;
		} */
		return 3;
	}
	
	
	
	
	/**
	 * 添加图片
	 * @param {Object} $type 类型
	 * @param {Object} $md5 MD5值加后缀
	 * return   -2 = 更新失败 ， -1 = 导入失败 ， 0 = 数据库连接失败 ，
			    1 = 导入成功  ，2 = 已有数据 ， 3 = 更新成功 
	 */
	public function insert_image($type,$md5){
		$rt = $this->is_image($type,$md5);
		//echo $rt."<br>";
		if ( $rt == 2 || $rt == 0) 
			return $rt;
		if ($rt == 3) {
			$sql = "INSERT INTO image (type, image) VALUES (".$type.", '".$md5."')";
			if(mysqli_query($this->conn,$sql)) 
				return 1;
			return -1;
		}else if($rt == 1){
			$sql = "UPDATE image SET type=".$type." WHERE image='".$md5."'";
			if(mysqli_query($this->conn,$sql))
				return 3;
			return -2;
		}
	}
	
	
	





	/**
	 * 判断是否有这句话
	 * @param {Object} $type 类型
	 * @param {Object} $text 这句话
	 * return  0 = 数据库连接失败   1 = 有这句话但类型不同（更新数据） 2 = 有这句话,类型也相同（不导入）   3 = 没有（插入） 
	 */
	public function is_text($type,$text){
		if($this->conn == null) return 0;
		$result = mysqli_query($this->conn,"SELECT type,content FROM text WHERE content='".$text."'");
		$row = mysqli_fetch_assoc($result);//查询
		if( $row != null && sizeof($row) >= 1 ){
			$rs = $row['type'] == null ? 0 : $row['type'];
			if( $rs != $type)
				return 1;
			return 2;
		}
		return 3;
	}
	
	
	
	
	
	
	
	/**
	 * 添加这句话
	 * @param {Object} $type 类型
	 * @param {Object} $text 这句话
	 * return 	-2 = 更新失败 ， -1 = 导入失败 ， 0 = 数据库连接失败 ，
				1 = 导入成功 ，2 = 已有这条数据 ，3 = 更新成功 
	 */
	public function insert_text($type,$text){
		$rt = $this->is_text($type,$text);
		if ( $rt == 2 || $rt == 0) return $rt;
		if ($rt == 3) {
			$sql = "INSERT INTO text( type, content) VALUES (".$type.", '".$text."')";
			if(mysqli_query($this->conn,$sql)) 
				return 1;
			return -1;
		}else if($rt == 1){
			$sql = "UPDATE text SET type=".$type." WHERE content='".$text."'";
			if(mysqli_query($this->conn,$sql))
				return 3;
			return -2;
		}
	}
	
	
	/**
	 * 删除一条数据 
	 * @param {Object} $str 数据
	 * @param {Object} $f 是否为图片数据
	 */
	public function delet($str,$f = true ){
		if($this->conn == null) return false;
		$sql = "DELETE FROM image WHERE image = '".$str."'";
		if(!$f)
			$sql = "DELETE FROM text WHERE content = '".$str."'";
		return mysqli_query($this->conn,$sql);
	}
	
	
	//关闭连接	
	public function set_sqlend(){
		mysqli_close($this->conn);
	}
	
		
}



?>