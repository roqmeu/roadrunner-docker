<?php

if (extension_loaded('elastic_apm')) {
    Elastic\Apm\ElasticApm::getCurrentTransaction()->discard();
}

header('Status: 200 OK');
header('Content-Type: text/plain; charset=UTF-8');

echo 'PONG';
