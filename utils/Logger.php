<?php

class Logger {
    public static function logDB($config) {
        try {
            $logDir = __DIR__ . '/../logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            
            $logMessage = sprintf(
                "[%s] DB Connection: host=%s, db=%s, user=%s, port=%s\n",
                date('Y-m-d H:i:s'),
                $config['host'],
                $config['database'],
                $config['username'],
                $config['port']
            );
            
            file_put_contents($logDir . '/db.log', $logMessage, FILE_APPEND);
        } catch (Exception $e) {
            // Silently fail logging
        }
    }
} 