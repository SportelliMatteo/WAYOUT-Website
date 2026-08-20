<?php

namespace Tests\Unit;

use App\Support\ProfileNicknameGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProfileNicknameGeneratorTest extends TestCase
{
    #[Test]
    public function it_generates_a_nickname_prefixed_with_an_at_sign(): void
    {
        $nickname = (new ProfileNicknameGenerator)->generate();

        $this->assertStringStartsWith('@', $nickname);
        $this->assertMatchesRegularExpression('/^@[a-z0-9_]+$/', $nickname);
        $this->assertLessThanOrEqual(30, strlen($nickname));
    }
}
