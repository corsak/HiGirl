<?
define("TOKEN", "weixin");
require_once "./user.class.php";

class wechatCallbackapi
{
    private $oneUser = null;

    /**
     * 构造函数
     *
     */
    public function __construct()
    {
        $this->oneUser = new user();
    }

    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            switch ($RX_TYPE) {
                //接收的是 事件 类型
                case "event":
                    $resultStr = $this->receiveEvent($postObj);
                    break;

                //接收的是 消息 类型，这里提供 text、image、voice
                case "text":
                    $resultStr = $this->receiveText($postObj);
                    break;
                case "image":
                    $resultStr = $this->receiveImage($postObj);
                    break;
                case "voice":
                    $resultStr = $this->receiveVoice($postObj);
                    break;
                default:
                    $resultStr = "【系统提示】<br>此类型消息不支持。仅支持文字、图片、语音 等消息类型";
                    break;
            }
            echo $resultStr;
        } else {
            echo "";
            exit;
        }
    }


    private function receiveEvent($object)
    {
        $contentStr = "";
        switch ($object->Event) {
            case "subscribe":
                $contentStr = "同学，你好。欢迎关注你好同学^.^ ";
            case "unsubscribe":
                break;
            case "CLICK":
                switch ($object->EventKey) {
                    case "company":
                        $contentStr[] = array("Title" => "你好同学",
                            "Description" => "微信匿名社交平台",
                            "PicUrl" => "...",
                            "Url" => "...");
                        break;

                    case "start":
                        //$contentStr = $object->FromUserName;
                        //break;
                        //var_dump ( $this->oneUser->checkUserReg2($object->FromUserName) );
                        $rst = $this->oneUser->checkUserReg2($object->FromUserName);
                        //$contentStr = $rst['code'];
                        //break;
                        if ($rst['code'] == 1001) {
                            //未注册，需要先注册填写信息
                            $contentStr = $rst['message'];
                            $contentStr .= "<br>填写地址：";
                            $contentStr .= "<a href=\"http://www.yzywnet.com/HiGirl/form.php?OpenID=" . $object->FromUserName . "\">注册您的信息</a>";
                            break;
                        } else {
                            //已经注册，接着判断队列状态是否在 聊天队列。
                            if ($rst['data']['queueStatus'] == 3) {
                                $contentStr = "【系统提示】<br>同学，你已经在聊天队列，若要重新匹配，请先断开此次连接！";
                                break;
                            } else {
                                //发起匹配
                                //$contentStr = $rst['data']['OpenID'];
                                //$contentStr .= $rst['data']['sex'];
                                $rst = $this->oneUser->matchObject($rst['data']['OpenID'], $rst['data']['sex']);
                                if ($rst['code'] == 1002) {
                                    $contentStr = $rst['message'];
                                    break;
                                } else {
                                    $contentStr = $rst['message'];
                                    break;
                                }
                            }
                        }
                    //$contentStr = $rst;
                    //$contentStr = "开始匹配操作中。。。";
                    //break;
                    case "end":
                        $rst = $this->oneUser->checkUserReg2($object->FromUserName);
                        if ($rst['code'] == 1001) {
                            //未注册，需要先注册填写信息
                            $contentStr = $rst['message'];
                            $contentStr .= "<br>填写地址：";
                            $contentStr .= "<a href=\"http://www.yzywnet.com/HiGirl/form.php?OpenID=" . $object->FromUserName . "\">注册您的信息</a>";
                            break;
                        } else {
                            //已经注册，接着判断队列状态是否在 聊天队列。
                            if ($rst['data']['queueStatus'] == 3) {
                                $contentStr = "【系统提示】<br>相遇是缘分，同学你确定要这样吗?" . "<a href=\"http://www.yzywnet.com/HiGirl/disconnectObject.php?OpenID=" . $object->FromUserName . "\">残忍断开</a>";
                                break;
                            } else {
                                $contentStr = "【系统提示】<br>同学，你都还没开始聊天呢，怎么能断开连接呢。请先发起匿名聊天。";
                                break;
                            }
                        }
                    //$contentStr = $this->oneUser->test();
                    //$contentStr = "断开匹配操作中。。。";
                    //break;

                    case "topic":
                        $rst = $this->oneUser->checkUserReg2($object->FromUserName);
                        if ($rst['code'] == 1001) {
                            //未注册，需要先注册填写信息
                            $contentStr = $rst['message'];
                            $contentStr .= "<br>填写地址：";
                            $contentStr .= "<a href=\"http://www.yzywnet.com/HiGirl/form.php?OpenID=" . $object->FromUserName . "\">注册您的信息</a>";
                            break;
                        } else {
                            //已经注册，接着判断队列状态是否在 聊天队列。
                            if ($rst['data']['queueStatus'] == 3) {
                                $fromUsername = $object->FromUserName;
                                $contentStr = $this->oneUser->sendTopic($fromUsername);

                                break;
                            } else {
                                $contentStr = "【系统提示】<br>同学，先匹配，再开始聊天。";
                                break;
                            }
                        }
                        break;

                    default:
                        $contentStr[] = array("Title" => "你好同学",
                            "Description" => "微信匿名社交平台",
                            "PicUrl" => "...",
                            "Url" => "...");
                        break;
                }
                break;

            default:
                break;

        }
        if (is_array($contentStr)) {
            $resultStr = $this->transmitNews($object, $contentStr);
        } else {
            /*if ($contentStr != 0) {
                $resultStr = $this->transmitText($object, $contentStr);
            }*/
            $resultStr = $this->transmitText($object, $contentStr);
        }
        return $resultStr;
    }


    private function receiveText($object)
    {
        $rst = $this->oneUser->checkUserReg2($object->FromUserName);
        if ($rst['code'] == 1001) {
            //未注册，需要先注册填写信息
            $contentStr = $rst['message'];
            $contentStr .= "<br>填写地址：";
            $contentStr .= "<a href=\"http://www.yzywnet.com/HiGirl/form.php?OpenID=" . $object->FromUserName . "\">注册您的信息</a>";
            $resultStr = $this->transmitText($object, $contentStr);
            return $resultStr;
        } else {
            //已经注册，接着判断队列状态是否在 聊天队列。
            if ($rst['data']['queueStatus'] == 3) {
                $fromUsername = $object->FromUserName;
                $toUsername = $object->ToUserName;
                $funcFlag = 0;
                $content = $object->Content;
                $contentStr = $this->oneUser->sendMsg($fromUsername, 'text', $content);
                if ($contentStr != 0) {
                    $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
                    return $resultStr;
                }
            } else {
                $contentStr = "【系统提示】<br>同学，先匹配，再开始聊天。";
                $resultStr = $this->transmitText($object, $contentStr);
                return $resultStr;

            }
        }
    }


    private function receiveImage($object)
    {
        $rst = $this->oneUser->checkUserReg2($object->FromUserName);
        if ($rst['code'] == 1001) {
            //未注册，需要先注册填写信息
            $contentStr = $rst['message'];
            $contentStr .= "<br>填写地址：";
            $contentStr .= "<a href=\"http://www.yzywnet.com/HiGirl/form.php?OpenID=" . $object->FromUserName . "\">注册您的信息</a>";
            $resultStr = $this->transmitText($object, $contentStr);
            return $resultStr;
        } else {
            //已经注册，接着判断队列状态是否在 聊天队列。
            if ($rst['data']['queueStatus'] == 3) {
                $fromUsername = $object->FromUserName;
                $toUsername = $object->ToUserName;
                $funcFlag = 0;
                $MediaId = $object->MediaId;
                $contentStr = $this->oneUser->sendMsg($fromUsername, 'image', $MediaId);
                if ($contentStr != 0) {
                    $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
                    return $resultStr;
                }
            } else {
                $contentStr = "【系统提示】<br>同学，先匹配，再开始聊天。";
                $resultStr = $this->transmitText($object, $contentStr);
                return $resultStr;

            }
        }
    }

    private function receiveVoice($object)
    {
        $rst = $this->oneUser->checkUserReg2($object->FromUserName);
        if ($rst['code'] == 1001) {
            //未注册，需要先注册填写信息
            $contentStr = $rst['message'];
            $contentStr .= "<br>填写地址：";
            $contentStr .= "<a href=\"http://www.yzywnet.com/HiGirl/form.php?OpenID=" . $object->FromUserName . "\">注册您的信息</a>";
            $resultStr = $this->transmitText($object, $contentStr);
            return $resultStr;
        } else {
            //已经注册，接着判断队列状态是否在 聊天队列。
            if ($rst['data']['queueStatus'] == 3) {
                $fromUsername = $object->FromUserName;
                $toUsername = $object->ToUserName;
                $funcFlag = 0;
                $MediaId = $object->MediaId;
                $contentStr = $this->oneUser->sendMsg($fromUsername, 'voice', $MediaId);
                if ($contentStr != 0) {
                    $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
                    return $resultStr;
                }
            } else {
                $contentStr = "【系统提示】<br>同学，先匹配，再开始聊天。";
                $resultStr = $this->transmitText($object, $contentStr);
                return $resultStr;

            }
        }
    }


    private function transmitText($object, $content, $funcFlag = 0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $funcFlag);
        return $resultStr;
    }

    private function transmitNews($object, $arr_item, $funcFlag = 0)
    {
        //首条标题28字，其他标题39字
        if (!is_array($arr_item))
            return;

        $itemTpl = "    <item>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            <PicUrl><![CDATA[%s]]></PicUrl>
                            <Url><![CDATA[%s]]></Url>
                        </item>
                    ";
        $item_str = "";
        foreach ($arr_item as $item)
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);

        $newsTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <Content><![CDATA[]]></Content>
                    <ArticleCount>%s</ArticleCount>
                    <Articles>
                    $item_str</Articles>
                    <FuncFlag>%s</FuncFlag>
                    </xml>";

        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item), $funcFlag);
        return $resultStr;
    }

    private function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

}
