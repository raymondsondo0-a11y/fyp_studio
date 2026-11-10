<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'POST requests only.']); exit; }
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$source = trim((string)($input['source_text'] ?? ''));
$course = trim((string)($input['course'] ?? ''));
$topic = trim((string)($input['topic'] ?? ''));
$level = trim((string)($input['level'] ?? 'University'));
$angle = trim((string)($input['angle'] ?? 'Concept-first'));
$voice = trim((string)($input['voice'] ?? 'Clear and straightforward'));
$student_context = trim((string)($input['student_context'] ?? ''));
if (mb_strlen($source) < 250) { http_response_code(422); echo json_encode(['success'=>false,'message'=>'Please provide at least 250 characters of readable original notes.']); exit; }
if (mb_strlen($source) > 50000) { http_response_code(413); echo json_encode(['success'=>false,'message'=>'The extracted notes are too long. Please upload a shorter file or divide it into smaller parts (maximum 50,000 characters).']); exit; }
$configFile = dirname(__DIR__).'/includes/config.local.php';
$apiKey = ''; $model = 'gpt-4o-mini'; $endpoint = 'https://api.openai.com/v1/chat/completions';
if (file_exists($configFile)) { $cfg = require $configFile; $apiKey = $cfg['ai_api_key'] ?? ''; $model = $cfg['ai_model'] ?? $model; $endpoint = $cfg['ai_endpoint'] ?? $endpoint; }
if ($apiKey === '' || $apiKey === 'YOUR_AI_API_KEY') { http_response_code(503); echo json_encode(['success'=>false,'message'=>'The AI service is not configured on the Maestroy server yet. Add a valid key in includes/config.local.php.']); exit; }
$system = 'You are Maestroy Academic Content Notes Assistant. Transform supplied lecture material into accurate, student-friendly academic content notes. Preserve the source meaning and all examinable concepts. Do not invent facts, statistics, citations, definitions, or claims. If the source is unclear or incomplete, explicitly mark the gap instead of guessing. Write naturally and clearly, not in repetitive template language. This is a learning aid, not a detector-evasion service: never promise that work is undetectable or uniquely different from every other student.';
$user = "Create detailed, useful academic CONTENT NOTES based only on the original lecture material below.\nCourse/discipline: $course\nTopic: $topic\nAcademic level: $level\nPreferred organization: $angle\nWriting voice: $voice\nStudent's own learning context or focus: $student_context\n\nIndividualization: use the student's selected organization, voice, focus and course context to make this version meaningfully tailored. Vary the explanation path and examples only where the source supports them. Do not fabricate personal experiences or unsupported examples. Different students may still receive overlapping core facts because the source material is the same; preserve factual consistency.\n\nRequirements: create a clear title and brief introduction; organize content into logical numbered sections and sub-sections; explain key terms; turn short or fragmented bullets into complete explanations; include source-supported examples; add a concise key-points recap, glossary and revision questions. Keep all important content. Do not mention AI or the fact that the notes were transformed. Do not add a bibliography unless the source itself provides references.\n\nReturn ONLY valid JSON in this structure: { \"title\": string, \"introduction\": string, \"sections\": [{\"heading\": string, \"paragraphs\": [string], \"key_points\": [string]}], \"glossary\": [{\"term\": string, \"definition\": string}], \"revision_questions\": [string], \"conclusion\": string, \"source_gaps\": [string] }.\n\nORIGINAL LECTURE NOTES:\n<<<\n$source\n>>>";
$payload = json_encode(['model'=>$model,'messages'=>[['role'=>'system','content'=>$system],['role'=>'user','content'=>$user]],'temperature'=>0.78,'response_format'=>['type'=>'json_object']], JSON_UNESCAPED_UNICODE);
$ch = curl_init($endpoint);
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$apiKey],CURLOPT_POSTFIELDS=>$payload,CURLOPT_TIMEOUT=>120]);
$raw = curl_exec($ch); $status = curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
if ($raw === false || $status < 200 || $status >= 300) { http_response_code(502); echo json_encode(['success'=>false,'message'=>'The AI service could not process these notes. Please try again or check the server AI configuration.']); exit; }
$data = json_decode($raw,true); $content = $data['choices'][0]['message']['content'] ?? ''; $result = json_decode($content,true);
if (!is_array($result) || empty($result['sections']) || !is_array($result['sections'])) { http_response_code(502); echo json_encode(['success'=>false,'message'=>'The AI returned an incomplete result. Please try generating the notes again.']); exit; }
echo json_encode(['success'=>true,'notes'=>$result], JSON_UNESCAPED_UNICODE);
