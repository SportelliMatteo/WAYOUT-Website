<?php

namespace Tests\Unit;

use App\Support\ProfileNicknameGenerator;
use PHPUnit\Framework\TestCase;

class ProfileNicknameGeneratorTest extends TestCase
{
    public function test_it_generates_a_nickname_close_to_the_person_name(): void
    {
        $nickname = (new ProfileNicknameGenerator)->generate('Matteo', 'Sportelli');

        $this->assertMatchesRegularExpression('/^@matteo_sportelli_[a-z0-9]{4}$/', $nickname);
        $this->assertLessThanOrEqual(29, strlen($nickname));
    }

    public function test_it_normalizes_accents_and_punctuation(): void
    {
        $nickname = (new ProfileNicknameGenerator)->generate('Mattéo', "D'Amico");

        $this->assertMatchesRegularExpression('/^@matteo_d_amico_[a-z0-9]{4}$/', $nickname);
    }
}
