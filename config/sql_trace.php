<?php

return [

    /**
     * 是否开启监听
     */
    'open_listen_sql' => true,

    /**
     * 日志的路径, 默认在 storage/logs/ 下
     */
    'sql_log_path' => '',

    /**
     * 慢 SQL 时间 (ms)
     */
    'slow_sql_time' => 1000,

    /**
     * 严重警告的 sql 执行时间 (ms)
     */
    'danger_sql_time' => 3000

];