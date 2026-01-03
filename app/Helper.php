<?php

namespace App;

use App\Enums\CollectionType;
use App\Enums\FieldType;
use App\Models\Collection;
use App\Models\CollectionField;
use App\Models\Project;
use App\Models\User;
use DB;
use Illuminate\Support\Str;

class Helper
{
    /**
     * Create first project, and a super user account
     * @param string $superuserEmail
     * @param string $superuserPassword
     * @return User
     */
    public static function initProject(string $superuserEmail = 'admin@larabase.com', string $superuserPassword = 'password'): User
    {
        DB::beginTransaction();

        $project = Project::create([
            'name' => 'Acme'
        ]);

        $userCollection = Collection::create([
            'name' => 'users',
            'project_id' => $project->id,
            'type' => CollectionType::Auth,
        ]);

        $collectionFields = CollectionField::createAuthFrom([
            [
                'name' => 'name',
                'type' => FieldType::Text,
                'unique' => false,
                'required' => true,
            ],
            [
                'name' => 'avatar',
                'type' => FieldType::File,
                'unique' => false,
                'required' => false,
            ],
        ]);

        foreach ($collectionFields as $f) {
            $userCollection->fields()->create($f);
        }

        $user = User::create([
            'name' => 'superuser_' . Str::random(8),
            'email' => $superuserEmail,
            'password' => $superuserPassword,
        ]);

        DB::commit();

        return $user;
    }
}
