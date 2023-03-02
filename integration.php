<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;


// NOTE: this presumes the server is running locally on port 8080
// also presumes the short descriptions on wikipedia won't be updated
final class Integration extends TestCase {
    public function test_basic_functionality(): void {
        // some special characters, but all taken directly from wikipedia URLs
        $titles = [
            'Yoshua_Bengio' => 'Canadian computer scientist',
            'Causa_limeña' => 'Appetizer in Peruvian cuisine',
            'Lady_Gaga' => 'American singer and actress (born 1986)',
            'Lupita_Nyong\'o' => 'Kenyan-Mexican actress',
            'Timothée_Chalamet' => 'American actor (born 1995)',
            'Becky_G' => 'American singer and actress (born 1997)',
        ];

        foreach ($titles as $title => $description) {
            $url = 'http://localhost:8080?title='.urlencode($title);
            $response = file_get_contents($url);
            $ret = json_decode($response, true);

            $this->assertTrue($ret['ok']);
            $this->assertSame($ret['short description'], $description);
        }
    }

    public function test_search(): void {
        // spaces and lowercase to trigger fallback search
        $titles = [
            'yoshua bengio' => 'Canadian computer scientist',
            'causa lima' => 'Appetizer in Peruvian cuisine',
            'api mountain' => 'Mountain in Darchula District, Nepal',
            'rosalía' => 'Spanish singer (born 1992)',
        ];

        foreach ($titles as $title => $description) {
            $url = 'http://localhost:8080?title='.urlencode($title);
            $response = file_get_contents($url);
            $ret = json_decode($response, true);

            $this->assertTrue($ret['ok']);
            $this->assertSame($ret['short description'], $description);
        }
    }

    public function test_no_page(): void {
        $titles = [
            'skdjalskjdl',
            'this page does not exist',
            'Steph_Toyofuku',
        ];

        foreach ($titles as $title) {
            $url = 'http://localhost:8080?title='.urlencode($title);
            $response = file_get_contents($url);
            $ret = json_decode($response, true);

            $this->assertFalse($ret['ok']);
            $this->assertSame($ret['error'], "unable to find article for title $title");
        }
    }

    public function test_no_description(): void {
        $titles = [
            'Rosalia',
            'Kehlani_discography',
            'JID_discography',
            'Jason_Stanton',
            'test'
        ];

        foreach ($titles as $title) {
            $url = 'http://localhost:8080?title='.urlencode($title);
            $response = file_get_contents($url);
            $ret = json_decode($response, true);

            $this->assertFalse($ret['ok']);
            $this->assertSame($ret['error'], "no short description provided for page $title");
        }
    }
}

?>
