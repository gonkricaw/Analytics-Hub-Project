<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => fake()->unique()->words(2, true),
            'type' => 'list_menu',
            'icon' => 'fas fa-link',
            'route_or_url' => fake()->unique()->url(),
            'content_id' => null,
            'order' => fake()->unique()->numberBetween(1, 10000),
            'role_permissions_required' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the menu is a parent menu.
     */
    public function parent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
            'type' => 'list_menu',
            'route_or_url' => null,
        ]);
    }

    /**
     * Indicate that the menu is a child menu.
     */
    public function child(int $parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Indicate that the menu is linked to content.
     */
    public function withContent(int $contentId): static
    {
        return $this->state(fn (array $attributes) => [
            'content_id' => $contentId,
            'type' => 'content_menu',
            'route_or_url' => null,
        ]);
    }

    /**
     * Indicate that the menu is a list menu.
     */
    public function listMenu(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'list_menu',
            'content_id' => null,
        ]);
    }

    /**
     * Indicate that the menu is a content menu.
     */
    public function contentMenu(int $contentId): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'content_menu',
            'content_id' => $contentId,
            'route_or_url' => null,
        ]);
    }
}
