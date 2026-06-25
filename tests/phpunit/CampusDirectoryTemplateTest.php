<?php
use PHPUnit\Framework\TestCase;

class CampusDirectoryTemplateTest extends TestCase {
    public function test_template_escapes_malicious_input() {
        $items = [
            'informationToDisplay' => [[ 'cn' => 'Name', 'mail' => 'Campus Email' ]],
            'nodeContent' => [ 'linkOutToCampusDirectory' => false ],
            'dirLayout' => 'list',
            'linkToProfile' => false,
            'items' => [
                [
                    'uid' => ['baduid'],
                    'cn' => ['<script>alert(1)</script>'],
                    // mail is expected as an array with a 'count' and numeric indexes in templates
                    'mail' => [
                        'count' => 1,
                        0 => 'bad@example.com',
                    ],
                    // website field used with a 'count' in templates
                    'ucscpersonpubwebsite' => [
                        'count' => 1,
                        0 => 'http://example.com label',
                    ],
                    'jpegphoto' => [],
                ]
            ]
        ];

        // Render template and capture output
        ob_start();
        include __DIR__ . '/../../templates/CampusDirectoryTemplate.php';
        $output = ob_get_clean();

        // Ensure raw script tags are not present and that they are escaped
        $this->assertStringNotContainsString('<script>', $output, 'Raw <script> should not appear in output');
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $output, 'Injected script should be escaped');
    }
}
