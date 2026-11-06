<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'POST requests only.']); exit; }
$input=json_decode(file_get_contents('php://input'),true) ?: [];
$course=trim($input['course'] ?? ''); $interest=trim($input['interest'] ?? ''); $level=trim($input['level'] ?? 'Bachelor degree'); $type=trim($input['type'] ?? 'Any suitable type'); $context=trim($input['context'] ?? ''); $userIdea=trim($input['user_idea'] ?? '');
if($course===''){http_response_code(422);echo json_encode(['success'=>false,'message'=>'Please enter your course or discipline.']);exit;}
$configFile=dirname(__DIR__).'/includes/config.local.php';
$apiKey='';$model='gpt-4o-mini';$endpoint='https://api.openai.com/v1/chat/completions';
if(file_exists($configFile)){ $cfg=require $configFile; $apiKey=$cfg['ai_api_key']??''; $model=$cfg['ai_model']??$model; $endpoint=$cfg['ai_endpoint']??$endpoint; }
if($apiKey===''){
 $ideas=[
 ['title'=>($userIdea!=='' ? 'Refined concept: '.$userIdea : 'Digital Service Access and Management Platform'),'description'=>'A platform that improves how people discover, request, and manage an important service in the selected field.','approach'=>'Needs assessment, requirements analysis, prototype development, and usability evaluation.','tools'=>'Choose tools that fit your course, budget, and available skills.'],
 ['title'=>'Data-Driven Decision Support for '.$course,'description'=>'A study or solution exploring how reliable data can help stakeholders make better decisions in '.$context.' or a comparable setting.','approach'=>'Collect relevant data, analyze patterns, develop recommendations, and validate findings.','tools'=>'Spreadsheet or statistical tools; a database or dashboard only if appropriate.'],
 ['title'=>'Improving '.$interest.' Through an Innovative Local Solution','description'=>'An applied project addressing a practical challenge related to '.$interest.' in a realistic local context.','approach'=>'Literature review, stakeholder interviews or surveys, solution design, and evaluation.','tools'=>'Research instruments and suitable design, analysis, or development tools.']
 ];
 echo json_encode(['success'=>true,'demo'=>true,'ideas'=>$ideas]); exit;
}
$prompt="Generate exactly 5 feasible final-year project ideas. Student course: $course. Level: $level. Interest: $interest. Preferred type: $type. Context: $context. Student's own idea (if supplied): $userIdea. If an own idea is supplied, make the first result a carefully refined version of that idea, preserve the student's intention, clarify the problem and target users, and make it feasible; then provide four relevant alternative or improved directions. Return ONLY valid JSON with key ideas, an array of objects with title, description, approach, tools, problem, target_users, solution, and expected_impact. Make ideas specific, achievable, ethical, and appropriate for the student's discipline. Do not invent references.";
$payload=json_encode(['model'=>$model,'messages'=>[['role'=>'system','content'=>'You are an academic project ideation assistant. Suggest realistic topics and clearly distinguish ideas from verified research. Return valid JSON only.'],['role'=>'user','content'=>$prompt]],'temperature'=>0.7,'response_format'=>['type'=>'json_object']]);
$ch=curl_init($endpoint);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$apiKey],CURLOPT_POSTFIELDS=>$payload,CURLOPT_TIMEOUT=>60]);$raw=curl_exec($ch);$err=curl_error($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
if($raw===false||$status<200||$status>=300){http_response_code(502);echo json_encode(['success'=>false,'message'=>'AI service could not complete the request. Check the server AI configuration and try again.']);exit;}
$data=json_decode($raw,true);$content=$data['choices'][0]['message']['content']??'';$parsed=json_decode($content,true);$ideas=$parsed['ideas']??[];
if(!is_array($ideas)||count($ideas)===0){http_response_code(502);echo json_encode(['success'=>false,'message'=>'The AI response was not in the expected format. Please try again.']);exit;}
echo json_encode(['success'=>true,'demo'=>false,'ideas'=>$ideas],JSON_UNESCAPED_UNICODE);
