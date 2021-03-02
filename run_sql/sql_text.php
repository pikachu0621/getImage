<?php  
 include_once '../init.php';
 include_once '../mysql.php';
 ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>图片批量导入</title>
		

	</head>
		<body>
			<b> 文字批量导入  </b> <br>
			<font> 只支持本地导入（相对路径，绝对路径，都可以）</font><br><br>
			<form action="sql_text.php" method="get">
				文件路径: <input type="text" style="width: 100%; height:26px;" name="path" value="night_mooring_text/mooring.txt"><br>
				分割符: <input type="text" style="width: 100%; height:26px;" name="rgr" value="\n" ><br>
				超出多少字的不导入: <input type="number" style="width: 100%; height:26px;" name="nmr" value="<?php echo $GLOBALS['text_length'];  ?>" ><br>
				<input type="radio" name="type" value="0" checked >导入成早安
				<input type="radio" name="type" value="1">导入成晚安<br>
				<input type="checkbox" name="delelt" value="1" /> 导入完毕删除这句话<br><br>
				<input type="submit" style="width: 100%; height:30px;" value="开始导入"><br/><br/>
			</form>
		</body>
	
<html>




<?php
/**
 * 图片批量导入数据库
 * 直接运行此代码即可
 */

$path =  $_GET['path'];
$type =  $_GET['type'] == null ? 0 : $_GET['type'];
$rgr =  $_GET['rgr'] == null ? "\n" : $_GET['rgr'];
$nmr =  $_GET['nmr'] == null ? $GLOBALS['text_length'] : $_GET['nmr'];
$delelt =  $_GET['delelt'] == null ? 0 : $_GET['delelt'];

if( $_GET['type'] == null)
	return;
	
if($path == null || $path == ''){
	to_html("无效路径","#f00");
	return;
}


//echo $path.'<br>'.$type.'<br>'.$rgr.'<br>'.$nmr.'<br>'.$delelt.'<br><br><br>';
//return;

$sql_tool = new MySqlTool('../sql/'.$GLOBALS['mydata'].'.sql');
list_file($path);
$_GET['path'] = null;
$path = null;

function list_file($date){
	
	
	global $type;
	global $delelt;
	global $rgr;
	global $nmr;
	global $sql_tool;
	
	if( !file_exists ($date) ){
		to_html('文件不存在',"#f00");
		return;
	}
	/* echo $date.'<br>'.$type.'<br>'.$rgr.'<br>'.$nmr.'<br>'.$delelt.'<br>';
	return; */
	chmod($date,0777);
	$flie_context = file_get_contents($date);
	$flie_context = str_replace("\n","[pk]",$flie_context);
	$rgr = str_replace('\n',"[pk]",$rgr);
	$flie_context_one = explode($rgr , $flie_context);
	
	
	$rf = $flie_context;
	$cg = 0;
	$sb = 0;
	$gx = 0;
	
	foreach ($flie_context_one as $_value){
		
		/* $strlen = mb_strlen($_value,'utf8');
		echo 'UU---'.$strlen."----".$_value."<br>";
		continue; */
		
		$strlen = mb_strlen($_value,'utf8');
		if($strlen <= 1 || $_value == "" || $_value == ' ' ) continue;
		
		$ass = str_split_unicode($_value, 12);
		$strr =  $strlen > 12 ? $ass[0].'....' :$_value ;
		
		if( mb_strlen($_value,'utf8') <= $nmr){
			$re =  $sql_tool->insert_text($type,$_value);
			if($re == -2){
				to_html($strr.' ---> 更新失败',"#F00");
				$sb ++;
			}else if($re == -1){
				to_html($strr.' ---> 导入失败',"#F00");
				$sb ++;
			}else if($re == 0){
				to_html(' ---> 数据库连接失败 <---',"#F00");
				$sb ++;
			}else if($re == 1){
				if($delelt)
					$rf = str_replace($_value.$rgr,"",$rf);
				to_html($strr.' ---> 导入成功',"#0F0");
				$cg ++;
			}else if($re == 2){
				to_html($strr.' ---> 已存在',"#F90");
				$sb ++;
			}else if($re == 3){
				to_html($strr.' ---> 更新成功',"#00F");
				$gx ++;
			}
		}else {
			to_html($strr . ' ---> 超出'.$nmr.'字',"#f00");
			$sb ++;
		}
	}
	
	if($delelt){
		$rf = str_replace("[pk]","\n",$rf);
		file_put_contents($date, $rf);
	}
	
	
	
	to_html( '<br><br>[本次导入'.$cg.'条&emsp;&emsp;更新'.$gx.'条&emsp;&emsp;失败'.$sb.'条]',"#000");
		
	
	//导入完成是否删除
	/* if($delelt){
		chmod($date,0777); 
		unlink($date);
	} */
}


function to_html($msg,$color = "#000"){
	echo '<font color="'.$color.'" >'.$msg.'</font><br/>';
}


/**
 * 分割字符串
 * @param {Object} $str 字符串
 * @param {Object} $l 几个
 */
function str_split_unicode($str, $l = 0) {
	if ($l > 0) {
		$ret = array();
		$len = mb_strlen($str, "UTF-8");
		for ($i = 0; $i < $len; $i += $l) {
			$ret[] = mb_substr($str, $i, $l, "UTF-8");
		}
		return $ret;
	}
	return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}


?>