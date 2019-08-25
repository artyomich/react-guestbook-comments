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
    $posts = simplexml_load_file($fileXML);
} else {
    $err = "Can't load file " . $fileXML;
    json_response(['error' => $err], 500);
    exit($err);
}

/**************************************
 * Handle Post comment                 *
 **************************************/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //10 sec limit between actions
    $lastSessionId = (string)$posts['last_session_id'];
    $lastUpdateTimestamp = (int)$posts['last_update_timestamp'];

/*    if (($lastSessionId == @$_POST['name']) && (now() - $lastUpdateTimestamp < 10)) {
        $err = '10 sec limit between user\'s comments! ';
        json_response(['error' => $err], 422);
        exit($err . now());
    }*/

	// Inputs
	$name = @$_POST['name'];
	$comment = @$_POST['message'];
    //$parentId = @$_POST['parent_id'];
    $parentId = 0;

	// Validate the input
	if (strlen($name) < 3) {
		json_response(['error' => 'Name is required!'], 422);
	}

	if (strlen($comment) < 5) {
		json_response(['error' => 'Comment is required!'], 422);
	}

	$data = [
	    'id' => (int)$posts['last_id'] + 1,
        'name' => $name,
        'message' => $comment,
        'parent_id' => $parentId
    ];

	save_comment($data + ['time' => time(), 'lastSessionId' => $lastSessionId], $posts, $fileXML);

	json_response(transform($data + ['time' => time()]), 201);
}

/**************************************
 * Return list of Comments             *
 **************************************/

// Read xml and print the results:
foreach ($posts->children() as $post) {
    $comments[] = time_elapsed_string((int)$post->time);
}

$comments = array_map('transform', $comments);

// Transform result
json_response($comments);


/************************************** Helper functions *************************************/

/*
 * Save a comment in xml
 * 
 * @param $data
 * @param $posts
 * @param $file
 * @return boolean
 */
function save_comment($data, $posts, $file)
{
	// Prepare statement
    $options = $posts->addChild('options');
    $options->addAttribute('last_id', strip_tags($data['id']));
    $options->addAttribute('last_session_id', strip_tags($data['lastSessionId']));
    $options->addAttribute('last_update_timestamp', strip_tags(strip_tags($data['time'])));

    $post = $posts->addChild('post');
    $post->addChild('id', strip_tags($data['id']));
    $post->addChild('name', strip_tags($data['name']));
    $post->addChild('message', strip_tags($data['message']));
    $post->addChild('parent_id', strip_tags($data['parent_id']));
    $post->addChild('time', strip_tags($data['time']));

    // Bind and execute
	return $posts->asXML($file);
}

/**
 * Die a valid json response
 * 
 * @param $data
 * @param int $status_code
 */
function json_response($data, $status_code = 200)
{
	http_response_code($status_code);
	header('Content-Type: application/json');
	global $posts;
    $posts = null;
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