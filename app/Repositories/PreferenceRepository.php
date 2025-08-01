<?php

namespace App\Repositories;

class PreferenceRepository
{
    public function getPreferences($user)
    {
        return $user->preference;
    }
}
