<?php

namespace Delayer;

/**
 * Class Client
 */
class Client
{

    // 键名
    const KEY_JOP_POOL = 'delayer:jop_pool';
    const PREFIX_JOP_BUCKET = 'delayer:jop_bucket:';
    const PREFIX_READY_QUEUE = 'delayer:ready_queue:';

    // 连接地址
    public $host = '127.0.0.1';

    // 连接端口
    public $port = 6379;

    // 数据库编号
    public $database = 0;

    // 密码
    public $password = '';

    // 驱动
    protected $_driver;

    /**
     * 构造
     * @param array $config
     */
    public function __construct(array $config)
    {
        // 导入配置
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }
        // 实例化驱动
        $this->_driver = new \Redis();
        // 连接
        $this->connect();
    }

    /**
     * 连接
     * @throws \RedisException
     */
    protected function connect()
    {
        if (!$this->_driver->connect($this->host, $this->port)) {
            throw new \RedisException("Redis connection failed, {$this->host}:{$this->port}.");
        }
        $this->_driver->auth($this->password);
        $this->_driver->select($this->database);
    }

    /**
     * 增加任务
     * @param Message $message
     * @param $delayTime
     * @param int $maxLifetime
     */
    public function push(Message $message, $delayTime, $readyMaxLifetime = 604800)
    {
        // 参数验证
        if (!$message->validate()) {
            throw new \InvalidArgumentException('Invalid message.');
        }
        // 增加
        $this->_driver->multi();
        $this->_driver->hMset(self::PREFIX_JOP_BUCKET . $message->id, ['topic' => $message->topic, 'body' => $message->body]);
        $this->_driver->expire(self::PREFIX_JOP_BUCKET . $message->id, $delayTime + $readyMaxLifetime);
        $this->_driver->zAdd(self::KEY_JOP_POOL, time() + $delayTime, $message->id);
        $ret = $this->_driver->exec();
        foreach ($ret as $status) {
            if (!$status) {
                return false;
            }
        }
        return true;
    }

    /**
     * 取出任务
     * @param $topic
     * @return bool|Message
     */
    public function pop($topic)
    {
        $id = $this->_driver->rPop(self::PREFIX_READY_QUEUE . $topic);
        if (empty($id)) {
            return false;
        }
        $data = $this->_driver->hGetAll(self::PREFIX_JOP_BUCKET . $id);
        if (!isset($data['topic']) || !isset($data['body'])) {
            return false;
        }
        $this->_driver->del(self::PREFIX_JOP_BUCKET . $id);
        return new Message([
            'id'    => $id,
            'topic' => $data['topic'],
            'body'  => $data['body'],
        ]);
    }

    /**
     * 阻塞取出任务
     * @param $topic
     * @param $timeout
     * @return bool|Message
     */
    public function bPop($topic, $timeout)
    {
        $ret = $this->_driver->brPop([self::PREFIX_READY_QUEUE . $topic], $timeout);
        if (empty($ret)) {
            return false;
        }
        $id   = array_pop($ret);
        $data = $this->_driver->hGetAll(self::PREFIX_JOP_BUCKET . $id);
        if (!isset($data['topic']) || !isset($data['body'])) {
            return false;
        }
        $this->_driver->del(self::PREFIX_JOP_BUCKET . $id);
        return new Message([
            'id'    => $id,
            'topic' => $data['topic'],
            'body'  => $data['body'],
        ]);
    }

    /**
     * 移除任务
     * @param $id
     * @return bool
     */
    public function remove($id)
    {
        $this->_driver->multi();
        $this->_driver->zRem(self::KEY_JOP_POOL, $id);
        $this->_driver->del(self::PREFIX_JOP_BUCKET . $id);
        $ret = $this->_driver->exec();
        foreach ($ret as $status) {
            if (!$status) {
                return false;
            }
        }
        return true;
    }

}
