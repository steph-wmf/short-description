<?php declare(strict_types=1);


// The API class - this is where all the methods used in index.php are defined
abstract class Api {
    // looks for a title provided in the request - if one is not provided,
    // returns null
    public static function get_title(): ?string {
        if (isset($_GET) && isset($_GET['title'])) {
            return $_GET['title'];
        }

        return null;
    }

    // build the query url to look up the last revision given a title
    public static function build_url(string $title): string {
        $query_params = [
            'action' => 'query',
            'format' => 'json',
            'formatversion' => 2,
            'prop' => 'revisions',
            'rvlimit' => 1,
            'rvprop' => 'content',
        ];

        // supply the title
        $query_params['titles'] = $title;

        // build the query string
        $query_string = http_build_query($query_params, '', '&');

        // then append it to the endpoint
        return "https://en.wikipedia.org/w/api.php?$query_string";
    }

    // query wikipedia by title, and return the contents of the article
    // returns null if the article or contents are missing
    public static function query_wikipedia(string $url): ?string {
        // we use file_get_contents over curl for compatibility and testing
        $response = file_get_contents($url);

        // decode json to array
        $json = json_decode($response, true);

        // then extract the content - first make sure we got results
        if (isset($json['query']) && isset($json['query']['pages'])) {
            $pages = $json['query']['pages'];

            // make sure the page exists and has revisions
            if (isset($pages[0]) && isset($pages[0]['revisions'])) {
                $revisions = $pages[0]['revisions'];

                // make sure the latest revision exists and has content
                if (isset($revisions[0]) && isset($revisions[0]['content'])) {
                    // finally, return the content
                    return $revisions[0]['content'];
                }
            }
        }

        // fail if content is missing or not where we expect it to be
        return null;
    }

    // build the search url for our fallback search
    public static function build_search_url(string $title): string {
        $base = 'https://en.wikipedia.org/w/api.php?action=opensearch&search=';

        // url encode title in case it has spaces
        return $base.urlencode($title);
    }

    // sometimes the title provided isn't an exact match and the api doesn't
    // seem to like that - perform a search to try and find a corresponding
    // article, and return the updated title, if any results
    public static function fallback_search(string $url): ?string {
        $response = file_get_contents($url);

        // decode json to array
        $json = json_decode($response, true);

        // if there are no results, give up
        if (!isset($json[3]) || !isset($json[3][0])) {
            return null;
        }

        // otherwise, take the first result and extract its title
        $result = $json[3][0];

        // if the result follows the standard format, get the title from the URL
        // urls are encoded in the response but we re-encode them in the query
        // function so decode here for Very Special Cases
        if (substr($result, 0, 30) === 'https://en.wikipedia.org/wiki/') {
            return urldecode(substr($result, 30));
        }

        // if not, we don't know what to do with it so return null
        return null;
    }

    // given a page's content, search for the short description and return it
    // returns null if no short description is found
    public static function extract_short_description(string $content): ?string {
        // short description does not appear to be case sensitive, so use the i
        // flag in our regex
        if (!preg_match('/{{short description\|(.+)}}/i', $content, $matches)) {
            // return null if no short description can be found
            return null;
        }

        $description = $matches[1] ?? null;

        // it appears sometimes short description is explicitly set to none
        // assume the intention here is to return null
        if ($description === 'None' || $description === 'none') {
            return null;
        }

        return $description;
    }
}

?>
