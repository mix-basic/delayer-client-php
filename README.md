## Delayer PHP 客户端

客户端使用非常简单，提供了 `push`、`pop`、`bPop`、`remove` 四个方法操作任务。

## 安装

通过 Composer 安装使用：

```shell
composer require mixstart/delayer-client-php:1.*
```

## Example

### `push` 方法

放入一个任务。

```php
<?php
include '../vendor/autoload.php';
// 要与Delayer服务器端配置的redis信息相同
$config  = [
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'database' => 0,
    'password' => '',
];
$client  = new \Delayer\Client($config);
// 任务数据，用户自己定义
$data    = [
    'orderID' => '2018101712578956648885474',
    'action'  => 'close',
];
$message = new \Delayer\Message([
    // 任务ID，必须全局唯一
    'id'    => md5(uniqid(mt_rand(), true)),
    // 主题，取出任务时需使用
    'topic' => 'close_order',
    // 必须转换为string类型
    'body'  => json_encode($data),
]);
// 第2个参数为延迟时间，第3个参数为延迟到期后如果任务没有被消费的最大生存时间
$ret     = $client->push($message, 20, 604800);
var_dump($ret);
```

### `pop` 方法

取出一个到期的任务。

```php
<?php
include '../vendor/autoload.php';
// 要与Delayer服务器端配置的redis信息相同
$config  = [
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'database' => 0,
    'password' => '',
];
$client  = new \Delayer\Client($config);
$message = $client->pop('close_order');
// 没有任务时，返回false
var_dump($message);
var_dump($message->body);
```

### `bPop` 方法

阻塞取出一个到期的任务。

```php
<?php
include '../vendor/autoload.php';
// 要与Delayer服务器端配置的redis信息相同
$config  = [
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'database' => 0,
    'password' => '',
];
$client  = new \Delayer\Client($config);
$message = $client->bPop('close_order', 10);
// 没有任务时，返回false
var_dump($message);
var_dump($message->body);
```

### `remove` 方法

移除一个未到期的任务。

```php
<?php
include '../vendor/autoload.php';
// 要与Delayer服务器端配置的redis信息相同
$config = [
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'database' => 0,
    'password' => '',
];
$client = new \Delayer\Client($config);
// push时定义的任务ID
$id = '***';
$ret    = $client->remove($id);
var_dump($ret);
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
