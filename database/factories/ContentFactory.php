<?php

namespace Database\Factories;

use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Content>
 */
class ContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Content::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $slug = Str::slug($title) . '-' . fake()->unique()->randomNumber(5);
        
        return [
            'title' => $title,
            'slug' => $slug,
            'type' => 'custom',
            'custom_content' => fake()->paragraphs(3, true),
            'embed_url_original' => null,
            'embed_url_uuid' => null,
            'created_by_user_id' => 1,
            'updated_by_user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the content uses custom content.
     */
    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'custom',
            'embed_url_original' => null,
            'embed_url_uuid' => null,
        ]);
    }

    /**
     * Indicate that the content uses an embed URL.
     */
    public function embedUrl(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'embed_url',
            'custom_content' => null,
            'embed_url_original' => fake()->url(),
            'embed_url_uuid' => fake()->uuid(),
        ]);
    }
}
