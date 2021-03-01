<?php
include_once 'init.php';
require_once 'vendor/autoload.php';
include_once 'mysql.php';
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;


out_image($_REQUEST['img'],$_REQUEST['w'],$_REQUEST['h'],
		  $_REQUEST['t1'],$_REQUEST['t2'],$_REQUEST['t3'],
		  $_REQUEST['g1'],$_REQUEST['g2'],
		  $_REQUEST['mr'],$_REQUEST['na'],
		  $_REQUEST['cc'],$_REQUEST['cl'],$_REQUEST['ct'],$_REQUEST['ca']);
	

function out_image(
	$img,
	$width , $height ,
	
	$text1 , $text2, $text3,
	$img_gs_1 , $img_gs_2,
	$mooring_ratio, $night_atb,
	
	$code_content, $code_log_img,
	$code_text , $code_atb){
		
	
	
	if ($GLOBALS['code_open']) {
		$code_content = $code_content == null || $code_content == '' ? $GLOBALS['code_content'] : $code_content;
		$code_log_img = $code_log_img == null || $code_log_img == '' ? $GLOBALS['code_log_img'] : $code_log_img;
		$code_text = $code_text == null || $code_text == '' ? $GLOBALS['code_text'] : $code_text;
		$code_atb = $code_atb == null  ? $GLOBALS['code_atb'] : $code_atb;
	}
	
	$sql_tool =  new MySqlTool();
	ob_start(); //启用输出缓冲
	mb_internal_encoding("UTF-8");
	$h=date('H');
	if($h >= $GLOBALS['date_mooring']['start'] && $h <= $GLOBALS['date_mooring']['stop']){
		
		
		
		
		$arr = $sql_tool->get_content(0);
		/* print_r($arr);
		return; */
		$img = $img == null || $img == '' ? ($arr['path'] == null || $arr['path'] == '' ?  $GLOBALS['mooring_img'] : $arr['path'] ) : $img;
		$text3 = $text3 == null ||  $text3 == '' ? ($arr['context'] == null || $arr['context'] == '' ?  $GLOBALS['mooring_text2'] : $arr['context'] ) :  $text3;
		//$img = ($img == null || $img == '') && $arr[1] !=null && $arr[1] !=null   ?
		
		
		$width = $width == null || $width == 0 ? $GLOBALS['mooring_w'] : $width;
		$height =  $height == null ||  $height == 0 ? $GLOBALS['mooring_h'] : $height;
		$img_gs_1 = $img_gs_1 == null  ? $GLOBALS['mooring_img_gs'] : $img_gs_1;
		$img_gs_2 = $img_gs_2 == null  ? $GLOBALS['mooring_img_mgs'] : $img_gs_2;
		$mooring_ratio = $mooring_ratio == null ? $GLOBALS['mooring_ratio'] : $mooring_ratio;
		$text1 = $text1 == null || $text1 == '' ? $GLOBALS['mooring_text1'] : $text1;
		
		
		

		
		
		
		$newimg = good_mooring($img,$width,$height,
			$img_gs_1,$img_gs_2,$mooring_ratio,$text1,$text3,
			$code_content, $code_log_img ,$code_text,$code_atb);
	}else{
		
		$arr = $sql_tool->get_content(1);
		$img = $img == null || $img == '' ? ($arr['path'] == null || $arr['path'] == '' ?  $GLOBALS['night_img'] : $arr['path'] ) : $img;
		$text3 = $text3 == null ||  $text3 == '' ? ($arr['context'] == null || $arr['context'] == '' ? $GLOBALS['night_text3'] : $arr['context'] ) :  $text3;
		
		//$img = $img == null || $img == '' ? $GLOBALS['night_img'] : $img;
		$width = $width == null || $width == 0 ? $GLOBALS['night_w'] : $width;
		$height =  $height == null ||  $height == 0 ? $GLOBALS['night_h'] : $height;
		$img_gs_1 = $img_gs_1 == null  ? $GLOBALS['night_img_gs'] : $img_gs_1;
		$night_atb = $night_atb == null ? $GLOBALS['night_atb'] : $night_atb;
		$text1 = $text1 == null || $text1 == '' ? $GLOBALS['night_text1'] : $text1;
		$text2 = $text2 == null || $text2 == '' ? $GLOBALS['night_text2'] : $text2;
		//$text3 = $text3 == null || $text3 == '' ? $GLOBALS['night_text3'] : $text3;
		
		$newimg = good_night(
			$img, $width,$height, 
			$img_gs_1,$night_atb,
			$text1,$text2,$text3,
			$code_content, $code_log_img ,$code_text,$code_atb);
	}
	header('Content-Type: image/Jpeg');
	imageJpeg($newimg);
	imagedestroy($newimg);
} 


//合成晚安图片
function good_night(
	//图片配置
	$imgSrc = 'image/p.jpg',  //图片src （支持url）
	$resize_width = 480, //图片宽 （最好高清）
	$resize_height = 800, //图片高（最好高清） 高度小于800最好别用二维码
	$gs = 0, //图片模糊度  （0~10）
	$top_a = 0, //上下白边透明度  （0~127）
	$text_1 = "   晚\n安", // 晚安问候语		 （这里最好别改，同样需要字体支持）
	$text_2 = "GOOD NIGHT", // "GOOD NIGHT"
	$text_3 = "晚安打工人", //励志文字 （多余文字会自动丢弃）
	
	
	//二维码配置
	$logContext = null ,// 二维码内容  （null 不添加）
	$logImage = null,// 二维码中间log图片 （null 不添加）
	$logText = null,// 二维码下方文字 （null 不添加）
	$logArg = 100){ // 二维码 透明度 （0~100  数越大越不透明）
		
		//比例
		$px = ($resize_width + $resize_height) * 0.01;
		
		//图片剪切
		$newimg = xq_image($imgSrc, $resize_width, $resize_height);
		
		//图片主色调/反色
		$rgb = getRgb($newimg);
		$rgbH =  getBackRgb($rgb);
		
		
		$newimg = blur($newimg, $gs);//图片高斯模糊
		
		
		//画矩形
		//上下两个
		$lin_color = imagecolorallocatealpha($newimg,255,255,255,$top_a);
		$lin_h = $px * 6;
		imagefilledrectangle($newimg,0,0,$resize_width,$lin_h, $lin_color);//画矩形
		$lin_hh = $lin_h * 3.6;
		$lin_y = $resize_height - $lin_hh;
		imagefilledrectangle($newimg,0,$lin_y,$resize_width,$resize_height, $lin_color);//画矩形
		
		
		
		//定义日期
		$a_w = $lin_h * 1.2;
		$a_h = $a_w;
		$a_x =$lin_h/2; // $resize_width - $a_w - $lin_h / 1.4    靠右
		$a_y = $lin_h / 2.8;
		$a_him_w = $px * 0.3;
		draw_rectangle($newimg,$a_x,$a_y,$a_w,$a_h,
		                ['r'=>255,'g'=>255,'b'=>255,'a'=>40],
		                $a_him_w,
		                $rgb);
		imagefilledrectangle($newimg,$d_x,$d_y,$d_x + $d_w,$d_y + $d_h,$f_color);//画矩形
		$d_size =$a_w * 0.2;
		$d_ttf = $GLOBALS['ttf_week'];
		$d_color = ImageColorAllocate ($newimg,$rgb['r'], $rgb['g'], $rgb['b']); //字体颜色
		
		$d_text_1 = getWeek();
		$box_array_1 = imagettfbbox($d_size , 0, $d_ttf, $d_text_1);
		$d_text_2 = date("m/d");
		$box_array_2 = imagettfbbox($d_size , 0, $d_ttf, $d_text_2);
		
		$dd_margin = $px * 0.4;//中间间隔； 
		$dd_x = $a_x + ($a_w - ($box_array_1[2] - $box_array_1[0]))/2;//星期x居中
		$dd_y = $a_y + ($box_array_1[3] - $box_array_1[5]) + ($a_h - (($box_array_2[3] - $box_array_2[5]) + ($box_array_1[3] - $box_array_1[5])))/2;//y居中考虑日期
		imagettftext($newimg, $d_size, 0, $dd_x, $dd_y - $dd_margin, $d_color, $d_ttf, $d_text_1);
		$dd_xx = $a_x + ($a_w - ($box_array_2[2] - $box_array_2[0]))/2;//日期x居中;
		$dd_yy = $dd_y + ($box_array_1[3] - $box_array_1[5])  ;//y居中考虑星期
		imagettftext($newimg, $d_size, 0, $dd_xx, $dd_yy + $dd_margin, $d_color, $d_ttf, $d_text_2);
		
		
		
		
		//定义晚安字体
		$f_size = $px * 5;
		$f_y = $lin_y + $f_size + $px * 2;
		$f_ttf = $GLOBALS['ttf_greet'][rand(0,sizeof($GLOBALS['ttf_greet'])-1)];//3种字体随机
		$f_color = ImageColorAllocate ($newimg,$rgb['r'], $rgb['g'], $rgb['b']); //字体颜色
		//居中
		$d_w = $px * 14; //good night 矩形宽
		$box_w = $d_w * 1.1;//励志文字宽
		$box_array_a = imagettfbbox($f_size , 0, $f_ttf, '安');
		$f_x = ($resize_width - ($box_w + $box_array_a[2]))/2 - $px;
		error_log($f_x,3,"logs/log.txt");
		imagettftext($newimg, $f_size  , 0, $f_x , $f_y, $f_color, $f_ttf, $text_1);
		
		
		
		//good night 字体
		$d_x = $f_x + $px * 7;
		$d_y = $f_y + $px * 1.6;
		$d_h = $px * 2.1;
		imagefilledrectangle($newimg,$d_x,$d_y,$d_x + $d_w,$d_y + $d_h,$f_color);//画矩形
		$d_size = $px * 1.4;
		$d_text = $text_2;
		$d_ttf = $GLOBALS['ttf_content'];
		$d_color = ImageColorAllocate ($newimg,255,255,255); //字体颜色
		$box_array = imagettfbbox($d_size , 0, $d_ttf, $d_text);
		//imagettftext($newimg, $d_size  , 0,$d_x + ($d_w - ($box_array[2] - $box_array[0]))/2 , $d_y + ($d_h  + ($box_array[3] - $box_array[5]))/2  , $d_color, $d_ttf, $d_text);
		imagettftext($newimg, $d_size  , 0,$d_x + ($d_w - ($box_array[2] - $box_array[0]))/2 , $d_y + $px * 0.1 + (($d_h + $box_array[3] - $box_array[5]) )/2  , $d_color, $d_ttf, $d_text);
		
		
		//定义励志文字
		$box_size = $px * 0.8;
		$box_x = $d_x;
		$box_y = $d_y + $d_h + $px * 1.8;
		$box_h = $d_h * 5;
		$box_ttf = $GLOBALS['ttf_content'];
		$box_color = ImageColorAllocate ($newimg,$rgb['r'], $rgb['g'], $rgb['b']); //字体颜色
		$box_text = getMaxStr($box_w, $box_h, $box_size, $box_ttf, $text_3);//大于改坐标字体换行
		imagettftext($newimg, $box_size , 0, $box_x , $box_y, $box_color, $box_ttf, $box_text);
		
		
		
		
		
		//定义二维码log
		if($logContext != null || $logContext != ''){
			$code_size = $px * 5;
			$code_margin = round( $code_size * 0.1 );
			$code_log_img_size = round( $code_size * 0.16 );
			$code_log_txt_size = $code_margin;
			$newimg_code = addCode( $logContext ,
								$code_size , $code_margin , 
								$rgb , ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0] ,
								$logImage , $code_log_img_size ,
								$logText , $code_log_txt_size);
			
			$code_w = imagesx($newimg_code);
			$code_h = imagesy($newimg_code);
			$code_x = $resize_width - $code_w - $px * 2;
			$code_y =  $lin_y - $code_h - $px * 2;
			
			imagecopymerge($newimg, $newimg_code,
				$code_x, $code_y, 0, 0, $code_w, $code_h, $logArg);// 向原图片添加二维码
		}
		return $newimg;
}







//合成早安图片
function good_mooring(
	//图片配置
	$imgSrc,  //图片src （支持url） 图片建议取比较亮一点的
	$resize_width , //图片宽 （最好高清）
	$resize_height, //图片高（最好高清） 高度小于800最好别用二维码
	$m_gs , //外框图片模糊度  （0~10）
	$ma_gs , //内框图片模糊度 （0~10）
	$ma_ratio , //卡片大小  （0~0.3）
	$good_mooring_str , //因为字体不全，只能用 （早上好鸭晚安辣~各位）这几个字
	$text , //励志文字 （多余文字会自动丢弃）
	
	
	//二维码配置
	$logContext,// 二维码内容  （null 不添加）
	$logImage ,// 二维码中间log图片 （null 不添加）
	$logText,// 二维码下方文字 （null 不添加）
	$logArg ){ // 二维码 透明度 （0~100  数越大越不透明）
	
	
	//图片剪切
	$newimg = xq_image($imgSrc, $resize_width, $resize_height);
	
	//图片主色调/反色
	$rgb = getRgb($newimg);
	$rgbH =  getBackRgb($rgb);
	
	
	//矩形外边距
	$ma = $resize_width * $ma_ratio;
	//矩形坐标
	$x1 =round( $ma );
	$y1 =round( $ma );
	$x2 =round( $resize_width - $ma );
	$y2 =round( $resize_height - $ma );
	
	
	//内部图片截取
	$ma_w = $resize_width - $ma * 2;//内部矩形宽
	$ma_h = $resize_height - $ma * 2 ;//内部矩形高
	
	$ma_m = $ma_w * 0.02;//调整内部图片边框
	$x_1 =round(  $x1 + $ma_m ); //内部图片坐标x
	$y_1 =round(  $y1 + $ma_m ); //内部图片坐标y
	$m_w =round(  $ma_w - $ma_m * 2 );//截取图片宽
	$m_h =round(  $ma_h * 0.7 );//截取图片高
	
	
	//截取图片
	$newimg_2 = imagecreatetruecolor($ma_w, $ma_h);//新建画布
	imagecopyresampled($newimg_2, $newimg,
		0, 0, $x_1, $y_1 , $m_w, $m_h, $m_w, $m_h);//截取内部图片到画布
	
	
	
	//高斯模糊
	$newimg = blur($newimg,$ma_ratio<=0?0:$m_gs);//外圈图片	
	$newimg_2 = blur($newimg_2, $ma_gs);//内圈图片
	
	
	//画矩形+截取的图片
	imagefilledrectangle($newimg,$x1,$y1,$x2,$y2, 0xFFFFFF);//画矩形
	imagecopymerge($newimg, $newimg_2, 
		$x_1, $y_1, 0, 0, $m_w, $m_h, 100);// 向原图片添加截取好的图片



	//定义早安字体 
	$f_size = ($ma_h - $m_h) * 0.2;
	$f_x = $x_1;
	$f_y = $y_1 + $m_h + $f_size + $f_size * 0.2;
	$f_color = ImageColorAllocate ($newimg, $rgb['r'], $rgb['g'], $rgb['b']); //字体颜色
	$f_ttf = $GLOBALS['ttf_greet'];//3种字体随机
	$f_array = imagettftext($newimg,$f_size , 0, $f_x , $f_y, $f_color, $f_ttf[rand(0,2)],$good_mooring_str);
	
	//定义日期
	$d_size = $f_size * 0.42;
	$d_x = $x_1 + $m_w - $f_size * 3;
	$d_y = $y_1 + $m_h - $f_size - $f_size * 0.1;
	$d_ttf = $GLOBALS['ttf_week'];//这个字体最好别改
	$d_color_2 = ImageColorAllocate ($newimg,255,255,255 /* 66,66,66 */); //阴影颜色
	$d_color_1 = ImageColorAllocate ($newimg,  $rgb['r'], $rgb['g'], $rgb['b']/* 255,255,255 */); //字体颜色
	$d_data = "   ".getWeek()."\n".date("Y/m/d");
	imagettftext($newimg,$d_size , 0, $d_x + $m_w * 0.005 , $d_y + $m_w * 0.005, $d_color_2, $d_ttf, $d_data);
	imagettftext($newimg,$d_size , 0, $d_x , $d_y, $d_color_1, $d_ttf, $d_data);
	
	
	//定义log 二维码
	if( $logContext != null || $logContext != ''){
		$code_size = round( ($ma_h - $m_h) * 0.25 /* $ma */);
		$code_margin = round( $code_size * 0.1 );
		$code_log_img_size = round( $code_size * 0.16 );
		$code_log_txt_size = $code_margin;

		$newimg_code = addCode( $logContext ,
							$code_size , $code_margin , 
							$rgb , ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0] ,
							$logImage , $code_log_img_size ,
							$logText , $code_log_txt_size);
		$code_w = imagesx($newimg_code);
		$code_h = imagesy($newimg_code);
		$code_x = $x_1 + $code_w * 0.24 ;
		$code_y =  $y_1 + $m_h - $code_h - $code_w * 0.22;
		imagecopymerge($newimg, $newimg_code,
			$code_x, $code_y, 0, 0, $code_w, $code_h, $logArg);// 向原图片添加二维码
	}
	
	//定义文字
	$box_size = $d_size  * 0.8;
	$box_x = $x_1 ;
	$box_y = $f_array[1] + $box_size + $box_size ;
	$box_w = $m_w;
	$box_h = $y2 - $box_y /* $f_array[1] */;
	$box_ttf = $GLOBALS['ttf_content'];
	$box_color = ImageColorAllocate ($newimg,/* 53,53,53 */ $rgb['r'], $rgb['g'], $rgb['b']); //字体颜色
	$box_text = getMaxStr($box_w, $box_h, $box_size, $box_ttf, $text);//大于改坐标字体换行
	imagettftext($newimg, $box_size , 0, $box_x , $box_y, $box_color, $box_ttf, $box_text);
	
	return $newimg;
}













/////////////////////////////////// 工具 //////////////////////////////////////


/**
 * 居中裁剪图片
 * @param string $source [原图路径]
 * @param int $width [设置宽度]
 * @param int $height [设置高度]
 */
function xq_image($source, $width, $height){
	
  //图片的类型
  $type = substr(strrchr($source, "."), 1);
  //初始化图象
  if ($type == "jpg" || $type == "jpeg") 
      $image = imagecreatefromjpeg($source);
  if ($type == "gif") 
      $image = imagecreatefromgif($source);
  if ($type == "png") 
      $image = imagecreatefrompng($source);
  if ($type == "webp") 
      $image = imagecreatefromwebp($source);
  
  // 获取图像尺寸信息 
  $target_w = $width;
  $target_h = $height;
  $source_w = imagesx($image);
  $source_h = imagesy($image);
  // 计算裁剪宽度和高度 
  $judge = (($source_w / $source_h) > ($target_w / $target_h));
  $resize_w = $judge ? ($source_w * $target_h) / $source_h : $target_w;
  $resize_h = !$judge ? ($source_h * $target_w) / $source_w : $target_h;
  $start_x = $judge ? ($resize_w - $target_w) / 2 : 0;
  $start_y = !$judge ? ($resize_h - $target_h) / 2 : 0;
  //绘制居中缩放图像 
  $resize_img = imagecreatetruecolor($resize_w, $resize_h);
  imagecopyresampled($resize_img, $image, 0, 0, 0, 0, $resize_w, $resize_h, $source_w, $source_h);
  $target_img = imagecreatetruecolor($target_w, $target_h);
  imagecopy($target_img, $resize_img, 0, 0, $start_x, $start_y, $resize_w, $resize_h);
  return $target_img;
}





/**
 * 画带边框的矩形
 * @param {Object} $im gd图片流
 * @param {Object} $x x坐标
 * @param {Object} $y y坐标
 * @param {Object} $w 矩形宽
 * @param {Object} $h 矩形高
 * @param {Object} $color 矩形颜色
 * @param {Object} $rim_w 矩形边框宽
 * @param {Object} $rim_color 矩形边框颜色
 */
function draw_rectangle($im,$x,$y,$w,$h,$color,$rim_w,$rim_color){
	
	$color = imagecolorallocatealpha($im,$color['r'],$color['g'],$color['b'],$color['a']);
	$rim_color = imagecolorallocatealpha($im,$rim_color['r'],$rim_color['g'],$rim_color['b'],$rim_color['a']);
	
	$lx_1 = $x;
	$ly_1 = $y;
	$lx_2 = $x + $rim_w;
	$ly_2 = $y + $h - $rim_w;
	imagefilledrectangle($im,$lx_1,$ly_1,$lx_2,$ly_2,$rim_color);
	
	$bx_1 = $lx_1;
	$by_1 = $ly_2;
	$bx_2 = $lx_1 + $w - $rim_w;
	$by_2 = $ly_2 + $rim_w;
	imagefilledrectangle($im,$bx_1,$by_1,$bx_2,$by_2,$rim_color);
	
	$rx_1 = $lx_1 + $w -$rim_w;
	$ry_1 = $ly_1 + $rim_w;
	$rx_2 = $bx_2 + $rim_w;
	$ry_2 = $by_2;
	imagefilledrectangle($im,$rx_1,$ry_1,$rx_2,$ry_2,$rim_color);
	
	$tx_1 = $lx_1 + $rim_w;
	$ty_1 = $ly_1;
	$tx_2 = $rx_1 + $rim_w;
	$ty_2 = $ry_1;
	imagefilledrectangle($im,$tx_1,$ty_1,$tx_2,$ty_2,$rim_color);
	
	$x_1 = $x + $rim_w;
	$y_1 = $y + $rim_w;
	$x_2 = $x + $w - $rim_w;
	$y_2 = $y + $h - $rim_w;
	imagefilledrectangle($im,$x_1,$y_1,$x_2,$y_2,$color);
}








/**
 * 处理字符串，固定在这个方块里，行高，多余的舍去 不足一行时舍去
 * @param {Object} $w_max  宽
 * @param {Object} $h_max  高
 * @param {Object} $text_str  字符串
 */
function getMaxStr($w_max,$h_max,$text_size,$text_ttf,$text_str){
	$w_max = getAbs(floor($w_max));
	$h_max = getAbs(floor($h_max));
	
	$arr=str_split_unicode($text_str);
	$str = ""; //已处理字符
	$str2 = "";
	
	for($i = 0;$i<count($arr);$i++){
		$str .= $arr[$i];
		$str2 .= $arr[$i];
		//限制宽
		$box_array = imagettfbbox($text_size , 0, $text_ttf,$str2.$arr[$i+1>=count($arr)?0:$i+1] );
		if( ($box_array[2] - $box_array[0]) >= $w_max){
			$str .= "\n";
			$str2 = "";
		}
		//限制高
		$box_array = imagettfbbox($text_size , 0, $text_ttf, $str.$arr[$i+1>=count($arr)?0:$i+1] );
		if( ($box_array[3] - $box_array[5]) > $h_max)
			break;
	}
	return $str; 
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

/**
 * 取绝对值
 * @param {Object} $keys 值
 */
function getAbs($keys){
    if($keys > 0) return $keys;
    else return  -$keys;
}


/**
 * 取图片主色
 * @param {Object} $i 图片流
 */
function getRgb($i){
	$rTotal = 0;
	$gTotal = 0;
	$bTotal = 0;
	$total = 0;
	//循环宽度和高度
	for ($x=0;$x<imagesx($i);$x++) {
	  for ($y=0;$y<imagesy($i);$y++) {
	  // 获取每一个像素点的RGB
	    $rgb = imagecolorat($i,$x,$y); //十进制数
	    $r  = ( $rgb  >>  16 ) &  0xFF ; //高16位的R
		$g  = ( $rgb  >>  8 ) &  0xFF ; //高16位的R
		$b  =  $rgb  &  0xFF ; //高16位的B
	    $rTotal += $r;
	    $gTotal += $g;
	    $bTotal += $b;
	    $total++;
	  }
	}
	//rgb
	return array('r'=>round($rTotal/$total),
		'g'=>round($gTotal/$total),
		'b'=>round($bTotal/$total));
}


/**
 * 取rgb反色
 * @param {Object} $arr 
 */
function getBackRgb($arr){
	//rgb
	return array('r'=>( 255 - $arr['r']),
		'g'=>(255 - $arr['g']),
		'b'=>(255 - $arr['b']));
}



/**
 * 获取星期几
 */
function getWeek(){
	$ga = date("w"); 
	switch( $ga ){ 
		case 1 : return  "星期一";
		case 2 : return  "星期二";
		case 3 : return  "星期三";
		case 4 : return  "星期四";
		case 5 : return  "星期五";
		case 6 : return  "星期六";
		case 0 : return  "星期日";
		default : return "获取失败"; 
	};
}


/**
 * Strong Blur
 *
 * @param $gdImageResource 图片资源
 * @param $blurFactor   可选择的模糊程度
 * 可选择的模糊程度 0使用 3默认 超过5时 极其模糊
 * @return GD image 图片资源类型
 * @author Martijn Frazer, idea based on http://stackoverflow.com/a/20264482
 */
 function blur($gdImageResource, $blurFactor = 3){
	 if ($blurFactor == 0) 
	 	return $gdImageResource;
	 
	// blurFactor has to be an integer
	$blurFactor = round($blurFactor);
	$originalWidth = imagesx($gdImageResource);
	$originalHeight = imagesy($gdImageResource);
	$smallestWidth = ceil($originalWidth * pow(0.5, $blurFactor));
	$smallestHeight = ceil($originalHeight * pow(0.5, $blurFactor));
	// for the first run, the previous image is the original input
	$prevImage = $gdImageResource;
	$prevWidth = $originalWidth;
	$prevHeight = $originalHeight;
	// scale way down and gradually scale back up, blurring all the way
	for($i = 0; $i < $blurFactor; $i += 1){
		// determine dimensions of next image
		$nextWidth = $smallestWidth * pow(2, $i);
		$nextHeight = $smallestHeight * pow(2, $i);
		// resize previous image to next size
		$nextImage = imagecreatetruecolor($nextWidth, $nextHeight);
		imagecopyresized($nextImage, $prevImage, 0, 0, 0, 0,
		$nextWidth, $nextHeight, $prevWidth, $prevHeight);
		// apply blur filter
		imagefilter($nextImage, IMG_FILTER_GAUSSIAN_BLUR);
		// now the new image becomes the previous image for the next step
		$prevImage = $nextImage;
		$prevWidth = $nextWidth;
		$prevHeight = $nextHeight;
	}
	// scale back to original size and blur one more time
	imagecopyresized($gdImageResource, $nextImage,
					0, 0, 0, 0, $originalWidth, $originalHeight, $nextWidth, $nextHeight);
	imagefilter($gdImageResource, IMG_FILTER_GAUSSIAN_BLUR);
	// clean up
	imagedestroy($prevImage);
	// return result
	return $gdImageResource;
 }



/**
 * 添加处理二维码
 */
function addCode($context,//内容或url
				$size,//大小
				$margin = 0,//外边距
				$color1,//纹理颜色
				$color2,//背景颜/-色
				$log_image,//log 图片 支持url
				$log_image_size = 0,//log图片大小
				$log_text = null,//log 文字内容
				$log_size = 0,//log 字体大小
				$log_ttf = 'font/c.ttf'){//log字体
	
	if ($context ==null || $context == '') 
		return null;
	$qrCode = new QrCode($context);
	$qrCode->setEncoding('UTF-8');
	$qrCode->setWriterByName('png');	
	$qrCode->setSize($size); //大小
	$qrCode->setMargin($margin); //边框外边距
	$qrCode->setForegroundColor($color1);
	$qrCode->setBackgroundColor($color2);
	if ($log_text != null && $log_text != '') 
		$qrCode->setLabel($log_text, 10,$GLOBALS['ttf_code'], LabelAlignment::CENTER());
	if($log_image != null && $log_image != '' ){
		$qrCode->setLogoPath($log_image);
		$qrCode->setLogoSize($log_image_size,$log_image_size);
	}
	$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_MARGIN); 
	$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_ENLARGE); 
	$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK); 
	
	$qrCode->setWriterOptions(['exclude_xml_declaration' => true]);
	//$qrCode->setErrorCorrection(ErrorCorrectionLevel::LOW()); //设置二维码的纠错率，可以有low、medium、quartile、hign多个纠错率
	$qrCode->setValidateResult(false);
	return imagecreatefromstring($qrCode->writeString());
}


?>
