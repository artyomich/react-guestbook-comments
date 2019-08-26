<?php
/* 
 * API for to store and list all comments
 * PHP and XML
 */
$_POST = isset($_POST['name']) ? : json_decode(file_get_contents("php://input"), true);
// Simple CORS
header("Access-Control-Allow-Origin: *");
// Set default timezone Moscow
date_default_timezone_set('Europe/Moscow');
/**************************************
 * XML load using SimpleXML           *
 **************************************/
$fileXML = 'comments.xml';
if (file_exists($fileXML)) {
    $xmlRoot = simplexml_load_file($fileXML);
} else {
    $err = "Can't load file " . $fileXML;
    jsonResponse(['error' => $err], 500);
    exit($err);
}
/**************************************
 * Handle Post comment                 *
 **************************************/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //Get posts attributes last_session_id, last_update_timestamp, last_id for update after manipulation
    foreach ($xmlRoot->children() as $posts) {
        foreach($posts->attributes() as $key => $value) {
            if ($key == 'last_session_id') {
                $lastSessionId = $value;
            } elseif ($key == 'last_update_timestamp') {
                $lastUpdateTimestamp = (int) $value;
            } elseif ($key == 'last_id') {
                $lastId = (int) $value;
            } else {
                $err = 'In XML attritute structure unresolved element with key: ' . $key . ', and value: ' . $value;
                jsonResponse(['error' => $err], 500);
                exit($err . now());
            }
        }
        break;
    }

    //All attributes are required
    if (!isset($lastId) && !isset($lastUpdateTimestamp) && !isset($lastSessionId)) {
        $err = "Can't find required attributes in XML: " . $fileXML;
        jsonResponse(['error' => $err], 500);
        exit($err);
    }
    $currentSessionId = @$_POST['name'];

    //10 sec limit between actions
    if (($lastSessionId == $currentSessionId) && (time() - $lastUpdateTimestamp < 10)) {
        //error 429 - Too Many Request
        $err = '10 sec limit between user\'s comments! ';
        jsonResponse(['error' => $err], 429);
        exit($err . now());
    }

    // Inputs
    $name = @$_POST['name'];
    $comment = @$_POST['message'];
    //$parentId = @$_POST['parent_id'];
    $parentId = 0;
    $time = time();

    // Validate the input
    if (strlen($name) < 3) {
        jsonResponse(['error' => 'Name is required!'], 422);
    }
    if (strlen($comment) < 5) {
        jsonResponse(['error' => 'Comment is required!'], 422);
    }
    $data = [
        'id' => $lastId + 1,
        'name' => $name,
        'message' => $comment,
        'parent_id' => $parentId
    ];
    saveComment($data + ['time' => $time, 'lastSessionId' => $currentSessionId], $xmlRoot, $fileXML);
    jsonResponse(transform($data + ['time' => time()]), 201);
}
/**************************************
 * Return list of Comments             *
 **************************************/
// Read xml and print the results:
foreach ($xmlRoot->children() as $posts) {
    foreach ($posts->children() as $post) {
        $comments[] = $post;
    }
}
//$comments = array_map('transform', $comments);

// Transform result
jsonResponse($comments);
/************************************** Helper functions *************************************/
/*
 * Save a comment in xml
 *
 * @param $data
 * @param $posts
 * @param $file
 * @return boolean
 */
function saveComment($data, $xmlRoot, $file)
{
    // Prepare data
    foreach ($xmlRoot->children() as $posts) {
        $posts->attributes()->last_id = strip_tags($data['id']);
        $posts->attributes()->last_session_id = strip_tags($data['lastSessionId']);
        $posts->attributes()->last_update_timestamp = (int)$data['time'];

        $post = $posts->addChild('post');
        $post->addChild('id', strip_tags($data['id']));
        $post->addChild('name', strip_tags($data['name']));
        $post->addChild('message', strip_tags($data['message']));
        $post->addChild('parent_id', strip_tags($data['parent_id']));
        $post->addChild('time', strip_tags($data['time']));
    }
    // Bind and execute
   return $xmlRoot->asXML($file);
}

/*
 * Update a comment in xml
 *
 * @param $data
 * @param $posts
 * @param $file
 * @return boolean
 */
function updateComment($data, $xmlRoot, $file)
{
    // Prepare data
    foreach ($xmlRoot->children() as $posts) {
        $posts->attributes()->last_id = strip_tags($data['id']);
        $posts->attributes()->last_session_id = strip_tags($data['lastSessionId']);
        $posts->attributes()->last_update_timestamp = (int)$data['time'];

        $post = $posts->addChild('post');
        $post->addChild('id', strip_tags($data['id']));
        $post->addChild('name', strip_tags($data['name']));
        $post->addChild('message', strip_tags($data['message']));
        $post->addChild('parent_id', strip_tags($data['parent_id']));
        $post->addChild('time', strip_tags($data['time']));
    }
    // Bind and execute
    return $xmlRoot->asXML($file);
}

/**
 * Die a valid json response
 *
 * @param $data
 * @param int $status_code
 */
function jsonResponse($data, $status_code = 200)
{
    http_response_code($status_code);
    header('Content-Type: application/json');
    global $xmlRoot;
    $xmlRoot = null;
    die(json_encode($data));
}
/*
 * Transform record from db
 *
 * @param $comm
 * @return array
 */
function transform($comm)
{
    return [
        'id' => (int)$comm['id'],
        'parent_id' => (int)$comm['parent_id'],
        'name' => $comm['name'],
        'message' => $comm['message'],
        'time' => time_elapsed_string((int)$comm['time']),
    ];
}
function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime('@' . $datetime);
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
