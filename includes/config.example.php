<?php
// Copy this file to config.local.php on the hosting account and add your private API key.
// Never commit config.local.php or expose API keys in browser JavaScript.
return [
    'ai_api_key' => 'YOUR_AI_API_KEY',
    'ai_model' => 'gpt-4o-mini',
    'ai_endpoint' => 'https://api.openai.com/v1/chat/completions'
];
