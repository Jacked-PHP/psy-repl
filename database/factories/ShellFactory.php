<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shell>
 */
class ShellFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->title,
            'php_binary' => '/usr/bin/php',
            'path' => $this->faker->filePath(),
            'code' => '<?php echo "Hello, World!";',
            'output' => '"Hello, World!"',
        ];
    }
}
