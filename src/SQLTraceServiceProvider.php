<?php

namespace ZhyCode\LaravelSqlTrace;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\ServiceProvider;
use DB;

class SQLTraceServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     * @return void
     */
    public function boot(): void
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $isOpenListen = config('sql_trace.open_listen_sql');
        $sqlLogPath = config('sql_trace.sql_log_path') ?: storage_path('/logs/');

        if ($isOpenListen) {
            $traceId = $_REQUEST['trace_id'] ?? uniqid(bin2hex(random_bytes(10)),true);

            DB::listen(function (QueryExecuted $sql) use ($traceId, $sqlLogPath) {
                // 处理绑定的数据
                foreach ($sql->bindings as $i => $binding) {
                    if ($binding instanceof \DateTime) {
                        $sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                    } else {
                        if (is_string($binding)) {
                            $sql->bindings[$i] = "'$binding'";
                        }
                    }
                }

                $trace_sql = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);
                $trace_sql = str_replace(array('\r\n', '\r', '\n'),' ', $trace_sql);
                $trace_sql = vsprintf($trace_sql, $sql->bindings);
                foreach ($sql->bindings as $key => $val) {
                    if (!is_numeric($key)) {
                        $trace_sql = str_replace(':' . $key, $val, $trace_sql);
                    }
                }

                $dbConf = $sql->connection->getConfig();
                if (isset($dbConf['password'])) {
                    unset($dbConf['password']);
                }

                $logPath = $sqlLogPath . 'sql-trace-' . now()->toDateString() . '.log';

                file_put_contents($logPath,json_encode([
                        'level' => $this->getSqlLevelByExecTime($sql->time),
                        'app_env' => config('app.env') ?? '',
                        'app_name' => config('app.name') ?? '',
                        'req_uri' => request()->url() ?? '',
                        'req_method' => request()->method() ?? '',
                        'req_body' => request()->all() ?? null,
                        'db_conf' => $dbConf,
                        'db_connection_name' => $sql->connectionName,
                        'trace_id' => $traceId,
                        'trace_datetime' => now()->toDateTimeString(),
                        'trace_sql_bindings' => $sql->bindings,
                        'execute_sql' => $trace_sql,
                        'execute_ms' => $sql->time,
                    ]).PHP_EOL,FILE_APPEND);
            });
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sql_trace.php', 'sql_trace');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['SqlTrace'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes(
            [
                __DIR__.'/../config/sql_trace.php' => config_path('config.php'),
            ],
            'sql_trace.config'
        );
    }

    /**
     * 根据执行时间,返回 SQL 执行等级
     * @param $time
     * @return string
     */
    protected function getSqlLevelByExecTime($time)
    {
        $traceConfig = config('trace');

        $dangerTime = $traceConfig['danger_sql_time'] ?? 0;

        $warnTime = $traceConfig['slow_sql_time'] ?? 0;

        if ($dangerTime && $time >= $dangerTime) {
            return 'danger';
        } elseif ($warnTime && $time >= $warnTime) {
            return 'warning';
        } else {
            return 'info';
        }
    }
}
