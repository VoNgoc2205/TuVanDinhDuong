<?php

define("USDA_API_KEY", getenv("USDA_API_KEY") ?: "KEY_USDA");

define("OPENAI_API_KEY", getenv("OPENAI_API_KEY") ?: "KEY_OPENAI");
define("OPENAI_MODEL", getenv("OPENAI_MODEL") ?: "gpt-4o-mini");

// =========================
// CALL OPENAI (CORE)
// =========================
function callOpenAIAPI($data)
{
    $input = [];

    if (!empty($data['contents']) && is_array($data['contents'])) {
        foreach ($data['contents'] as $contentBlock) {
            if (!isset($contentBlock['parts']) || !is_array($contentBlock['parts'])) {
                continue;
            }

            $content = [];
            foreach ($contentBlock['parts'] as $part) {
                if (isset($part['text'])) {
                    $content[] = ['type' => 'input_text', 'text' => $part['text']];
                    continue;
                }

                if (isset($part['inline_data'])) {
                    $inline = $part['inline_data'];
                    $mimeType = $inline['mime_type'] ?? 'image/jpeg';
                    $content[] = [
                        'type' => 'input_image',
                        'image_url' => "data:{$mimeType};base64," . ($inline['data'] ?? ''),
                        'detail' => 'auto'
                    ];
                }
            }

            if (!empty($content)) {
                $input[] = ['role' => 'user', 'content' => $content];
            }
        }
    } elseif (!empty($data['content']) && is_array($data['content'])) {
        $content = [];
        foreach ($data['content'] as $part) {
            if (($part['type'] ?? '') === 'input_text' && isset($part['text'])) {
                $content[] = ['type' => 'input_text', 'text' => $part['text']];
                continue;
            }

            if (($part['type'] ?? '') === 'input_image') {
                $image = $part['image'] ?? [];
                $mimeType = $image['mime_type'] ?? 'image/jpeg';
                $imageData = $image['data'] ?? ($part['image_url'] ?? '');
                $content[] = [
                    'type' => 'input_image',
                    'image_url' => strpos($imageData, 'data:') === 0 ? $imageData : "data:{$mimeType};base64,{$imageData}",
                    'detail' => 'auto'
                ];
            }
        }

        if (!empty($content)) {
            $input[] = ['role' => 'user', 'content' => $content];
        }
    } elseif (!empty($data['text'])) {
        $input = trim($data['text']);
    }

    $payload = [
        'model' => $data['model'] ?? OPENAI_MODEL,
        'input' => $input,
        'temperature' => $data['temperature'] ?? 0.3,
        'max_output_tokens' => $data['max_output_tokens'] ?? ($data['maxOutputTokens'] ?? 512)
    ];

    $ch = curl_init("https://api.openai.com/v1/responses");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . OPENAI_API_KEY
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    $result = json_decode($response, true);
    if (!is_array($result) || isset($result['error'])) {
        return null;
    }

    return $result;
}
