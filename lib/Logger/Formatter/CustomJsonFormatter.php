<?php
namespace Lib\Logger\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class CustomJsonFormatter extends NormalizerFormatter
{
    private $extraInfo = [
        'SERVER_NAME',
        'REQUEST_URI',
        'HTTP_INCAP_CLIENT_IP',
        'REMOTE_ADDR',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_REFERER'
    ];

    private function getServerInfo()
    {
        $i = [];
        foreach ($this->extraInfo as $key) {
            if (isset($_SERVER[$key])) {
                $i[$key] = $_SERVER[$key];
            }
        }

        return $i;
    }

    public function format(LogRecord $record): string
    {
        $data = [
            'time'       => $record->datetime->format('Y-m-d H:i:s'),
            'type'       => $record->level->getName(),
            'process_id' => getLogUuid(),
            'info'       => json_decode($record->message),
            'sessionId'  => getSession() ?? '',
            'context'    => $record->context,
            'extra'      => $record->extra,
            'serverInfo' => $this->getServerInfo()
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}