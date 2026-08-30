<?php

namespace App\Support;

use Faker\Factory;
use Illuminate\Support\Str;

class ProfileNicknameGenerator
{
    private const MAX_NICKNAME_LENGTH = 20;

    private const RANDOM_SUFFIX_LENGTH = 4;

    public function generate(string $firstName, string $lastName): string
    {
        $maxBaseLength = self::MAX_NICKNAME_LENGTH - self::RANDOM_SUFFIX_LENGTH - 2;

        $normalized = Str::of($firstName.'_'.$lastName)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9_]+/', '_')
            ->trim('_')
            ->limit($maxBaseLength, '')
            ->toString();

        if ($normalized === '') {
            $normalized = Str::of(Factory::create('it_IT')->userName())
                ->ascii()
                ->lower()
                ->replaceMatches('/[^a-z0-9_]+/', '_')
                ->trim('_')
                ->limit($maxBaseLength, '')
                ->toString();
        }

        return '@'.($normalized !== '' ? $normalized : 'wayout').'_'.Str::lower(Str::random(self::RANDOM_SUFFIX_LENGTH));
    }
}
