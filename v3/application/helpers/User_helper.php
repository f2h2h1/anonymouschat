<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * 用于post数据的curl函数
 */
function https_request($url, $data = NULL, $headers = array("Content-Type: text/xml; charset=utf-8"))
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	if ( ! empty($data))
	{
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($curl);
	curl_close($curl);
	return $output;
}

/**
 * 致命错误
 */
function fatal_error($describe, $data = NULL)
{
	logger($describe, $data);
	exit;
}

/**
 * 日志记录
 */
function logger($describe, $data = NULL)
{
	$log_file = date("Y-m-d").".log";
	$dir = "application/logs/anonymous_log";
	if (!is_dir($dir))
	{
		mkdir($dir, 0777, true);
	}
	$path = $dir."/".$log_file;
	$file = fopen($path, "a");
	if ( ! $file)
	{
		return;
	}
	$br = "\r\n";
	$txt = "[time]".date('H:i:s').$br;
	$txt .= "[describe]".$describe.$br;
	if ( ! empty($_SERVER['REMOTE_ADDR']))
	{
		$txt .= "[ip]".$_SERVER['REMOTE_ADDR'].$br;
	}
	if ( ! empty($_SERVER['REQUEST_URI']))
	{
		$txt .= "[request_uri]".$_SERVER['REQUEST_URI'].$br;
	}
	if ( ! empty($data))
	{
		$txt .= "[data]".$br;
		$txt .= sprintf("%s", var_export($data, TRUE)).$br;
	}
	$txt .= $br;
	fwrite($file, $txt);
	fclose($file);
}

function failed_query(string $file, string $line, array $db_error) : void
{
	$data = ['file' => $file, 'line' => $line, 'db_error' => $db_error];
	logger('数据库错误，query执行失败', $data);

	if (defined('IN_WECHAT') && IN_WECHAT)
	{
		wechat_exit();
	}
}

function db_result_null(string $file, string $line, array $db_error) : void
{
	if ($db_error['code'] !== '00000')
	{
		failed_query(__FILE__, __LINE__, $db_error);
	}
}

function wechat_exit()
{
	$xmlTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
				</xml>";

	if (defined('OPENID') && defined('GHID'))
	{
		$result = sprintf($xmlTpl, OPENID, GHID, time(), '系统繁忙，请稍后再试。');
		echo $result;
	}
	exit;
}
