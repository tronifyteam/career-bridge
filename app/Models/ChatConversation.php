<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $fillable = ['user_a_id', 'user_b_id', 'is_closed', 'closed_by', 'closed_at'];

    protected function casts(): array
    {
        return [
            'is_closed'  => 'boolean',
            'closed_at'  => 'datetime',
        ];
    }

    /**
     * Get or create a canonical conversation record for two users.
     * Always stores user_a_id as the smaller ID to avoid duplicates.
     */
    public static function forPair(int $userA, int $userB): self
    {
        [$a, $b] = $userA < $userB ? [$userA, $userB] : [$userB, $userA];
        return self::firstOrCreate(['user_a_id' => $a, 'user_b_id' => $b]);
    }

    /**
     * Find an existing conversation for a pair without creating one.
     */
    public static function findPair(int $userA, int $userB): ?self
    {
        [$a, $b] = $userA < $userB ? [$userA, $userB] : [$userB, $userA];
        return self::where('user_a_id', $a)->where('user_b_id', $b)->first();
    }
}
