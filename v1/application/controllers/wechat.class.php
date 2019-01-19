<?php
class wechat
{
    
    public $appId;//公众号的appId
    private $appSecret;//公众号的appSecret
    
    //public $datatime;
    public $timestamp;//时间戳
    
    private $wechat_cache;//缓存对象
    
    //构造函数给上面四个属性赋值
    public function __construct($appId = null, $appSecret = null) 
    {
        
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        
        
        //$this->datatime=date("Y-m-d H:i:s",time());
        $this->timestamp = time();
        // $this->wechat_cache = $this->init_cache();
    } 
    
    //验证签名
    public function checkSignature()
    {
        $signature = $_GET["signature"];//微信加密签名
        $timestamp = $_GET["timestamp"];//时间戳
        $nonce = $_GET["nonce"];//随机数	
        $echoStr = $_GET["echostr"];//随机字符串
        
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if ($tmpStr == $signature) {
            if (!isset($echoStr)) {
                return true;
            } else {
                echo $echoStr;
                exit;
            }
        } else {
            //die("token验证失败");
            return false;
        }
    }
    
    //接收来自微信的消息
    public function receiveMessageStr()
    {
        return file_get_contents("php://input");
    }	
    public function receiveMessage($postStr = null)
    {
        if ($postStr === null) {
            $postStr = $this->receiveMessageStr();
        }
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $postObj;
    }
    
    //回复文本消息
    public function transmitText($object, $content)
    {
        if (!isset($content) || empty($content)) {
            return "";
        }
        
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        
        return $result;
    }
    
    //回复图文消息
    public function transmitNews($object, $newsArray)
    {
        if (!is_array($newsArray)) {
            return "";
        }
        $itemTpl = "<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
        </item>
";
        $item_str = "";
        foreach ($newsArray as $item) {
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>%s</ArticleCount>
    <Articles>
$item_str    </Articles>
</xml>";
        
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }
    
    //回复音乐消息
    public function transmitMusic($object, $musicArray)
    {
        if (!is_array($musicArray)) {
            return "";
        }
        $itemTpl = "<Music>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <MusicUrl><![CDATA[%s]]></MusicUrl>
        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
    </Music>";
        
        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);
        
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[music]]></MsgType>
    $item_str
</xml>";
        
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
    
    //回复图片消息
    public function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
        <MediaId><![CDATA[%s]]></MediaId>
    </Image>";
        
        $item_str = sprintf($itemTpl, $imageArray['MediaId']);
        
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[image]]></MsgType>
    $item_str
</xml>";
        
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
    
    //回复语音消息
    public function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
        <MediaId><![CDATA[%s]]></MediaId>
    </Voice>";
        
        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[voice]]></MsgType>
    $item_str
</xml>";
        
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
    
    //回复视频消息
    public function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
        <MediaId><![CDATA[%s]]></MediaId>
        <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
    </Video>";
        
        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);
        
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[video]]></MsgType>
    $item_str
</xml>";
        
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
    
    //回复多客服消息
    public function transmitService($object)
    {
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
    
    //回复第三方接口消息
    public function relayPart3($url, $rawData, $token)
    {
        $timestamp = time();
        $nonce = rand(10000,99999);
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $signature = sha1($tmpStr);
        
        $url=$url."?timestamp=".$timestamp."&signature=".$signature."&nonce=".$nonce;
        
        return $this->https_request($url, $rawData);

    }
    
    //字节转Emoji表情
    public function bytes_to_emoji($cp)
    {
        if ($cp > 0x10000){       # 4 bytes
            return chr(0xF0 | (($cp & 0x1C0000) >> 18)).chr(0x80 | (($cp & 0x3F000) >> 12)).chr(0x80 | (($cp & 0xFC0) >> 6)).chr(0x80 | ($cp & 0x3F));
        }else if ($cp > 0x800){   # 3 bytes
            return chr(0xE0 | (($cp & 0xF000) >> 12)).chr(0x80 | (($cp & 0xFC0) >> 6)).chr(0x80 | ($cp & 0x3F));
        }else if ($cp > 0x80){    # 2 bytes
            return chr(0xC0 | (($cp & 0x7C0) >> 6)).chr(0x80 | ($cp & 0x3F));
        }else{                    # 1 byte
            return chr($cp);
        }
    }
    
    //日志记录
    private function logger($log_content)
    {
        if (isset($_SERVER['HTTP_APPNAME'])) {   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        } elseif ($_SERVER['REMOTE_ADDR'] != "127.0.0.1") { //LOCAL
            $max_size = 1000000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('Y-m-d H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }
    
    //用于post数据的curl函数
    public function https_request($url, $data = null, $headers = array("Content-Type: text/xml; charset=utf-8"))
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    
    //获取access_token
    public function get_access_token()
    {
        $cache_key="access_token@".$this->appId;
        
        $temp_arr = $this->get_cache($cache_key);
        
        if (empty($temp_arr)) {
            
            $temp_str = $this->refresh_get_access_token();
            
            $this->add_cache($cache_key, $temp_str);
            
        } else {
            
            $expires_time = $temp_arr[1];
            
            if ($this->timestamp>$expires_time) {
                
                $temp_str = $this->refresh_get_access_token();
                
                $this->add_cache($cache_key, $temp_str);		
                
            } else {
                
                $temp_str = $temp_arr[0];
                
            }
        }
        
        return $temp_str;
    }    
    
    private function refresh_get_access_token()
    {
        $access_token_json = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appId."&secret=".$this->appSecret."");//获取access_token
        $access_token_object = json_decode($access_token_json); //把JSON编码的字符串转换为object
        $access_token = $access_token_object->access_token;
        
        return $access_token;
    }
    
    //获取jssdk的ticket
    private function get_jsapi_ticket()
    {
        $cache_key = "jsapi_ticket@".$this->appId;
        
        $temp_arr = $this->get_cache($cache_key);
        
        if (empty($temp_arr)) {
            
            $temp_str = $this->refresh_get_jsapi_ticket();
            
            $this->add_cache($cache_key, $temp_str);
            
        } else {
            
            $expires_time = $temp_arr[1];
            
            if ($this->timestamp>$expires_time) {
                
                $temp_str = $this->refresh_get_jsapi_ticket();
                
                $this->add_cache($cache_key, $temp_str);		
                
            } else {
                
                $temp_str = $temp_arr[0];
                
            }
        }
        
        return $temp_str;
    }
    
    private function refresh_get_jsapi_ticket()
    {
        $access_token = $this->get_access_token();//获取access_token
        $jsapi_ticket_json = file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi");//获取jsapi_ticket
        $jsapi_ticket_object = json_decode($jsapi_ticket_json);//把JSON编码的字符串转换为object
        $jsapi_ticket = $jsapi_ticket_object->ticket;
        
        return $jsapi_ticket;
    }
    
    //获取jssdk的签名    
    public function get_jssk_signature()
    {
        $jsapi_ticket = $this->get_jsapi_ticket();//获取jssdk的ticket
        $noncestr = $this->get_noncestr();// 生成签名的随机字符串
        $timestamp = $this->timestamp;// 生成签名的时间戳
        //$url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];// 当前网页的URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $protocol.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];// 当前网页的URL
        $string1 = "jsapi_ticket=".$jsapi_ticket."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url."";//拼接参与签名的字段，顺序不能变
        $signature = sha1($string1);//对string1作sha1加密，签名到此生成完毕
        
        return $signature;
    }    
    
    //获取卡劵的ticket
    public function get_api_ticket()
    {
        $cache_key = "jsapi_ticket@".$this->appId;
        
        $temp_arr = $this->get_cache($cache_key);
        
        if (empty($temp_arr)) {
            
            $temp_str = $this->refresh_get_api_ticket();
            
            $this->add_cache($cache_key, $temp_str);
            
        } else {
            
            $expires_time = $temp_arr[1];
            
            if ($this->timestamp>$expires_time) {
                
                $temp_str = $this->refresh_get_api_ticket();
                
                $this->add_cache($cache_key, $temp_str);		
                
            } else {
                
                $temp_str = $temp_arr[0];
                
            }
        }
        
        return $temp_str;
    }    
    
    private function refresh_get_api_ticket()
    {
        $access_token = $this->get_access_token();
        $api_ticket_json = file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=wx_card");
        $api_ticket_object = json_decode($api_ticket_json);
        $api_ticket = $api_ticket_object->ticket;
        
        return $api_ticket;
    }    
    
    //获取卡劵的签名cardSign
    public function get_cardSign($card_id)
    {
        
        $timestamp = $this->timestamp;
        $noncestr = $this->get_noncestr();
        $api_ticket = $this->get_api_ticket();
        
        $arrdata = array("timestamp" => $timestamp, "noncestr" => $noncestr, "jsapi_ticket" => $api_ticket,"card_id" => $card_id);
        arsort($arrdata，3);
        $paramstring = "";
        foreach ($arrdata as $key => $value) {
            $paramstring .= $value;
        }
        $sign = sha1($paramstring);
        if (!$sign)
            return false;
        return $sign;
    }
    
    //生成签名的随机字符串
    public function get_noncestr($length = 16) 
    {
        $cache_key = "noncestr@".$this->appId;
        
        $temp_arr = $this->get_cache($cache_key);
        
        if (empty($temp_arr)) {
            
            $temp_str = $this->refresh_get_noncestr();
            
            $this->add_cache($cache_key, $temp_str);
            
        } else {
            
            $expires_time = $temp_arr[1];
            
            if ($this->timestamp>$expires_time) {
                
                $temp_str = $this->refresh_get_noncestr();
                
                $this->add_cache($cache_key, $temp_str);		
                
            } else {
                
                $temp_str = $temp_arr[0];
                
            }
        }
        
        return $temp_str;
    }
    
    private function refresh_get_noncestr($length = 16) 
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        
        return $str;
    }
    
    //获取网页授权的access_token
    public function get_web_access_token($code)
    {
        
        $appid = $this->appId;
        $appsecret = $this->appSecret;
        
        $access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
        $access_token_json = file_get_contents($access_token_url);
        $access_token_array = json_decode($access_token_json, true);
        
        return $access_token_array;
    }
    
    //获取网页授权的用户信息
    public function get_web_user_info($code)
    {
        $access_token_array = $this->get_web_access_token($code);
        $access_token = $access_token_array['access_token'];
        $openid = $access_token_array['openid'];
        
        $userinfo_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
        $userinfo_json = file_get_contents($userinfo_url);
        $userinfo_array = json_decode($userinfo_json, true);
        
        return $userinfo_array;
    }
    
    //获取关注者列表
    public function get_user_list($next_openid = NULL)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$access_token."&next_openid=".$next_openid;
        $res = $this->https_request($url);
        return json_decode($res, true);
    }
    
    //获取用户基本信息
    public function get_user_info($openid)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $res = $this->https_request($url);
        return json_decode($res, true);
    }
    
    //创建菜单
    public function create_menu($data)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $res = $this->https_request($url, $data);
        return json_decode($res, true);
    }
    
    //发送客服消息
    public function send_custom_message($msg)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
        return $this->https_request($url, urldecode(json_encode($msg)));
    }
    
    //发送客服消息，文本消息
    public function send_custom_message_text($touser, $content)
    {
        $msg = array(
            'touser' => "$touser",
            'msgtype' => "text",
            'text' => array(
                'content' => urlencode("$content"),
            )                         
        );
        return $this->send_custom_message($msg);
    }
    
    //发送客服消息，图片消息
    public function send_custom_message_image($touser, $media_id)
    {
        $msg = array(
            'touser' => "$touser",
            'msgtype' => "image",
            'image' => array(
                'media_id' => "$media_id",
            )                         
        );
        return $this->send_custom_message($msg);
    }
    
    //发送客服消息，语音消息
    public function send_custom_message_voice($touser, $media_id)
    {
        $msg = array(
            'touser' => "$touser",
            'msgtype' => "voice",
            'voice' => array(
                'media_id' => "$media_id",
            )                         
        );
        return $this->send_custom_message($msg);
    }
    
    //发送客服消息，视频消息
    public function send_custom_message_video($touser, $data)
    {
        $msg = array(
            'touser' => "$touser",
            'msgtype' => "video",
            'video' => array(
                'media_id' => $data['MediaId'],
                'thumb_media_id' => $data['ThumbMediaId'],
                'title' => urlencode($data['Title']),
                'description' => urlencode($data['Description']),
            )                         
        );
        return $this->send_custom_message($msg);
    }
    
    //发送客服消息，音乐消息
    public function send_custom_message_music($touser, $data)
    {
        $msg = array(
            'touser' => "$touser",
            'msgtype' => "music",
            'music' => array(
                'title' => $data['Title'],
                'description' => urlencode($data['Description']),
                'musicurl' => $data['MusicUrl'],
                'hqmusicurl' => $data['HQMusicUrl'],
                'thumb_media_id' => $data['Thumb_media_id'],
            )                         
        );
        return $this->send_custom_message($msg);
    }
    
    //发送客服消息，发送图文消息（点击跳转到外链）
    public function send_custom_message_news($touser, $data)
    {
        foreach ($data as $key => $value) {
            $articles[$key]['title'] = urlencode($value['Title']);
            $articles[$key]['description'] = urlencode($value['Description']);
            $articles[$key]['url'] = $value['Url'];
            $articles[$key]['picurl'] = $value['PicUrl'];
        }
        
        $msg = array(
            'touser' => "$touser",
            'msgtype' => "news",
            'news' => array(
                'articles' => $articles,
            ),                        
        );
        
        return $this->send_custom_message($msg);
    }
    
    //发送客服消息，图文消息（点击跳转到图文消息页面）
    public function send_custom_message_mpnews($touser, $media_id)
    {
        foreach ($media_id as $key => $value) {
            $temp[$key]['media_id'] = $value;
        }
        
        $msg = array(
            'touser' => "$touser",
            'msgtype' => "mpnews",
            'mpnews' => $temp,                       
        );
        
        return $this->send_custom_message($msg);
    }
    
    //发送客服消息，发送卡券
    public function send_custom_message_wxcard($touser, $card_id)
    {
        $sgins=$this->get_cardSign($card_id);
        
        $msg = array(
            'touser' => "$touser",
            'msgtype' => "wxcard",
            'wxcard' =>  array(
                'card_id' => "$card_id",
                'card_ext' => "$sgins",
            ),                         
        );
        
        return $this->send_custom_message($msg);
    }
    
    /*
    //初始化缓存_memcache
    private function init_cache()
	{
        $mem = new Memcache();
        $mem->connect("localhost", 11211); 
		
		return $mem;
	}
    
    //获取缓存里的值_mamcache
    private function get_cache($cache_key)
    {
        $mem = $this->wechat_cache;

        $cache = $mem->get($cache_key);
        
        
        return $cache; 
    }
    
    //往缓存里添加值_mamcache
    private function add_cache($cache_key, $cache_value)
    {
        $mem = $this->wechat_cache;
		$expires_time = $this->timestamp+7000;
		$cache_value_arr = array($cache_value, $expires_time);
        $mem->set($cache_key, $cache_value_arr,0,7100);
    }
    
    //删除缓存里的值_mamcache
    public function delete_cache($cache_key)
    {
        $mem = $this->wechat_cache;

        $mem->delete($cache_key);
    }
    */
    /*
    //初始化缓存_memcached 未通过测试
    private function init_cache()
	{
        $mem = new Memcached();
        $mem->addServer("localhost", 11211); 
		
		return $mem;
	}
    
    //获取缓存里的值_mamcached
    private function get_cache($cache_key)
    {
        $mem = $this->wechat_cache;

        $cache = $mem->get($cache_key);
        
        
        return $cache; 
    }
    
    //往缓存里添加值_mamcached
    private function add_cache($cache_key, $cache_value)
    {
        $mem = $this->wechat_cache;
		$expires_time = $this->timestamp+7000;
		$cache_value_arr = array($cache_value, $expires_time);
        $mem->set($cache_key, $cache_value_arr, 7100);
    }
    
    //删除缓存里的值_mamcached
    public function delete_cache($cache_key)
    {
        $mem = $this->wechat_cache;

        $mem->delete($cache_key);
    }
    */
    /*
	//初始化缓存_kvdb
    private function init_cache()
	{
        $kv = new SaeKV();
        $ret = $kv->init();
		
		return $kv;
	}
	
    //获取缓存里的值_kvdb
    private function get_cache($cache_key)
    {
        $kv = $this->wechat_cache;
        $cache = $kv->get($cache_key);

        return $cache; 
    }
    
    //往缓存里添加值_kvdb
    private function add_cache($cache_key,$cache_value)
    {
        $kv = $this->wechat_cache;
		$expires_time = $this->timestamp+7000;
		$cache_value_arr = array($cache_value, $expires_time);
        $ret = $kv->set($cache_key, $cache_value_arr);
    }
    
    //删除缓存里的值_kvdb
    public function delete_cache($cache_key)
    {
        $kv = $this->wechat_cache;
        $ret = $kv->delete($cache_key);
    }
    */
    
    //初始化缓存_mysql
    private function init_cache()
    {
        $dataBaseConfig['dataBaseHost'] = base64_decode("aHNuZXdtZWRpYS5teXNxbC5yZHMuYWxpeXVuY3MuY29t");
        $dataBaseConfig['dataBaseName'] = base64_decode("c2Fl");
        $dataBaseConfig['dataBaseServerPort'] = base64_decode("MzMwNg==");
        $dataBaseConfig['dataBaseUserName'] = base64_decode("cWl0YW8=");
        $dataBaseConfig['dataBasePassWord'] = base64_decode("bHVvZnVoYW8=");
        $dataBaseConfig['sourceName'] = "mysql:dbname={$dataBaseConfig['dataBaseName']};host={$dataBaseConfig['dataBaseHost']};port={$dataBaseConfig['dataBaseServerPort']}";
        $dbclass = 'PDO';
        //printf("<pre>%s</pre>\n",var_export( $dataBaseConfig ,TRUE));
        try {// 创建连接
            $dbh = new $dbclass($dataBaseConfig['sourceName'], $dataBaseConfig['dataBaseUserName'], $dataBaseConfig['dataBasePassWord'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
        }
        catch (PDOException $e) {// 检测连接
            die($e->getMessage());
        }
        
        return $dbh;
    }
    //获取缓存里的值_mysql
    private function get_cache($cache_key)
    {
        $dbh = $this->wechat_cache;
        $sql = "SELECT cache_value, expires_time FROM wechat_cache WHERE cache_key='$cache_key'";
        $cache = $dbh->query($sql)->fetch(PDO::FETCH_NUM);
        return $cache; 
    }
    
    //往缓存里添加值_mysql
    private function add_cache($cache_key, $cache_value)
    {
        $dbh = $this->wechat_cache;
        $expires_time = $this->timestamp+7000;
        $this->delete_cache($cache_key);
        $sql = "INSERT INTO wechat_cache (cache_key, cache_value, expires_time, datetime) VALUES ('$cache_key', '$cache_value', '$expires_time', now())";
        $result = $dbh->exec($sql);//echo $sql;die();
        if($result > 0)
            return true;
        else
            return false;
    }
    
    //删除缓存里的值_mysql
    public function delete_cache($cache_key)
    {
        $dbh = $this->wechat_cache;
        $sql = "DELETE FROM wechat_cache WHERE cache_key = '$cache_key' ";
        $result = $dbh->exec($sql);
        if($result > 0)
            return true;
        else
            return false;
    }
    
    /*
  	//初始化缓存_redis
    private function init_cache()
	{
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
		
		return $redis;
	}
    //获取缓存里的值_redis
    private function get_cache($cache_key)
    {
        $redis = $this->wechat_cache;
        $cache = $redis->lrange($cache_key,0 , 5);
        return $cache; 
    }
    
    //往缓存里添加值_redis
    private function add_cache($cache_key, $cache_value)
    {
        $redis = $this->wechat_cache;
		$expires_time = $this->timestamp+7000;
		$this->delete_cache($cache_key);
        $result1 = $redis->lpush($cache_key, $expires_time);
        $result2 = $redis->lpush($cache_key, $cache_value);
        if($result1 > 0 && $result2 > 0) 
        	return true;
        else
        	return false;
    }
    
    //删除缓存里的值_redis
    public function delete_cache($cache_key)
    {
        $redis = $this->wechat_cache;
        $result = $redis->del($cache_key);
        if($result > 0)
            return true;
        else
            return false;
    }
	*/
}
?>