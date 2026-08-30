<?php

namespace Tests\Unit;

use App\Support\ProfileNicknameGenerator;
use PHPUnit\Framework\TestCase;

class ProfileNicknameGeneratorTest extends TestCase
{
    public function test_it_generates_a_nickname_close_to_the_person_name(): void
    {
        $nickname = (new ProfileNicknameGenerator)->generate('Matteo', 'Sportelli');

        $this->assertMatchesRegularExpression('/^@matteo_sportel_[a-z0-9]{4}$/', $nickname);
        $this->assertLessThanOrEqual(20, strlen($nickname));
    }

    public function test_it_normalizes_accents_and_punctuation(): void
    {
        $nickname = (new ProfileNicknameGenerator)->generate('Mattéo', "D'Amico");

        $this->assertMatchesRegularExpression('/^@matteo_d_amico_[a-z0-9]{4}$/', $nickname);
        $this->assertLessThanOrEqual(20, strlen($nickname));
    }

    public function test_it_respects_the_backend_limit_for_long_names(): void
    {
        $nickname = (new ProfileNicknameGenerator)->generate(
            'Massimiliano Alessandro',
            'De Santis Monteverde',
        );

        $this->assertMatchesRegularExpression('/^@[a-z0-9_]+_[a-z0-9]{4}$/', $nickname);
        $this->assertSame(20, strlen($nickname));
    }
}
