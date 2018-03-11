<?php
//error_reporting(0);
#引入模块
require 'lib/phpQuery.php';
require 'lib/QueryList.php';
require 'core/readHtml.php';
require "lib/Medoo.php";
include "lib/Snoopy.class.php";
include "core/myfunction.php";

use Medoo\Medoo;
use QL\QueryList;

$db = new medoo([
    'database_type' => 'sqlite',
    'database_file' => 'db/91.db'
]);

//echo $_REQUEST["proxy"] ? 'tcp://'.$_REQUEST["proxy"] : '';

//根据地址，获取视频地址
function getList($url){

	#获取URL
	/*$url = $_REQUEST["url"];

	$video=$db->select("videos","Video",["URL" => $url]);

	if($data){
		return $video[0]["Video"];
	}*/

	#$html = readHtml($url,urldecode($_REQUEST["proxyip"]));
	$html = getHtml($url);

	$rules = array(
    //采集id为one这个元素里面的纯文本内容
    'video' => array('source','src')
	);
	$data = QueryList::Query($html,$rules)->data;
	//print_r($data);

	$link = $data[0]["video"];

    //print_r($db->id());
    if($link){
        global $db,$viewkey;

    	$db->insert("videos",[
    		"url" => $viewkey,
    		"link" => $link
    	]);
    }


	return $link;
}

//$video = getList();



function getHtml($url){

    $ip = randIp();
    $snoopy = new Snoopy;

    #$snoopy->proxy_host = "165.227.104.78";
    $snoopy->proxy_port = "3128";
    #$snoopy->_isproxy = true;

    $snoopy->cookies["PHPSESSID"] = 'fsef'; //伪装sessionid
    #$snoopy->agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36"; //伪装浏览器
    //$snoopy->referer = 'http://www.4wei.cn'; //伪装来源页地址 http_referer
    $snoopy->rawheaders["Pragma"] = "no-cache"; //cache 的http头信息
    $snoopy->rawheaders["Accept-language"] = "zh-cn"; //cache 的http头信息
    $snoopy->rawheaders["Content-Type"] = "text/html; charset=utf-8"; //cache 的http头信息
    $snoopy->rawheaders["CLIENT-IP"] = $ip; //伪装ip
    $snoopy->rawheaders["HTTP_X_FORWARDED_FOR"] = $ip; //伪装ip
    #$snoopy->fetch("http://www.checkip.net");
    $snoopy->fetch($url);

    return $snoopy->results;
}


#获取URL
$url = urldecode($_REQUEST["url"]);

$urlarr=parse_url($url);
parse_str($urlarr['query'],$parr);
$viewkey = $parr["viewkey"];

$dbResult=$db->select("videos","link",["url" => $viewkey]);

    //print_r($dbResult);
//$video = '';

//数据在缓存中，直接取
if($dbResult){
    //global $video;
    $video = $dbResult[0];
    $catch=true;
    //echo $video;
}else{
    global $video;
    $video = getList($url);
    //echo "src";
    //$video = getVideo($url);
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="format-detection" content="telephone=no">
        <title>视频详情-91视频预览</title>
        <link rel="stylesheet" href="frozenui/css/frozen.css">
        <link rel="stylesheet" href="frozenui/css/demo.css">
    </head>
    <body>
    	<header class="ui-header ui-header-positive ui-border-b">
            <i class="ui-icon-return" onclick="history.back()"></i><a href="91.php" style="position: absolute;left: 20px;">&nbsp;&nbsp;&nbsp;&nbsp;列表页</a><h1>视频详情</h1><button onclick="window.location.href='index.php';" class="ui-btn">回首页</button>
        </header>

        <section class="ui-container">
        	<?php if($video){ ?>
        	<div class="ui-tooltips ui-tooltips-guide">
                <div class="ui-tooltips-cnt ui-tooltips-cnt-link ui-border-b">
                    <i class="ui-icon-talk"></i><?php if($catch) echo "通过缓存"?>加载成功
                </div>
            </div>
            <?php } ?>

            <?php if(!$video){ ?>
            <div class="ui-tooltips ui-tooltips-warn">
                <div class="ui-tooltips-cnt ui-border-b">
                    <i></i>获取失败，请刷新或更换代理重试
                </div>
            </div>
            
            <?php } ?>

            <p class="demo-desc"><?php echo urldecode($_REQUEST["title"]) ?></p>
        	<video width="100%"  controls="controls">
        		<source src="<?php echo $video; ?>" type="video/mp4">
        	</video>
		</section>
    </body>
</html>



















