<?php

namespace SelfProject\FilamentGeminiSearch\Models;

use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    protected $table = 'gemini_search_histories';

    protected $fillable = [
        'query',
        'result_text',
        'sources',
        'search_queries',
        'user_id',
    ];

    protected $casts = [
        'sources' => 'array',
        'search_queries' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
