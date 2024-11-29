<?php

namespace Database\Factories;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ListingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Listing::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->sentence(rand(5, 7));
        $datetime = $this->faker->dateTimeBetween('-1 month', 'now');

        $content = '';
        for($i=0; $i < 5; $i++) {
            $content .= '<p class="mb-4">' . $this->faker->sentences(rand(5, 10), true) . '</p>';
        }

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . rand(1111, 9999),
            'company' => $this->faker->company,
            'location' => $this->faker->country,
            'job_type' => $this->faker->randomElement(['Full-Time', 'Internship', 'Freelance', 'Part-Time']),
            'work_policy' => $this->faker->randomElement(['On-Site', 'Remote', 'Hybrid']),
            'salary_from' => $this->faker->numberBetween(2000000, 10000000), // Contoh gaji dari
            'salary_to' => $this->faker->numberBetween(10000000, 25000000), // Contoh gaji sampai
            'is_highlighted' => (rand(1, 9) > 7),
            'is_active' => true,
            'content' => $content,
            'apply_link' => $this->faker->url,
            'created_at' => $datetime,
            'updated_at' => $datetime
        ];
    }
}
