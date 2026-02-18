<?php

return [
    // Comma-separated list of keys to redact from logs. Can be set via the
    // `LOG_REDACT_KEYS` environment variable for runtime control.
    // Example: LOG_REDACT_KEYS=password,api_key,secret,token
    'keys' => array_filter(array_map('trim', explode(',', env('LOG_REDACT_KEYS', 'password,pass,pwd,secret,token,access_token,refresh_token,api_key,apikey,auth,authorization,ssn,credit_card,card_number,private_key,aws_secret,aws_secret_access_key,db_password')))),
];
