# Laravel-SQL-Trave

### 介绍
利用 Laravel 框架本身的 SQL 监控，通过 SQL 监听，完成 SQL 的日志记录; 主要应用于本地/测试环境的开发，涉及 SQL 日志，不要用于生产环境！！！  
该生成的日志，可以直接使用，也可以配合 LogViewer 工具使用（最佳实践方案）

### 部署

#### composer 加载
```
  cd project
  composer require zhy-code/laravel-sql-trace:1.0.1
  git checkout composer.json
  php artisan package:discover
```

#### 修改配置文件
```
   verdor/zhy-code/laravel-sql-trace/config/sql_trace.php
```


### 配合 LogViewer 的内容
#### 正则表达式
```
    public static string $regex = '/^{"level":"(?<level>.*)","app_env":"(?<app_env>\S+)","app_name":"(?<app_name>\S+)","req_uri":"(?<req_uri>\S+)","req_method":"(?<req_method>\S+)","req_body":(?<req_body>.*),"db_conf":(?<db_conf>.*),"db_connection_name":"(?<db_connection_name>.*)","trace_id":"(?<trace_id>.*)","trace_datetime":"(?<trace_datetime>.*)","trace_sql_bindings":(?<trace_sql_bindings>.*),"execute_sql":"(?<execute_sql>.*)","execute_ms":(?<execute_ms>.*)}/';

```
