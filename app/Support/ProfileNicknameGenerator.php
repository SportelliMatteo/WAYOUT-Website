<?php

namespace App\Support;

use Faker\Factory;
use Illuminate\Support\Str;

class ProfileNicknameGenerator
{
    public function generate(string $firstName, string $lastName): string
    {
        $normalized = Str::of($firstName.'_'.$lastName)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9_]+/', '_')
            ->trim('_')
            ->limit(22, '')
            ->toString();

        if ($normalized === '') {
            $normalized = Str::of(Factory::create('it_IT')->userName())
                ->ascii()
                ->lower()
                ->replaceMatches('/[^a-z0-9_]+/', '_')
                ->trim('_')
                ->limit(22, '')
                ->toString();
        }

        return '@'.($normalized !== '' ? $normalized : 'wayout').'_'.Str::lower(Str::random(4));
    }
}
