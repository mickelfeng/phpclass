<?php
/*
 *@author widuu
 *@time 2013-7-4
 *@模拟提交测试微信数据
 */
class WinXinPost{
    private $event = "";
    private $content = "";
    private $time;
    
    /*
     *使用严格遵守微信公众平台参数配置http://mp.weixin.qq.com/wiki/index.php?title=消息接口指南
     *如果是text或者image类型就直接输入$content
     *其他的就输入array 譬如地理位置输入
     *<Location_X>23.134521</Location_X>
     *<Location_Y>113.358803</Location_Y>
     *   <Scale>20</Scale>
     * <Label><![CDATA[位置信息]]></Label>
     * array('1.29290','12.0998','20','位置信息');
     *
     */
    public function __construct($event,$url,$content){
        $this-> event = $event;
        $this-> url = $url;
        $this-> content = $content;
        $this-> time = time();
    }

    //返回接收的消息
    public function result(){
        $postObj = simplexml_load_string($this->post(), 'SimpleXMLElement', LIBXML_NOCDATA);
        foreach ((array)$postObj as $key => $value) {
                $str.=$key.'=>'.$value."<br>";
        }
        return $str;

    }

    //处理成xml数据
    private  function xml_data(){
        $str = "
            <xml>
                 <ToUserName>100012</ToUserName>
                 <FromUserName>100012</FromUserName>
                 <CreateTime>{$this->time}</CreateTime>
                 <MsgType>{$this->event}</MsgType>
                 {$this->judgment()}
                 <MsgId>1234567890123456</MsgId>
            </xml>
         ";
         return $str;
    }

    //模拟post提交
    private function post(){
        $header[] = "Content-type: text/xml";//定义content-type为xml
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $this->url);//设置链接
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头
        curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml_data());//POST数据
        $response = curl_exec($ch);//接收返回信息
        if(curl_errno($ch)){//出错则显示错误信息
             print curl_error($ch);
        }
         curl_close($ch); //关闭curl链接
         return $response;
     }

    //文本消息
    private function text(){
        return  "<Content>{$this->content}</Content>";
    }

    //图形消息
    private function image(){
        return "<PicUrl>{$this->content}</PicUrl>";
    }

    //链接消息
    private function link(){
        $data = $this->content;
        $str = "
            <Title>{$data[0]}</Title>
            <Description>{$data[1]}</Description>
            <Url>{$data[2]}</Url>
        ";
        return $str;
    }

    //地理位置消息
    private function location(){
        $data = $this->content;
        $str = "
            <Location_X>{$data[0]}</Location_X>
            <Location_Y>{$data[1]}</Location_Y>
            <Scale>20</Scale>
            <Label>{$data[3]}</Label>";
         return $str;
    }
   
    //根据消息类型加载相应的东西
    private function judgment(){
        $type = $this->event;
        return $this->$type();
    }

}

$a = new WinXinPost("text","http://www.lingphp.com/wx_sample.php",11111);
echo $a->result();
