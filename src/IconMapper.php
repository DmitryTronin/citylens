<?php
declare(strict_types=1);
namespace CityLens;
final class IconMapper
{
    public static function forWeatherId(int $id): string
    {
        return match (true) {
            $id >= 200 && $id < 300 => 'THUNDER', $id >= 300 && $id < 400 => 'SLEET',
            $id >= 500 && $id < 600 => $id === 511 ? 'SNOW' : 'RAIN',
            $id >= 600 && $id < 700 => in_array($id, [611, 612, 613], true) ? 'SLEET' : 'SNOW',
            $id >= 700 && $id < 800 => $id === 781 ? 'WIND' : 'FOG', $id === 800 => 'CLEAR_DAY',
            $id === 801 || $id === 802 => 'PARTLY_CLOUDY_DAY', $id === 803 || $id === 804 => 'CLOUDY', default => 'PARTLY_CLOUDY_DAY'
        };
    }
}
