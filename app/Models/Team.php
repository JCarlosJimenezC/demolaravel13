<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    public function add($users)
    {
        $this->guardAgainstTooManyMembers($users);

        if ($users instanceof User) {
            return $this->users()->save($users);
        }

        $this->users()->saveMany($users);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    protected function guardAgainstTooManyMembers($users)
    {
        $newCount = $users instanceof User ? 1 : $users->count();

        if ($this->users()->count() + $newCount > $this->size) {
            throw new Exception();
        }
    }
}
