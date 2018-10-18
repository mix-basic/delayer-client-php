<?php

namespace Delayer;

/**
 * Class Message
 * @package Delayer
 */
class Message
{

    // 任务id
    public $id;

    // 主题
    public $topic;

    // 正文
    public $body;

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
    }

    /**
     * 验证
     * @return bool
     */
    public function validate()
    {
        foreach ($this as $attribute) {
            if (is_null($attribute) || $attribute === '') {
                return false;
            }
        }
        return true;
    }

}
