<?php

namespace Kho8k\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kho8k\Core\Models\Actor;
use Illuminate\Support\Str;

class ActorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Actor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $name = $this->faker->name,
            'slug' => Str::slug($name),
        ];
    }
}
