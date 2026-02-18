<?php

namespace App\Logging;

use Monolog\Logger;

/**
 * Monolog tap that adds a processor to redact sensitive keys in log records.
 */
class RedactSensitiveData
{
    /**
     * Invoke the tap.
     *
     * @param  \Monolog\Logger  $logger
     * @return void
     */
    public function __invoke(Logger $logger)
    {
        $logger->pushProcessor(function (array $record) {
            $configured = config('logging_redaction.keys', []);
            $sensitiveKeys = is_array($configured) && count($configured) > 0
                ? $configured
                : [
                    'password', 'pass', 'pwd', 'secret', 'token', 'access_token', 'refresh_token',
                    'api_key', 'apikey', 'auth', 'authorization', 'ssn', 'credit_card',
                    'card_number', 'private_key', 'aws_secret', 'aws_secret_access_key', 'db_password',
                    // Additional sensitive keys to redact
                    'sentry_auth_token', 'sentry_dsn', 'aws_access_key_id', 'stripe_secret', 'stripe_token',
                ];

            $redact = function ($value) use (&$redact, $sensitiveKeys) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        // If key looks sensitive, replace with placeholder
                        if (in_array(strtolower((string) $k), $sensitiveKeys, true)) {
                            $value[$k] = '[REDACTED]';
                        } else {
                            $value[$k] = $redact($v);
                        }
                    }
                    return $value;
                }

                if (is_string($value)) {
                    // redact common inline patterns
                    $value = preg_replace('/(password|pwd|pass|api_key|apikey|token|access_token)=([^&\s,;]+)/i', '$1=[REDACTED]', $value);
                    $value = preg_replace('/Authorization:\s*Bearer\s+([^\s,;]+)/i', 'Authorization: Bearer [REDACTED]', $value);
                    return $value;
                }

                return $value;
            };

            if (isset($record['context']) && is_array($record['context'])) {
                $record['context'] = $redact($record['context']);
            }

            if (isset($record['extra']) && is_array($record['extra'])) {
                $record['extra'] = $redact($record['extra']);
            }

            // Also scrub message
            if (! empty($record['message']) && is_string($record['message'])) {
                $record['message'] = $redact($record['message']);
            }

            return $record;
        });
    }
}
