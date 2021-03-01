<?php

	//数据库
	$GLOBALS['servername'] = 'localhost';                      //数据库地址
	$GLOBALS['username'] = 'root';                             //数据库名
	$GLOBALS['password'] = 'root';                             //数据库密码
	$GLOBALS['mydata'] = 'get_image';                          //数据库
	
	
	
	//图片目录
	$GLOBALS['image_path'] = "image/";
	
	//早安图片显示时间段
	$GLOBALS['date_mooring'] = ['start'=> 5, 'stop'=> 13 ];    //24小时格式
	
	//祝福语长度 （0个字 ~ 60个字）
	$GLOBALS['text_length'] = 60;                            //祝福语长度 （0个字 ~ 60个字） 
	
	
	
	
	
	
	
	
	
	
	//字体配置 (这里为了大小，只有 c.ttf 字体包为完整的，其他用别的文字会不显示，或者你自己改为完整的字体包)
	$GLOBALS['ttf_greet'] = [realpath('font/a.ttf'),realpath('font/a1.ttf'),realpath('font/a2.ttf')];    // 问候语（晚安，早上好鸭）的字体  3种随机
	$GLOBALS['ttf_week'] = realpath('font/b.ttf');             // 星期字体
	$GLOBALS['ttf_content'] = realpath('font/c.ttf');          // 励志文字题
	$GLOBALS['ttf_code'] = realpath('font/c.ttf');             // 二维码字体
	
	
	//早安默认值
	$GLOBALS['mooring_img'] = 'image/find/mooring.jpg';        // 图片                          （最好高清）
	$GLOBALS['mooring_w'] = 1080;                              // 图片宽                        （最好高清）
	$GLOBALS['mooring_h'] = 1920;                              // 图片高                        （最好高清  高度小于800最好别用二维码）
	$GLOBALS['mooring_text1'] = "早上好鸭";                     // 早安问候语                     （需要字体支持）
	$GLOBALS['mooring_text2'] = "你浅浅的微笑，\n是一首纯真的抒情诗，\n是一支幽婉的小夜曲。\n早安！";     //祝福语  （60个中文字左右）
	$GLOBALS['mooring_img_gs'] = 6;                            // 外框图片模糊度                 （0 ~ 10）
	$GLOBALS['mooring_img_mgs'] = 0;                           // 内框图片模糊度                 （0 ~ 10）
	$GLOBALS['mooring_ratio'] = 0.1;                           // 卡片大小                      （0 ~ 0.3）
	
	//晚安默认值
	$GLOBALS['night_img'] = 'image/find/night.jpg';            // 图片                          （最好高清）
	$GLOBALS['night_w'] = 1080;                                // 图片宽                        （最好高清）
	$GLOBALS['night_h'] = 1920;                                // 图片高                        （最好高清）
	$GLOBALS['night_text1'] = "   晚\n安";                     // 晚安问候语                     （这里最好别改，同样需要字体支持）
	$GLOBALS['night_text2'] = "GOOD NIGHT";                    // GOOD NIGHT                   （6>个中文）
	$GLOBALS['night_text3'] = "在一切变好之前\n我们总要经历一些不开心的日子\n这段日子也许很长\n也许只是一觉醒来。\n有时候，选择快乐，更需要勇气。\n晚安！"; //祝福语 （60>个中文）
	$GLOBALS['night_img_gs'] = 0;                              // 图片模糊度                    （0 ~ 10）
	$GLOBALS['night_atb'] = 0;                                 // 上下白边透明度                 （0 ~ 127  数越大越透明）
	 
	//二维码水映默认值
	$GLOBALS['code_open'] = true;                              // 是否设置当get没有值时启用此配置  （false 不启用）
	$GLOBALS['code_content'] = 'http://pikachu.org.cn';        // 扫描后的内容                   （null 不添加 这个没有则不添加二维码）
	$GLOBALS['code_log_img'] = 'image/find/log.png';                 // 二维码中间log图片               （null 不添加 , 支持http/https）
	$GLOBALS['code_text'] = 'PIKACHU';                         // 二维码下方文字                  （null 不添加 , 中文 3 ~ 6 个字为佳）
	$GLOBALS['code_atb'] = 100;                                // 二维码透明度                   （0~100  数越大越不透明）
?>