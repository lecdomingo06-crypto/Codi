<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function videoEmbedUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        $host = parse_url($this->video_url, PHP_URL_HOST);
        $path = trim((string) parse_url($this->video_url, PHP_URL_PATH), '/');

        if ($host && str_contains($host, 'youtu.be')) {
            return "https://www.youtube.com/embed/{$path}";
        }

        if ($host && str_contains($host, 'youtube.com')) {
            parse_str((string) parse_url($this->video_url, PHP_URL_QUERY), $query);

            if (! empty($query['v'])) {
                return 'https://www.youtube.com/embed/'.$query['v'];
            }

            if (str_starts_with($path, 'embed/')) {
                return $this->video_url;
            }
        }

        return $this->video_url;
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function versions()
    {
        return $this->hasMany(LessonVersion::class);
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }
}
