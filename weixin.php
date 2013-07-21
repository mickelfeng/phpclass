 <?php
/**
  * wechat php test
  */
include("ABClient.php");  //爱帮网申请 然后下载的sdk
define("FANAPK", "API key");  //这个百度应用duapp.com创建一个应用获得的 API key
define("WAPK","key"); //这个key地址http://lbsyun.baidu.com/apiconsole/key?application=key来获取
//define your token
define("TOKEN", "这里写你自己的token");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
  private $model;
	private $name;
	 public function __construct(){
		$this->model = new ABClient();
		$a = $this->model->test_transfer();
		$this->name = include("name.php");
	} 
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

    public function responseMsg()
    {
		
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
				$type = $postObj->MsgType;
				$event = $postObj->Event;
				$c = $postObj->Location_X;
				$d = $postObj->Location_Y;
                $keyword = trim($postObj->Content);
                $time = time();
				$msgid = $postObj->MsgId;
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";             
				if($type == "event" && $event == "subscribe")
                {
              		$msgType = "text";
                	$contentStr.= "欢迎关注微度网络，该公众平台有以下几种功能：\n\n";
					$contentStr.= "1.直接输入汉字或者中文进行中英文翻译，如“你好”\n";
					$contentStr.= "2.输入“天气+地区”进行天气查询，如“天气+石家庄”\n";
					$contentStr.= "3.输入“城市+起点+终点”进行公交查询，如“石家庄+火车站+宫家庄”\n";
					$contentStr.= "4.输入“@任何内容”跟小贱鸡聊天，如“@小贱鸡”\n";
					$contentStr.= "5.微信发送您的地理位置进行天气查询，您可以试一试\n";
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                	echo $resultStr;
                	
                }else if($type == "text"){
					$keywords = explode("+",$keyword);
					$b = explode("@",$keyword);
					if(isset($b[1])){
						$contentStr = $this->simsim($b[1]);
						$msgType = "text";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
						exit();
					}
					if($keywords[0]=="火车"){
						$city = $keywords[1];
						$end = $keywords[2];
						$time = $keywords[3];
						$result = $this->doget($city,$end,$time);
						if(!empty($result)){
							$result = strip_tags($result['datas']);
							$return_str = str_replace("&nbsp;","",$result);
							$return_str = str_replace("\\n","",$return_str);
							$a = explode(",",$return_str);
							$name =array();
							$c = array_chunk($a,16);
							array_pop($c);
							foreach($c as $k =>$v){
								$str="余票：\n商务座:".$v[5]."，特等座:".$v[6]."，一等座:".$v[7]."，二等座:".$v[8]."，高级软卧:".$v[9]."，软卧:".$v[10]."，硬卧:".$v[11]."，软座:".$v[12]."，硬座:".$v[13]."，无座:".$v[14]."，其他:".$v[15];
								$str = preg_replace("/硬座\:--，/","",$str);
								$str = preg_replace("/商务座\:--，/","",$str);
								$str = preg_replace("/特等座\:--，/","",$str);
								$str = preg_replace("/一等座\:--，/","",$str);
								$str = preg_replace("/二等座\:--，/","",$str);
								$str = preg_replace("/高级软卧\:--，/","",$str);
								$str = preg_replace("/软卧\:--，/","",$str);
								$str = preg_replace("/硬卧\:--，/","",$str);
								$str = preg_replace("/软座\:--，/","",$str);
								$str = preg_replace("/硬座\:--，/","",$str);
								$str = preg_replace("/其他\:--/","",$str);
								$contentStr.="车次:{$v[1]},发站：{$v[2]}，到站：{$v[3]}，历时：{$v[4]}。\n{$str}\n\n";
								if($k == 12){
									break;
								}
							}
						}else{
							$contentStr.="抱歉没有查到";
						}
						
						$msgType = "text";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
						exit();
					}
					if(isset($keywords[1])&&!isset($keywords[2])){
						if($this->pexpress(trim($keywords[0]))){
							$a = $this->express(trim($keywords[0]),trim($keywords[1]));
							if($a["message"]=="ok"){
								if($a['ischeck']==1){
									$contentStr.="您的包裹已经签收\n\n";
								}else{
									$contentStr.="您的包裹还未签收\n\n";
								}
								$data = $a['data'];
								foreach($data as $k =>$v){
									$contentStr.="更新时间：{$v["time"]}\n物流状态：{$v["context"]}\n\n";
								}
							}else{
									$contentStr = "查询失败，请输入正确后查询";
							}
								$msgType = "text";
								$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
								echo $resultStr;
						}else{
								$weather = $this->weather($keywords[1]);
								if(!empty($weather))
								{
									$data = $this->json2array($weather);
									foreach($data as $k =>$v)
									{
										$str.=$v['date'].'。天气：'.$v['weather'].'。风速：'.$v['wind'].'。温度：'.$v['temperature']."\n\n";
									}
								}else{
									$str="请您输入正确的地址，输入方式如天气+北京";
								}
								
								$msgType = "text";
								$contentStr = $str;
								$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
								echo $resultStr;
						}
					
					}else if(isset($keywords[2])&&($keywords[0]!=="火车")){
						$city = $keywords[0];
						$start = $keywords[1];
						$end =$keywords[2];
						$a = $this->model->test_transfer($city,$start,$end);
						if(!empty($a)){
							 foreach($a as $k=>$v){
								$contentStr.= "线路{$c}:".$v['dist']."米。估计耗费时间:{$v['time']}分钟。\n乘车线路：{$v['segments']["segment"][0]["line_name"]}。\n上车地点：{$v['segments']["segment"][0]["start_stat"]}。\n下车地点:{$v['segments']["segment"][0]["end_stat"]}。\n经过路线:{$v['segments']["segment"][0]["stats"]}\n\n";
								if($k == 4){
									break;
								}
							}
						
						}else{
							$contentStr="抱歉没有查询到";
						}
						
						$msgType = "text";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					}else{
						$fanyi = $this->fanyi($keyword);
						$msgType = "text";
						$contentStr = $fanyi;
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					}
					
                }else if($type == 'location'){
					$result = $this->address($c,$d);
					if(isset($result['district'])){
						$weather = $this->weather($result['district']);
						if(!$weather){
							$weather = $this->weather($result['city']);
						}
					}else{
						$weather = $this->weather($result['city']);
					}
					
					$data = $this->json2array($weather);
					foreach($data as $k =>$v){
						$str.=$v['date'].'。天气：'.$v['weather'].'。风速：'.$v['wind'].'。温度：'.$v['temperature']."\n\n";
					}
					$msgType = "text";
					$contentStr = $str;
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
					echo $resultStr;
				}else{
					echo "您可以输入点什么";
				}

        }else {
        	echo "";
        	exit;
        }
    }
	//获取地理位置的
	private function address($addx,$addy)
	{
		$arr = array();
		$result = $this->map_text("http://api.map.baidu.com/geocoder/v2/?ak=".WAPK."&callback=renderReverse&location={$addx},{$addy}&output=xml&pois=0");
		$data = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
		$result = $this->json2array($data);
		$arr = $result["result"]["addressComponent"];
		$address = $result['result']['formatted_address'];
		array_push($arr,$address);
		return $arr;
	}
	//解析json为数组
	private function json2array($json) {
			if ($json) {
				foreach ((array)$json as $k=>$v) {
					$data[$k] = !is_string($v) ? $this->json2array($v) : $v;
				}
				return $data;
			}
		}	
	//获取天气信息
	private function weather($data)
	{
		//根据API调用输出数据
		$url="http://api.map.baidu.com/telematics/v2/weather?location={$data}&output=json&ak=".WAPK;
		$weather=$this->map_text($url);
		$data = json_decode($weather);
		return $data->results;
	}
	//调用翻译的api
	private function fanyi($data)
	{
		//根据API调用输出数据
		$url="http://openapi.baidu.com/public/2.0/bmt/translate?client_id=".FANAPK."&q={$data}&from=auto&to=auto";
		$fanyi=$this->map_text($url);
		$shuju=json_decode($fanyi);
		$result=$shuju->trans_result;
		return $result[0]->dst;
	}
	//解析地址的api
	private function map_text($url)  
	{
		if(!function_exists('file_get_contents')) {
		$file_contents = file_get_contents($url);
			} else {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$file_contents = curl_exec($ch);
			curl_close($ch);
		}
			return $file_contents;
	}
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	private function simsim($keywords){
		$curlPost=array("txt"=>$keywords);
		$ch = curl_init();//初始化curl
		curl_setopt($ch,CURLOPT_URL,'http://xiaohuangji.com/ajax.aspx');//抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		$data = curl_exec($ch);//运行curl
		curl_close($ch);
		return $data;
	}
	private function pexpress($exname){
			$a = $this->map_text("http://www.kuaidi100.com/");
			preg_match_all("/data\-code\=\"(?P<name>\w+)\"\>\<span\>(?P<title>.*)\<\/span>/iU",$a,$b);
			$name = array();
			foreach($b['title'] as $k=>$v){
				 $name[$v] = $b['name'][$k];
			}
			if(!empty($name[$exname])){
				return true;
			}else{
				return false;
			}
	}
	private function express($keywords,$number){
			$a = $this->map_text("http://www.kuaidi100.com/");
			preg_match_all("/data\-code\=\"(?P<name>\w+)\"\>\<span\>(?P<title>.*)\<\/span>/iU",$a,$b);
			$name = array();
			foreach($b['title'] as $k=>$v){
				 $name[$v] = $b['name'][$k];
			}
			$this->name = $name;
			$keywords = $name[$keywords];
			$url = "http://www.kuaidi100.com/query?type={$keywords}&postid={$number}";
			$result = $this->map_text($url);
			$result = $this->json2array(json_decode($result));
			return $result;
	}
	
	private function doget ($start,$end,$time) // get获取数据使用
	{
		if(empty($time)){
			$time = date('Y-m-d',time());
		}else{
			if(substr($time,0,1)!=0){
				$time = date('Y-0',time()).$time;
			}else{
				$time = date('Y-',time()).$time;
			}
		}
		$star = $this->name[$start];
		$end = $this->name[$end];
		$url = "http://dynamic.12306.cn/otsquery/query/queryRemanentTicketAction.do?method=queryLeftTicket&orderRequest.train_date={$time}&orderRequest.from_station_telecode={$star}&orderRequest.to_station_telecode={$end}&orderRequest.train_no=&trainPassType=QB&trainClass=QB%23D%23Z%23T%23K%23QT%23&includeStudent=00&seatTypeAndNum=&orderRequest.start_time_str=00%3A00--24%3A00";
		$optionget = array('http' => array('method' => "GET", 'header' => "User-Agent:Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.21022; .NET CLR 3.0.04506; CIBA)\r\nAccept:*/*\r\nReferer:http://dynamic.12306.cn/otsquery/query/queryRemanentTicketAction.do?method=init")); 
		$file = file_get_contents($url, false , stream_context_create($optionget));
		return $this->json2array(json_decode($file));
	}
	
}

?>
