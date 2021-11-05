# 磁盘可用空间监控

一个简单的PHP脚本，用于检查当前磁盘的可用空间，目前只接入了飞书群机器人消息，当磁盘可用空间小于设定的阈值时会向飞书群发送报警消息。

## 脚本配置

打开文件，顶部的Config类里的变量就是配置项，修改对应值即可。

|           配置项           | 描述                                                   |
| :------------------------: | ------------------------------------------------------ |
|        Config::$ip         | 机器的ip，用于对机器做区分，通过报警消息知道是哪台机器 |
|       Config::$path        | 要检查的磁盘目录，数组，可以有多个目录                 |
| Config::$allowMinFreeSpace | 允许的最小可用空间，单位：GB                           |
|  Config::$larkRobotApiUrl  | 飞书机器人API地址                                      |
|  Config::$larkRobotApiKey  | 飞书机器人API Key                                      |

## 定时监控

可通过crontab来进行定时监控，如每两小时检查一次：

```shell
*/120 * * * * php /home/user/free_space_monitor.php > /dev/null 2>/dev/null
```

