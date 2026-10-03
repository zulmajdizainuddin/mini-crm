<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();
        $domain = Str::slug(Str::before($name, ' ')).'.'.fake()->randomElement(['com', 'com.my', 'my', 'io']);

        return [
            'name' => $name,
            'email' => 'hello@'.$domain,
            'website' => 'https://www.'.$domain,
            'logo' => null,
        ];
    }

    /**
     * Generate a simple 200×200 PNG monogram logo on the public disk (requires GD).
     */
    public function withLogo(): static
    {
        return $this->afterMaking(function (Company $company): void {
            if (! function_exists('imagecreatetruecolor')) {
                return;
            }

            $company->logo = $this->generateLogo($company->name);
        });
    }

    private function generateLogo(string $name): string
    {
        $palette = [[37, 99, 235], [5, 150, 105], [217, 119, 6], [219, 39, 119], [124, 58, 237], [8, 145, 178], [220, 38, 38]];
        [$r, $g, $b] = $palette[abs(crc32($name)) % count($palette)];

        // Draw the initials on a tiny canvas, then scale it up for a chunky monogram.
        $small = imagecreatetruecolor(20, 20);
        $background = imagecolorallocate($small, $r, $g, $b);
        $foreground = imagecolorallocate($small, 255, 255, 255);

        if ($background === false || $foreground === false) {
            throw new RuntimeException('Could not allocate logo colours.');
        }

        imagefill($small, 0, 0, $background);
        $initials = Str::upper(collect(explode(' ', preg_replace('/[^A-Za-z ]/', '', $name)))
            ->filter()->take(2)->map(fn (string $w) => $w[0])->implode(''));
        $x = (int) ((20 - imagefontwidth(3) * strlen($initials)) / 2);
        imagestring($small, 3, $x, 3, $initials, $foreground);

        $logo = imagecreatetruecolor(200, 200);
        imagecopyresized($logo, $small, 0, 0, 0, 0, 200, 200, 20, 20);

        ob_start();
        imagepng($logo);
        $png = (string) ob_get_clean();

        $path = 'logos/'.Str::uuid().'.png';
        Storage::disk('public')->put($path, $png);

        return $path;
    }
}
