<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class WeatherTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Provides simulated weather information for a given location. This is a demo tool that returns mock weather data.';

    /**
     * Handle the tool request.
     *
     * @return array<int, \Laravel\Mcp\Response>
     */
    public function handle(Request $request): array
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100',
            'units' => 'in:celsius,fahrenheit',
        ], [
            'location.required' => 'You must specify a location to get weather for. For example, "New York City" or "Tokyo".',
            'location.max' => 'Location name cannot be longer than 100 characters.',
            'units.in' => 'You must specify either "celsius" or "fahrenheit" for the units.',
        ]);

        $location = $validated['location'];
        $units = $validated['units'] ?? 'celsius';
        
        // Simulate weather data (in a real application, you'd call a weather API)
        $weatherData = $this->generateMockWeather($location, $units);

        return [
            Response::text("🌤️ Weather for {$location}:"),
            Response::text("Temperature: {$weatherData['temperature']}° {$weatherData['units']}"),
            Response::text("Condition: {$weatherData['condition']}"),
            Response::text("Humidity: {$weatherData['humidity']}%"),
            Response::text("Wind Speed: {$weatherData['wind_speed']} {$weatherData['wind_units']}"),
        ];
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'location' => $schema->string()
                ->description('The location to get the weather for.')
                ->required(),

            'units' => $schema->string()
                ->enum(['celsius', 'fahrenheit'])
                ->description('The temperature units to use.')
                ->default('celsius'),
        ];
    }

    /**
     * Generate mock weather data for demonstration.
     */
    private function generateMockWeather(string $location, string $units): array
    {
        $conditions = ['Sunny', 'Partly Cloudy', 'Cloudy', 'Light Rain', 'Overcast'];
        $condition = $conditions[array_rand($conditions)];
        
        // Generate temperature based on location hash for consistency
        $baseTemp = abs(crc32($location)) % 30 + 10; // Temperature between 10-40°C
        
        if ($units === 'fahrenheit') {
            $temperature = round($baseTemp * 9/5 + 32);
            $tempUnits = 'F';
        } else {
            $temperature = $baseTemp;
            $tempUnits = 'C';
        }

        return [
            'temperature' => $temperature,
            'units' => $tempUnits,
            'condition' => $condition,
            'humidity' => rand(30, 80),
            'wind_speed' => rand(5, 25),
            'wind_units' => 'km/h',
        ];
    }
}
