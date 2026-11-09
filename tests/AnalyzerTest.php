<?php
namespace DafapSEO\Tests;

use PHPUnit\Framework\TestCase;
use DafapSEO\SEO\Analyzer;

class AnalyzerTest extends TestCase {
    public function testComputeScoreReturnsInt() {
        $score = Analyzer::compute_score(123); // ID fictif
        $this->assertIsInt($score);
    }
}
