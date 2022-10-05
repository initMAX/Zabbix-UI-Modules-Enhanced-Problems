<?php
/*
** initMAX
** Copyright (C) 2021-2022 initMAX s.r.o.
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 3 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

namespace Modules\EnhancedProblems\Helpers\Functions;

use Exception;

class CColor
{
    private $_hex;
    private $_hsl;
    private $_rgb;

    public const DEFAULT_ADJUST = 10;

    public function __construct(string $hex)
    {
        $color = self::sanitizeHex($hex);
        $this->_hex = $color;
        $this->_hsl = self::hexToHsl($color);
        $this->_rgb = self::hexToRgb($color);
    }

    public static function hexToHsl(string $color): array
    {
        // Sanity check
        $color = self::sanitizeHex($color);

        // Convert HEX to DEC
        $R = hexdec($color[0] . $color[1]);
        $G = hexdec($color[2] . $color[3]);
        $B = hexdec($color[4] . $color[5]);

        $HSL = array();

        $var_R = ($R / 255);
        $var_G = ($G / 255);
        $var_B = ($B / 255);

        $var_Min = min($var_R, $var_G, $var_B);
        $var_Max = max($var_R, $var_G, $var_B);
        $del_Max = $var_Max - $var_Min;

        $L = ($var_Max + $var_Min) / 2;

        if ($del_Max == 0) {
            $H = 0;
            $S = 0;
        } else {
            if ($L < 0.5) {
                $S = $del_Max / ($var_Max + $var_Min);
            } else {
                $S = $del_Max / (2 - $var_Max - $var_Min);
            }

            $del_R = ((($var_Max - $var_R) / 6) + ($del_Max / 2)) / $del_Max;
            $del_G = ((($var_Max - $var_G) / 6) + ($del_Max / 2)) / $del_Max;
            $del_B = ((($var_Max - $var_B) / 6) + ($del_Max / 2)) / $del_Max;

            if ($var_R == $var_Max) {
                $H = $del_B - $del_G;
            } elseif ($var_G == $var_Max) {
                $H = (1 / 3) + $del_R - $del_B;
            } elseif ($var_B == $var_Max) {
                $H = (2 / 3) + $del_G - $del_R;
            }

            if ($H < 0) {
                $H++;
            }
            if ($H > 1) {
                $H--;
            }
        }

        $HSL['H'] = ($H * 360);
        $HSL['S'] = $S;
        $HSL['L'] = $L;

        return $HSL;
    }

    public static function hslToHex(array $hsl = array()): string
    {
        // Make sure it's HSL
        if (empty($hsl) || !isset($hsl["H"], $hsl["S"], $hsl["L"])) {
            throw new Exception("Param was not an HSL array");
        }

        list($H, $S, $L) = array($hsl['H'] / 360, $hsl['S'], $hsl['L']);

        if ($S == 0) {
            $r = $L * 255;
            $g = $L * 255;
            $b = $L * 255;
        } else {
            if ($L < 0.5) {
                $var_2 = $L * (1 + $S);
            } else {
                $var_2 = ($L + $S) - ($S * $L);
            }

            $var_1 = 2 * $L - $var_2;

            $r = 255 * self::hueToRgb($var_1, $var_2, $H + (1 / 3));
            $g = 255 * self::hueToRgb($var_1, $var_2, $H);
            $b = 255 * self::hueToRgb($var_1, $var_2, $H - (1 / 3));
        }

        // Convert to hex
        $r = dechex(round($r));
        $g = dechex(round($g));
        $b = dechex(round($b));

        // Make sure we get 2 digits for decimals
        $r = (strlen("" . $r) === 1) ? "0" . $r : $r;
        $g = (strlen("" . $g) === 1) ? "0" . $g : $g;
        $b = (strlen("" . $b) === 1) ? "0" . $b : $b;

        return $r . $g . $b;
    }

    public static function hexToRgb(string $color): array
    {
        // Sanity check
        $color = self::sanitizeHex($color);

        // Convert HEX to DEC
        $R = hexdec($color[0] . $color[1]);
        $G = hexdec($color[2] . $color[3]);
        $B = hexdec($color[4] . $color[5]);

        $RGB['R'] = $R;
        $RGB['G'] = $G;
        $RGB['B'] = $B;

        return $RGB;
    }

    public static function rgbToHex(array $rgb = array()): string
    {
        // Make sure it's RGB
        if (empty($rgb) || !isset($rgb["R"], $rgb["G"], $rgb["B"])) {
            throw new Exception("Param was not an RGB array");
        }

        // https://github.com/mexitek/phpColors/issues/25#issuecomment-88354815
        // Convert RGB to HEX
        $hex[0] = str_pad(dechex((int)$rgb['R']), 2, '0', STR_PAD_LEFT);
        $hex[1] = str_pad(dechex((int)$rgb['G']), 2, '0', STR_PAD_LEFT);
        $hex[2] = str_pad(dechex((int)$rgb['B']), 2, '0', STR_PAD_LEFT);

        // Make sure that 2 digits are allocated to each color.
        $hex[0] = (strlen($hex[0]) === 1) ? '0' . $hex[0] : $hex[0];
        $hex[1] = (strlen($hex[1]) === 1) ? '0' . $hex[1] : $hex[1];
        $hex[2] = (strlen($hex[2]) === 1) ? '0' . $hex[2] : $hex[2];

        return implode('', $hex);
    }

    public static function rgbToString(array $rgb = array()): string
    {
        // Make sure it's RGB
        if (empty($rgb) || !isset($rgb["R"], $rgb["G"], $rgb["B"])) {
            throw new Exception("Param was not an RGB array");
        }

        return 'rgb(' .
            $rgb['R'] . ', ' .
            $rgb['G'] . ', ' .
            $rgb['B'] . ')';
    }

    public function darken(int $amount = self::DEFAULT_ADJUST): string
    {
        // Darken
        $darkerHSL = $this->darkenHsl($this->_hsl, $amount);
        // Return as HEX
        return self::hslToHex($darkerHSL);
    }

    public function lighten(int $amount = self::DEFAULT_ADJUST): string
    {
        // Lighten
        $lighterHSL = $this->lightenHsl($this->_hsl, $amount);
        // Return as HEX
        return self::hslToHex($lighterHSL);
    }

    public function mix(string $hex2, int $amount = 0): string
    {
        $rgb2 = self::hexToRgb($hex2);
        $mixed = $this->mixRgb($this->_rgb, $rgb2, $amount);
        // Return as HEX
        return self::rgbToHex($mixed);
    }

    public function makeGradient(int $amount = self::DEFAULT_ADJUST): array
    {
        // Decide which color needs to be made
        if ($this->isLight()) {
            $lightColor = $this->_hex;
            $darkColor = $this->darken($amount);
        } else {
            $lightColor = $this->lighten($amount);
            $darkColor = $this->_hex;
        }

        // Return our gradient array
        return array("light" => $lightColor, "dark" => $darkColor);
    }

    public function isLight($color = false, int $lighterThan = 130): bool
    {
        // Get our color
        $color = ($color) ? $color : $this->_hex;

        // Calculate straight from rbg
        $r = hexdec($color[0] . $color[1]);
        $g = hexdec($color[2] . $color[3]);
        $b = hexdec($color[4] . $color[5]);

        return (($r * 299 + $g * 587 + $b * 114) / 1000 > $lighterThan);
    }

    public function isDark($color = false, int $darkerThan = 130): bool
    {
        // Get our color
        $color = ($color) ? $color : $this->_hex;

        // Calculate straight from rbg
        $r = hexdec($color[0] . $color[1]);
        $g = hexdec($color[2] . $color[3]);
        $b = hexdec($color[4] . $color[5]);

        return (($r * 299 + $g * 587 + $b * 114) / 1000 <= $darkerThan);
    }

    public function complementary(): string
    {
        // Get our HSL
        $hsl = $this->_hsl;

        // Adjust Hue 180 degrees
        $hsl['H'] += ($hsl['H'] > 180) ? -180 : 180;

        // Return the new value in HEX
        return self::hslToHex($hsl);
    }

    public function getHsl(): array
    {
        return $this->_hsl;
    }

    public function getHex(): string
    {
        return $this->_hex;
    }

    public function getRgb(): array
    {
        return $this->_rgb;
    }

    private function darkenHsl(array $hsl, int $amount = self::DEFAULT_ADJUST): array
    {
        // Check if we were provided a number
        if ($amount) {
            $hsl['L'] = ($hsl['L'] * 100) - $amount;
            $hsl['L'] = ($hsl['L'] < 0) ? 0 : $hsl['L'] / 100;
        } else {
            // We need to find out how much to darken
            $hsl['L'] /= 2;
        }

        return $hsl;
    }

    private function lightenHsl(array $hsl, int $amount = self::DEFAULT_ADJUST): array
    {
        // Check if we were provided a number
        if ($amount) {
            $hsl['L'] = ($hsl['L'] * 100) + $amount;
            $hsl['L'] = ($hsl['L'] > 100) ? 1 : $hsl['L'] / 100;
        } else {
            // We need to find out how much to lighten
            $hsl['L'] += (1 - $hsl['L']) / 2;
        }

        return $hsl;
    }

    private function mixRgb(array $rgb1, array $rgb2, int $amount = 0): array
    {
        $r1 = ($amount + 100) / 100;
        $r2 = 2 - $r1;

        $rmix = (($rgb1['R'] * $r1) + ($rgb2['R'] * $r2)) / 2;
        $gmix = (($rgb1['G'] * $r1) + ($rgb2['G'] * $r2)) / 2;
        $bmix = (($rgb1['B'] * $r1) + ($rgb2['B'] * $r2)) / 2;

        return array('R' => $rmix, 'G' => $gmix, 'B' => $bmix);
    }

    private static function hueToRgb(float $v1, float $v2, float $vH): float
    {
        if ($vH < 0) {
            ++$vH;
        }

        if ($vH > 1) {
            --$vH;
        }

        if ((6 * $vH) < 1) {
            return ($v1 + ($v2 - $v1) * 6 * $vH);
        }

        if ((2 * $vH) < 1) {
            return $v2;
        }

        if ((3 * $vH) < 2)
        {
            return ($v1 + ($v2 - $v1) * ((2 / 3) - $vH) * 6);
        }

        return $v1;
    }

    private static function sanitizeHex(string $hex): string
    {
        // Strip # sign if it is present
        $color = str_replace("#", "", $hex);

        // Validate hex string
        if (!preg_match('/^[a-fA-F0-9]+$/', $color))
        {
            throw new Exception("HEX color does not match format");
        }

        // Make sure it's 6 digits
        if (strlen($color) === 3)
        {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        } 
        elseif (strlen($color) !== 6)
        {
            throw new Exception("HEX color needs to be 6 or 3 digits long");
        }

        return $color;
    }

    public function __toString()
    {
        return "#" . $this->getHex();
    }

    public function __get(string $name)
    {
        switch (strtolower($name)) {
            case 'red':
            case 'r':
                return $this->_rgb["R"];
            case 'green':
            case 'g':
                return $this->_rgb["G"];
            case 'blue':
            case 'b':
                return $this->_rgb["B"];
            case 'hue':
            case 'h':
                return $this->_hsl["H"];
            case 'saturation':
            case 's':
                return $this->_hsl["S"];
            case 'lightness':
            case 'l':
                return $this->_hsl["L"];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
            E_USER_NOTICE
        );
        return null;
    }

    public function __set(string $name, $value)
    {
        switch (strtolower($name)) {
            case 'red':
            case 'r':
                $this->_rgb["R"] = $value;
                $this->_hex = self::rgbToHex($this->_rgb);
                $this->_hsl = self::hexToHsl($this->_hex);
                break;
            case 'green':
            case 'g':
                $this->_rgb["G"] = $value;
                $this->_hex = self::rgbToHex($this->_rgb);
                $this->_hsl = self::hexToHsl($this->_hex);
                break;
            case 'blue':
            case 'b':
                $this->_rgb["B"] = $value;
                $this->_hex = self::rgbToHex($this->_rgb);
                $this->_hsl = self::hexToHsl($this->_hex);
                break;
            case 'hue':
            case 'h':
                $this->_hsl["H"] = $value;
                $this->_hex = self::hslToHex($this->_hsl);
                $this->_rgb = self::hexToRgb($this->_hex);
                break;
            case 'saturation':
            case 's':
                $this->_hsl["S"] = $value;
                $this->_hex = self::hslToHex($this->_hsl);
                $this->_rgb = self::hexToRgb($this->_hex);
                break;
            case 'lightness':
            case 'light':
            case 'l':
                $this->_hsl["L"] = $value;
                $this->_hex = self::hslToHex($this->_hsl);
                $this->_rgb = self::hexToRgb($this->_hex);
                break;
        }
    }
}

