<?php

namespace App\Support;

use Faker\Factory;
use Illuminate\Support\Str;

class ProfileNicknameGenerator
{
    public function generate(): string
    {
        $username = Factory::create('it_IT')->userName();
        $normalized = Str::of($username)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9_]+/', '_')
            ->trim('_')
            ->limit(22, '')
            ->toString();

        return ($normalized !== '' ? $normalized : 'wayout').'_'.Str::lower(Str::random(6));
    }
}
