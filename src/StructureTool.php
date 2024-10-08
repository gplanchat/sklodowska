<?php

declare(strict_types=1);

/*
 * This file is part of the Modelflow AI package.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

class StructureTool
{
    public function getStepInput(string $code): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'location' => ['type' => 'string'],
                'timestamp' => ['type' => 'string', 'format' => 'timestamp'],
                'weather' => ['type' => 'string', 'enum' => ['rain', 'snow', 'sunset', 'sunrise', 'sunny'], 'description' => 'The current weather'],
                'timeOfDay' => ['type' => 'string', 'enum' => ['sunset', 'sunrise', 'day', 'night'], 'description' => 'Time of the day'],
                'temperature' => ['type' => 'number', 'description' => 'Temperature in Fahrenheit'],
            ]
        ];
    }

    public function getStepOutput(string $code): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'city' => ['type' => 'string'],
                'date' => ['type' => 'string', 'format' => 'date-time'],
                'clouds' => ['type' => 'boolean'],
                'rain' => ['type' => 'boolean'],
                'snow' => ['type' => 'boolean'],
                'sunset' => ['type' => 'string'],
                'temp' => ['type' => 'number', 'description' => 'Temperature in Celsius'],
            ]
        ];
    }
}
