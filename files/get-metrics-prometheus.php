<?php

header('Status: 200 OK');
header('Content-Type: text/plain; charset=UTF-8');

if (extension_loaded('elastic_apm')) {
    Elastic\Apm\ElasticApm::getCurrentTransaction()->discard();
}

$time = time();
$metrics = [];

$apcu = extension_loaded('apcu') && apcu_enabled();
$keyOpcache = '/get-metrics-prometheus.php/opcache';
$keyFpm = '/get-metrics-prometheus.php/fpm';

if (function_exists('opcache_get_status')) {
    $metrics[] = metrics_opcache_status();
}

if (function_exists('opcache_get_configuration')) {
    $metrics[] = metrics_apcu($apcu, $keyOpcache, 'metrics_opcache_config');
}

if (function_exists('fpm_get_status')) {
    $status = fpm_get_status();

    if (false !== $status) {
        $pool = $status['pool'];

        $metrics[] = metrics_fpm_status($status, $pool);

        $metrics[] = metrics_apcu($apcu, "$keyFpm/$pool", static function () use ($pool) {
            return metrics_fpm_config($pool);
        });
    }
}

$metrics = array_merge(...$metrics);

if (count($metrics) > 0) {
    $metrics[] = '';

    echo implode(" {$time}\n", $metrics);
}

function metrics_apcu($apcu, $key, $callable)
{
    if ($apcu) {
        $metrics = apcu_fetch($key);

        if (false === $metrics) {
            $metrics = $callable();

            apcu_store($key, $metrics, 86400);
        }
    } else {
        $metrics = $callable();
    }

    return $metrics;
}

function metrics_opcache_status()
{
    $status = opcache_get_status(false);

    if (false !== $status && false !== $status['opcache_enabled']) {
        $memMax = $status['memory_usage']['free_memory'] + $status['memory_usage']['used_memory'] + $status['memory_usage']['wasted_memory'];
        $memStringsMax = $status['interned_strings_usage']['used_memory'] + $status['interned_strings_usage']['free_memory'];

        $metrics = [
            "php_opcache_restarts{reason=\"oom\"} {$status['opcache_statistics']['oom_restarts']}",
            "php_opcache_restarts{reason=\"hash\"} {$status['opcache_statistics']['hash_restarts']}",
            "php_opcache_restarts{reason=\"manual\"} {$status['opcache_statistics']['manual_restarts']}",
            "php_opcache_num_cached_scripts {$status['opcache_statistics']['num_cached_scripts']}",
            "php_opcache_num_cached_keys {$status['opcache_statistics']['num_cached_keys']}",
            "php_opcache_max_cached_keys {$status['opcache_statistics']['max_cached_keys']}",
            "php_opcache_hits {$status['opcache_statistics']['hits']}",
            "php_opcache_misses {$status['opcache_statistics']['misses']}",
            "php_opcache_blacklist_misses {$status['opcache_statistics']['blacklist_misses']}",
            "php_opcache_used_memory {$status['memory_usage']['used_memory']}",
            "php_opcache_wasted_memory {$status['memory_usage']['wasted_memory']}",
            "php_opcache_max_memory {$memMax}",
            "php_opcache_strings_used_memory {$status['interned_strings_usage']['used_memory']}",
            "php_opcache_strings_max_memory {$memStringsMax}",
        ];

        if (isset($status['jit']) && false !== $status['jit']['enabled']) {
            $memJitUsed = $status['jit']['buffer_size'] - $status['jit']['buffer_free'];

            $metrics[] = "php_opcache_jit_used_memory {$memJitUsed}";
            $metrics[] = "php_opcache_jit_max_memory {$status['jit']['buffer_size']}";
        }
    } else {
        $metrics = [];
    }

    return $metrics;
}

function metrics_opcache_config()
{
    $config = opcache_get_configuration();

    if (false !== $config) {
        $memStringsMax = $config['directives']['opcache.interned_strings_buffer'] * 1048576;

        return [
            "php_opcache_config_max_memory {$config['directives']['opcache.memory_consumption']}",
            "php_opcache_config_strings_max_memory {$memStringsMax}",
            "php_opcache_config_max_cached_keys {$config['directives']['opcache.max_accelerated_files']}",
            "php_opcache_config_max_wasted_memory_percentage {$config['directives']['opcache.max_wasted_percentage']}",
        ];
    }

    return [];
}

function metrics_fpm_status($status, $pool)
{
    return [
        "php_fpm_requests_total{pool=\"$pool\"} {$status['accepted-conn']}",
        "php_fpm_requests_slow_total{pool=\"$pool\"} {$status['slow-requests']}",
        "php_fpm_requests_queue{pool=\"$pool\"} {$status['listen-queue']}",
        "php_fpm_requests_queue_max{pool=\"$pool\"} {$status['listen-queue-len']}",
        "php_fpm_processes_used{pool=\"$pool\", status=\"active\"} {$status['active-processes']}",
        "php_fpm_processes_used{pool=\"$pool\", status=\"idle\"} {$status['idle-processes']}",
        "php_fpm_processes_max_reached_total{pool=\"$pool\"} {$status['max-children-reached']}",
    ];
}

function metrics_fpm_config($pool)
{
    $directives = [
        'request_slowlog_timeout' => 'php_fpm_config_requests_slow_timeout',
        'request_terminate_timeout' => 'php_fpm_config_requests_terminate_timeout',
        'pm.max_children' => 'php_fpm_config_processes_max',
        'pm.min_spare_servers' => 'php_fpm_config_processes_spare_min',
        'pm.max_spare_servers' => 'php_fpm_config_processes_spare_max',
    ];

    $pipes = [];
    $matches = [];
    $results = [];
    $text = null;

    $file = proc_open('php-fpm -tt', [2 => ['pipe', 'w']], $pipes);

    if (is_resource($file)) {
        try {
            if (is_resource($pipes[2])) {
                try {
                    $text = stream_get_contents($pipes[2]);
                } finally {
                    fclose($pipes[2]);
                }
            }
        } finally {
            proc_close($file);
        }
    }

    if (is_string($text) && '' !== $text) {
        foreach ($directives as $directive => $name) {
            if (1 === preg_match("/{$directive} = (\d+)([smh]?)\s/", $text, $matches)) {
                if ('m' === $matches[2]) {
                    $matches[1] *= 60;
                } elseif ('h' === $matches[2]) {
                    $matches[1] *= 60 * 60;
                }

                $results[] = "{$name}{pool=\"$pool\"} {$matches[1]}";
            }
        }
    }

    return $results;
}
