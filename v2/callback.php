<?php

define("TOKEN", "weixinweixin");
define("AppID", "");
// define("EncodingAESKey", "");
// require_once('crypt/wxBizMsgCrypt.php');

$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    //验证签名
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
            exit;
        }
    }

    //响应消息
    public function responseMsg()
    {
        $postStr = file_get_contents("php://input");
        // printf("<pre>%s</pre>\n",var_export($postStr, true));
        // printf("<pre>%s</pre>\n",var_export($_GET, true));
        if (!empty($postStr)){
            
            $timestamp  = isset($_GET['timestamp'])?$_GET['timestamp']:"";
            $nonce = isset($_GET['nonce'])?$_GET["nonce"]:"";
            $msg_signature  = isset($_GET['msg_signature'])?$_GET['msg_signature']:"";
            $encrypt_type = isset($_GET['encrypt_type'])?$_GET['encrypt_type']:"";
            
            // 加密模式
            // if ($encrypt_type == 'aes'){
            //     $pc = new WXBizMsgCrypt(TOKEN, EncodingAESKey, AppID);                
            //     // $this->logger(" D \r\n".$postStr);
            //     $decryptMsg = "";  //解密后的明文
            //     $errCode = $pc->DecryptMsg($msg_signature, $timestamp, $nonce, $postStr, $decryptMsg);
            //     $postStr3 = $decryptMsg;
            // }
            
            // $this->logger("R \r\n".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            if (($postObj->MsgType == "event") && ($postObj->Event == "subscribe" || $postObj->Event == "unsubscribe")){
                //过滤关注和取消关注事件
                if ($postObj->Event == "subscribe") {
                    $result = $this->relayPart3("http://148.70.58.43/anonymouschat/ci2/index.php/Anonymouschat/subscribe".'?'.$_SERVER['QUERY_STRING'], $postStr);
                    echo $result;
                }
            }else{
                
            }
            exit;
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                   // 1 获取当前字符串的编码
                   //$encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK","GB18030",'BIG5')); 
                   // 2 将字符编码改为utf-8
                   //$str_encode = mb_convert_encoding($str, 'UTF-8', $encode);
                   $str = $postObj->Content;
                   $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK","GB18030",'BIG5')); 
                   $str_encode = mb_convert_encoding($str, 'UTF-8', $encode);
                   $postObj->Content = $str_encode;
                   if (strstr($postObj->Content, "交友")){
                        $result = $this->relayPart3("http://148.70.58.43/anonymouschat/ci/index.php/Anonymouschat/joinchat".'?'.$_SERVER['QUERY_STRING'], $postStr);
                        echo $result;die();
                    }else{
                        $result = $this->relayPart3("http://148.70.58.43/anonymouschat/ci/index.php/Anonymouschat/chat".'?'.$_SERVER['QUERY_STRING'], $postStr);
                        echo $result;die();
                    }
                    $result = $this->receiveText($postObj);
                    break;
                case "image":
                    $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    $result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    $result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
            // $this->logger("T \r\n".$result);
            
            // 加密模式下的回复
            if ($encrypt_type == 'aes'){
                $encryptMsg = ''; //加密后的密文
                $errCode = -1;
                $text = "<xml>
                    <ToUserName><![CDATA[".$postObj->FromUserName."]]></ToUserName>
                    <FromUserName><![CDATA[".$postObj->ToUserName."]]></FromUserName>
                    <CreateTime>".$timestamp."</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[".$nonce."\n".$timestamp."]]></Content>
                </xml>";
                try{
                    $timeStamp = $timestamp;
                    $nonce = $nonce;
                    $pc = new WXBizMsgCrypt(TOKEN, EncodingAESKey, AppID);
                    $errCode = $pc->encryptMsg($text, $timeStamp, $nonce, $encryptMsg);
                }catch(Exception $e){
                    echo $e;
                }
                // if ($errCode == 0) {
                    // $result = $encryptMsg;
                // } else {
                    // $text = "<xml>
                        // <ToUserName><![CDATA[".$postObj->FromUserName."]]></ToUserName>
                        // <FromUserName><![CDATA[".$postObj->ToUserName."]]></FromUserName>
                        // <CreateTime>".$timestamp."</CreateTime>
                        // <MsgType><![CDATA[text]]></MsgType>
                        // <Content><![CDATA[".$errCode."]]></Content>
                    // </xml>";
                    // $result = $text;
                // }
                $result = $text;
                // $this->logger(" E \r\n".$result);
            }
            
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

    //接收事件消息
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
                $content = "欢迎关注方倍工作室 ";
                $content .= (!empty($object->EventKey))?("\n来自二维码场景 ".str_replace("qrscene_","",$object->EventKey)):"";
                break;
            case "unsubscribe":
                $content = "取消关注";
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "COMPANY":
                        $content = array();
                        $content[] = array("Title"=>"方倍工作室", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                        break;
                    default:
                        $content = "点击菜单：".$object->EventKey;
                        break;
                }
                break;
            case "VIEW":
                $content = "跳转链接 ".$object->EventKey;
                break;
            case "SCAN":
                $content = "扫描场景 ".$object->EventKey;
                break;
            case "LOCATION":
                $content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
                break;
            case "scancode_waitmsg":
                if ($object->ScanCodeInfo->ScanType == "qrcode"){
                    $content = "扫码带提示：类型 二维码 结果：".$object->ScanCodeInfo->ScanResult;
                }else if ($object->ScanCodeInfo->ScanType == "barcode"){
                    $codeinfo = explode(",",strval($object->ScanCodeInfo->ScanResult));
                    $codeValue = $codeinfo[1];
                    $content = "扫码带提示：类型 条形码 结果：".$codeValue;
                }else{
                    $content = "扫码带提示：类型 ".$object->ScanCodeInfo->ScanType." 结果：".$object->ScanCodeInfo->ScanResult;
                }
                break;
            case "scancode_push":
                $content = "扫码推事件";
                break;
            case "pic_sysphoto":
                $content = "系统拍照";
                break;
            case "pic_weixin":
                $content = "相册发图：数量 ".$object->SendPicsInfo->Count;
                break;
            case "pic_photo_or_album":
                $content = "拍照或者相册：数量 ".$object->SendPicsInfo->Count;
                break;
            case "location_select":
                $content = "发送位置：标签 ".$object->SendLocationInfo->Label;
                break;
            default:
                $content = "receive a new event: ".$object->Event;
                break;
        }

        if(is_array($content)){
            if (isset($content[0]['PicUrl'])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
        }
        return $result;
    }

    //接收文本消息
    private function receiveText($object)
    {
        $keyword = trim($object->Content);
        // 多客服人工回复模式
        // if (strstr($keyword, "请问在吗") || strstr($keyword, "在线客服")){
            // $result = $this->transmitService($object);
            // return $result;
        // }

        //自动回复模式
        if (strstr($keyword, "文本")){
            $content = "这是个文本消息";
        } else {
            $content = date("Y-m-d H:i:s",time())."\nOpenID：".$object->FromUserName."\n技术支持 方倍工作室";
        }

        if (is_array($content)) {
            if (isset($content[0])) {
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        } else {
            $result = $this->transmitText($object, $content);
        }
        return $result;
    }

    //接收图片消息
    private function receiveImage($object)
    {
        $content = array("MediaId"=>$object->MediaId);
        $result = $this->transmitImage($object, $content);
        return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，经度为：".$object->Location_Y."；纬度为：".$object->Location_X."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "你刚才说的是：".$object->Recognition;
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }
        return $result;
    }

    //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        if (!isset($content) || empty($content)){
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
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return "";
        }
        $itemTpl = "        <item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
        </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
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
    private function transmitMusic($object, $musicArray)
    {
        if(!is_array($musicArray)){
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
    private function transmitImage($object, $imageArray)
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
    private function transmitVoice($object, $voiceArray)
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
    private function transmitVideo($object, $videoArray)
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
    private function transmitService($object)
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
    private function relayPart3($url, $rawData)
    {
        $headers = array("Content-Type: text/xml; charset=utf-8");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    //字节转Emoji表情
    function bytes_to_emoji($cp)
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
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 1000000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('Y-m-d H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }
}
