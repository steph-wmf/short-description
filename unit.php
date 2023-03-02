<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

include_once('api.php');


final class Unit extends TestCase {
    public function test_get_title(): void {
        $_GET['title'] = 'test';

        $title = Api::get_title();

        $this->assertSame($title, 'test');
    }

    public function test_get_title_none(): void {
        unset($_GET);

        $title = Api::get_title();

        $this->assertNull($title);
    }

    public function test_build_url(): void {
        $url = Api::build_url('test');

        $this->assertSame($url, 'https://en.wikipedia.org/w/api.php?action=query&format=json&formatversion=2&prop=revisions&rvlimit=1&rvprop=content&titles=test');
    }

    public function test_build_url_special_chars(): void {
        $url = Api::build_url('test with spaces');

        $this->assertSame($url, 'https://en.wikipedia.org/w/api.php?action=query&format=json&formatversion=2&prop=revisions&rvlimit=1&rvprop=content&titles=test+with+spaces');
    }

    public function test_query_wikipedia(): void {
        $content = Api::query_wikipedia('test/test_response.json');

        $this->assertSame($content, 'test content');
    }

    public function test_query_wikipedia_no_content(): void {
        $test_files = [
            'test/test_response_no_content.json',
            'test/test_response_no_revisions.json',
            'test/test_response_no_pages.json',
            'test/test_response_no_query.json',
        ];

        foreach ($test_files as $test_file) {
            $content = Api::query_wikipedia($test_file);

            $this->assertNull($content);
        }
    }

    public function test_build_search_url(): void {
        $url = Api::build_search_url('test');

        $this->assertSame($url, 'https://en.wikipedia.org/w/api.php?action=opensearch&search=test');
    }

    public function test_build_search_url_special_chars(): void {
        $url = Api::build_search_url('test with spaces');

        $this->assertSame($url, 'https://en.wikipedia.org/w/api.php?action=opensearch&search=test+with+spaces');
    }

    public function test_fallback_search(): void {
        $title = Api::fallback_search('test/test_search_response.json');

        $this->assertSame($title, 'Yoshua_Bengio');
    }

    public function test_fallback_search_special_chars(): void {
        $title = Api::fallback_search('test/test_search_response_special_chars.json');

        $this->assertSame($title, 'Causa_limeña');
    }

    public function test_fallback_search_no_results(): void {
        $title = Api::fallback_search('test/test_search_response_no_results.json');

        $this->assertNull($title);
    }

    public function test_fallback_search_prefix(): void {
        $title = Api::fallback_search('test/test_search_response_prefix.json');

        $this->assertNull($title);
    }

    public function test_extract_short_description(): void {
        $description = Api::extract_short_description('{{short description|test description}}');

        $this->assertSame($description, 'test description');
    }

    public function test_extract_short_description_padded(): void {
        $description = Api::extract_short_description('prefix {{short description|test description}} suffix');

        $this->assertSame($description, 'test description');
    }

    public function test_extract_short_description_caps(): void {
        $description = Api::extract_short_description('{{Short Description|test description}}');

        $this->assertSame($description, 'test description');
    }

    public function test_extract_short_description_none(): void {
        $contents = [
            '{{short description|None}}',
            '{{short description|none}}',
            'this one has no short description',
        ];

        foreach ($contents as $content) {
            $description = Api::extract_short_description($content);

            $this->assertNull($description);
        }
    }
}

?>
