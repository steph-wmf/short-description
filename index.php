<?php declare(strict_types=1);

// here is the endpoint for handling requests, while the majority of the logic
// lives in api.php

header('Content-type: application/json');

include_once('api.php');


// helper function for returning errors - corresponding respond_ok function
// does not exist since we're only responding ok from one location
function respond_error(string $error): void {
    echo json_encode(['ok' => false, 'error' => $error]);

    exit;
}

// get the title from the request
$title = Api::get_title();

// attempt to get article contents using the provided title
$url = Api::build_url($title);
$content = Api::query_wikipedia($url);

if (!$content) {
    // oops no content - check to see if we can find the article in search
    $search_url = Api::build_search_url($title);
    $search_title = Api::fallback_search($search_url);

    if (!$search_title) {
        // no luck, time to give up
        respond_error("unable to find article for title $title");
    }

    // try again with the updated title
    $content = Api::query_wikipedia(Api::build_url($search_title));

    if (!$content) {
        // not sure when/if this would ever happen but better safe than sorry
        respond_error("no contents for article $search_title");
    }
}

// try to find the short description
$short_description = Api::extract_short_description($content);

if (!$short_description) {
    respond_error("no short description provided for page $title");
}

// return to the user
echo json_encode(['ok' => true, 'short description' => $short_description]);

?>
