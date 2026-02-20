<?php

namespace Tests\Unit;

use App\Support\InputValidation;
use PHPUnit\Framework\TestCase;

class InputValidationTest extends TestCase
{
    public function test_safe_text_regex_allows_common_business_characters(): void
    {
        $value = "R&D - Core Banking / Channel (Phase 2): O'Hara @QA_1, ok!";

        $this->assertSame(1, preg_match(InputValidation::SAFE_TEXT_REGEX, $value));
    }

    public function test_safe_text_regex_rejects_html_and_script_like_input(): void
    {
        $value = '<script>alert("xss")</script>';

        $this->assertSame(0, preg_match(InputValidation::SAFE_TEXT_REGEX, $value));
    }

    public function test_normalize_grouped_integer_supports_dot_and_comma_grouping(): void
    {
        $this->assertSame('1000', InputValidation::normalizeGroupedInteger('1.000'));
        $this->assertSame('1000', InputValidation::normalizeGroupedInteger('1,000'));
        $this->assertSame('1000', InputValidation::normalizeGroupedInteger('1000'));
    }

    public function test_normalize_localized_decimal_supports_comma_and_dot(): void
    {
        $this->assertSame('10.5', InputValidation::normalizeLocalizedDecimal('10,5'));
        $this->assertSame('10.5', InputValidation::normalizeLocalizedDecimal('10.5'));
        $this->assertSame('10', InputValidation::normalizeLocalizedDecimal('10'));
    }
}
