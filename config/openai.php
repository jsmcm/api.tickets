<?php

return [
    "apiKey"        => env("OPENAI_API_KEY", ""),
    "model"         => env("OPENAI_MODEL", ""),
    "maxTokens"     => env("OPENAI_MAX_TOKENS", 3),
    "temperature"   => env("OPENAI_TEMPERATURE", 0),
];
