<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>图片批量导入</title>
		

	</head>
		<body>
			<b> 图片批量导入  </b> <br>
			<font> 只支持本地导入（相对路径，绝对路径，都可以）</font><br>
			<a href="https://wallhaven.cc/search?q=morning"> 早晨图片 </a>&emsp;&emsp;
			<a href="https://wallhaven.cc/search?q=night"> 夜晚图片 </a><br><br>
			
			<form action="sql_image.php" method="get">
				文件夹名: <input type="text" style="width: 100%; height:26px;" name="path" value="mooring_img"><br>
				<input type="radio"  name="type" value="0" checked >导入成早安
				<input type="radio" name="type" value="1">导入成晚安<br>
				<input type="checkbox" name="delelt" value="1" /> 删除已导入图片<br><br>
				<!-- checked="checked" -->
				<input type="submit" style="width: 100%; height:30px;" value="开始导入"><br><br>
			</form>
		</body>
	
<html>




<?php
/**
 * 图片批量导入数据库
 * 直接运行此代码即可
 */
include_once '../init.php';
include_once '../mysql.php';


$path =  $_GET['path'];
$type =  $_GET['type'] == null ? 0 : $_GET['type'];
$delelt =  $_GET['delelt'] == null ? 0 : $_GET['delelt'];


if( $_GET['type'] == null)
	return;

if($path == null || $path == ''){
	to_html("无效路径","#f00");
	return;
}
$sql_tool = new MySqlTool('../sql/'.$GLOBALS['mydata'].'.sql');
list_file($path);
$_GET['path'] = null;
$path = null;
$_GET['type'] = 0;
$type = 0;

function list_file($date){
	
    $temp=scandir($date);
	foreach($temp as $v){
		$a=$date.'/'.$v;
		if(is_dir($a)){
			if($v=='.' || $v=='..') continue;
			list_file($a);
		}else{
			//文件类型
			$type_end = substr(strrchr($a, "."), 1);
			if($type_end == 'jpg' || $type_end == 'png' || $type_end == 'webp' || $type_end == 'jpeg' || $type_end == 'gif'){
				
				global $type;
				global $delelt;
				global $sql_tool;
				
				$md5 = md5_file($a);
				$md5s = $md5.'.'.$type_end;
				$file_name = substr(strrchr($a, "/"), 1);
	
				//echo $type."<br>";
				$rt = $sql_tool->insert_image($type,$md5s);
				if($rt == 2){
					to_html($file_name.' ---> 已存在',"#F90");
				}else if($rt == -2){
					to_html($file_name.' ---> 更新失败',"#F00");
				}else if($rt == -1){
					to_html($file_name.' ---> 导入失败',"#F00");
				}else if($rt == 0){
					to_html('---> 数据库连接失败 <---',"#F00");
				}else if($rt == 1){
					//数据库导入完成
					if(copy($a,'../'.$GLOBALS['image_path'].$md5s)){
						//导入完成是否删除
						if($delelt){
							chmod($a,0777); 
							unlink($a);
						}
						to_html($file_name.' ---> 完成',"#000");
					}else{
						$sql_tool->delet($md5s);
						to_html($file_name. '---> 图片复制失败',"#F00");
					}
				}else if($rt == 3){
					to_html($file_name.' ---> 更新成功',"#00F");
				}
				
			}
        }
    }
}

function to_html($msg,$color = "#000"){
	echo '<font color="'.$color.'" >'.$msg.'</font><br/>';
}


?>