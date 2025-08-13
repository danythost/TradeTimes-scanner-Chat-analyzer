<?php

require 'vendor/autoload.php';

use OpenAI\Client;
use Dotenv\Dotenv;

header('Content-Type: application/json');

try {
    // Load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $apiKey = $_ENV['OPENAI_API_KEY'];
    $client = OpenAI::client($apiKey);

   // Validate image
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    throw new Exception("Image upload failed.");
}

$tmpPath = $_FILES['image']['tmp_name'];
$imageMime = mime_content_type($tmpPath);
$allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];

if (!in_array($imageMime, $allowed)) {
    throw new Exception("Unsupported image format: $imageMime");
}

// checking folder exist
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Save uploaded file
$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = uniqid('upload_', true) . '.' . $ext;
$uploadPath = $uploadDir . $filename;
$tmpPath = $_FILES['image']['tmp_name'];

if (!move_uploaded_file($tmpPath, $uploadPath)) {
    throw new Exception("Failed to move uploaded file to uploads directory.");
}

// Encode for GPT API
$imageData = base64_encode(file_get_contents($uploadPath));
$imageMime = mime_content_type($uploadPath);
$imageUri = "data:$imageMime;base64,$imageData";

// Create /annotations folder
$annotatedDir = __DIR__ . '/annotations/';
if (!is_dir($annotatedDir)) {
    mkdir($annotatedDir, 0777, true);
}

// Prepare annotated image path and URL
$annotatedFilename = uniqid('chart_', true) . '.png';
$annotatedPath = $annotatedDir . $annotatedFilename;

copy($uploadPath, $annotatedDir . $annotatedFilename);
$annotatedUrl = 'https://app.tradetimescanner.com/annotations/' . $annotatedFilename;


// Include in response
$decoded['annotated_chart_url'] = $annotatedUrl;


    // Gather inputs
    $level     = $_POST['level'] ?? 'Beginner';
    $currency  = $_POST['pair'] ?? 'EUR/USD';
    $timeframe = $_POST['timeframe'] ?? '1H';

   // Load and personalize the prompt
$promptTemplate = <<<PROMPT
You are a professional AI trading assistant.

You will be given:
- A screenshot of a TradingView candlestick chart (image input)
- Metadata:
  - Experience Level: {experience_level}
  - Currency Pair or Stock Symbol: {currency_pair}
  - Timeframe: {timeframe}

Your job is to respond with one of the following two cases, depending on the image:

---

1. **If the image is NOT a valid TradingView candlestick chart**, or it's unclear, unreadable, or doesn’t match the expected timeframe or symbol, respond ONLY with this JSON:

{
  "experience_level": "{experience_level}",
  "currency_pair": "{currency_pair}",
  "timeframe": "{timeframe}",
  "is_chart_valid": false,
  "error": "Describe the specific reason (e.g., wrong stock shown, image cut off, not a chart, etc.)"
}

---

2. **If the image IS valid and contains a clearly visible candlestick chart**, analyze it Identify patterns (e.g., double bottom, head and shoulders), Recommend a trading strategy with ? 80% success probability and strategy must includ the full trade insight using this exact JSON format:
Do not include any markdown, headers, or natural text outside the JSON structure.
{
  "experience_level": "{experience_level}",
  "currency_pair": "{currency_pair}",
  "timeframe": "{timeframe}",
  "is_chart_valid": true,
  "trade_direction": "Buy or Sell",
  "entry_point": "e.g. 1.0845",
  "stop_loss": "e.g. 1.0800",
  "take_profit": "e.g. 1.0900",
  "identified_pattern": "e.g. Double Bottom, Head and Shoulders",
  "estimated_success_rate": "e.g. 82%",
  "summary_explanation": "A short human-like explanation of what was detected on the chart, e.g. key levels, indicators, or trends.",
  "visual_annotation_instructions": "Describe how to annotate the chart: mark trendlines, zones, candlestick patterns, etc.",
  "annotated_chart_url": ""
}


PROMPT;


    $filledPrompt = str_replace(
    ['{experience_level}', '{currency_pair}', '{timeframe}'],
    [$level, $currency, $timeframe],
    $promptTemplate
);

    // Call GPT-4o
$response = $client->chat()->create([
    'model' => 'gpt-4o',
    'messages' => [
        [
            'role' => 'system',
            'content' => $filledPrompt
        ],
        [
            'role' => 'user',
            'content' => [
                ['type' => 'image_url', 'image_url' => ['url' => $imageUri]]
            ]
        ],
    ],
    'max_tokens' => 350,
]);


    // Extract and decode GPT response 
    $raw = trim($response['choices'][0]['message']['content']);
    file_put_contents(__DIR__ . '/last-gpt-reply.txt', $raw); // for debugging

    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        throw new Exception("Invalid JSON from GPT");
    }

    // Annotated chart placeholder logic 
    $annotatedDir = __DIR__ . '/annotations/';
    if (!is_dir($annotatedDir)) {
        mkdir($annotatedDir, 0777, true);
    }

    $annotatedFilename = uniqid('chart_', true) . '.png';
    $annotatedPath = $annotatedDir . $annotatedFilename;

    // original chart as annotation
    move_uploaded_file($tmpPath, $annotatedPath);

    $decoded['annotated_chart_url'] = $decoded['is_chart_valid']
        ? "https://app.tradetimescanner.com/annotations/$annotatedFilename"
        : null;

    // Send JSON back to frontend
    echo json_encode($decoded);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Internal Server Error: " . $e->getMessage()
    ]);
}