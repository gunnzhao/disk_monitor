<?php

class Config
{
	// 服务器IP
	public static $ip = '127.0.0.1';
	// 要检查的磁盘目录
	public static $path = [
		'/',
	];
    // 允许的最小可用空间(GB)
    public static $allowMinFreeSpace = 3;
	// 飞书机器人API地址
	public static $larkRobotApiUrl = '';
	// 飞书机器人API Key
	public static $larkRobotApiKey = '';
}

$diskUsedStatus = checkDiskSpace(Config::$path);
if ($diskUsedStatus['alarm']) {
	sendLarkMsg($diskUsedStatus['alarm']);
}

/**
 * 检查目录对应的磁盘可用空间
 * 
 * @param array $path 目录集合
 * @return array
 */
function checkDiskSpace($pathArr)
{
	$result = [
		'alarm' => [],
		'normal' => []
	];

	foreach ($pathArr as $path) {
		$total = byte2gb(disk_total_space($path));
		$free = byte2gb(disk_free_space($path));

		if ($free <= Config::$allowMinFreeSpace) {
			$result['alarm'][] = [
				'path' => $path,
				'total_space' => $total,
				'free_space' => $free
			];
		} else {
			$result['normal'][] = [
				'path' => $path,
				'total_space' => $total,
				'free_space' => $free
			];
		}
	}

	return $result;
}

/**
 * 字节换算成gb
 * 
 * @param int $byte 字节数
 * @return float
 */
function byte2gb($byte)
{
	return round(($byte / (1024 * 1024 * 1024)), 1);
}

/**
 * 发送飞书消息
 * 
 * @param array $alarmData 要报警的数据
 * @return void
 */
function sendLarkMsg($alarmData)
{
	$contents = [
		'**机器IP: ' . Config::$ip . '**',
	];

	foreach ($alarmData as $data) {
		$contents[] = $data['path'] . ' 磁盘总空间: **' . $data['total_space'] . 'G** 可用空间: **' . $data['free_space'] . 'G**';
	}
	$contents[] = '火速处理';

	$content = trim(implode(PHP_EOL, $contents));

	$card = [
        'config' => [
            'wide_screen_mode' => true
        ],
        'header' => [
            'template' => 'red',
            'title' => [
                'content' => '磁盘空间不足警报',
                'tag' => 'plain_text',
            ]
        ],
        'elements' => [
            [
                'fields' => [
                    [
                        'is_short' => true,
                        'text' => [
                            'content' => $content,
                            'tag' => 'lark_md',
                        ],
                    ]
                ],
                'tag' => 'div',
            ]
        ]
    ];

    $timestamp = time();
    $key = Config::$larkRobotApiKey;

    $data = [
        'timestamp' => $timestamp,
        'sign' => base64_encode(hash_hmac('sha256', '', "{$timestamp}\n{$key}", true)),
        'msg_type' => 'interactive',
        'card' => $card
    ];

    httpPost(Config::$larkRobotApiUrl, $data);
}

/**
 * 发送POST请求
 * 
 * @param array $url 请求地址
 * @param array $data 请求数据
 * @return
 */
function httpPost($url, $data)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	$response = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}
