<?php

class QRCode
{
    private const ECC_L = 1;
    private const ECC_M = 0;
    private const ECC_Q = 3;
    private const ECC_H = 2;

    private const
        PAD0 = 0xEC;
    private const
        PAD1 = 0x11;

    private static int $version = 0;
    private static int $level = 0;
    private static int $size = 0;
    private static array $matrix = [];
    private static array $rsBlocks = [];

    public static function render(string $data, int $level = self::ECC_L, int $margin = 2): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        self::encode($data, $level);
        $moduleCount = self::$size;
        $pixelSize = 6;
        $marginPx = $margin * $pixelSize;
        $imageSize = $moduleCount * $pixelSize + $marginPx * 2;

        $img = imagecreatetruecolor($imageSize, $imageSize);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, $imageSize, $imageSize, $white);

        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount; $col++) {
                if (self::$matrix[$row][$col]) {
                    $x1 = $marginPx + $col * $pixelSize;
                    $y1 = $marginPx + $row * $pixelSize;
                    imagefilledrectangle($img, $x1, $y1, $x1 + $pixelSize - 1, $y1 + $pixelSize - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($img, null, 6);
        $png = ob_get_clean();
        imagedestroy($img);

        return base64_encode($png);
    }

    private static function encode(string $data, int $level): void
    {
        self::$level = $level;
        $bits = self::createData($data);
        self::$version = self::getMinimumVersion($data, $level);
        self::$size = self::$version * 4 + 17;
        self::$matrix = array_fill(0, self::$size, array_fill(0, self::$size, 0));
        self::$rsBlocks = self::getRsBlocks(self::$version, $level);

        $totalDataBytes = 0;
        foreach (self::$rsBlocks as $block) {
            $totalDataBytes += $block[0];
        }

        $maxLen = $totalDataBytes * 8;
        if (strlen($bits) > $maxLen) {
            throw new InvalidArgumentException('Datos muy largos para el nivel de corrección');
        }

        while (strlen($bits) % 8 !== 0) {
            $bits .= '0';
        }
        while (strlen($bits) < $maxLen) {
            $bits .= self::str_pad(self::$version % 2 === 0 ? self::PAD0 : self::PAD1, 8);
        }

        $bytes = [];
        for ($i = 0; $i < strlen($bits); $i += 8) {
            $bytes[] = bindec(substr($bits, $i, 8));
        }

        $dataCodewords = self::interleaveDataBytes($bytes);
        $eccCodewords = self::interleaveErrorCorrection($bytes);
        $finalCodewords = array_merge($dataCodewords, $eccCodewords);

        self::placeCodewords($finalCodewords);
        self::applyMask(0);
        self::writeFormatInfo(0);
    }

    private static function createData(string $data): string
    {
        $mode = 4;
        $bits = str_pad(decbin($mode), 4, '0', STR_PAD_LEFT);

        $len = strlen($data);
        $charCountBits = self::$version < 10 ? 8 : 16;
        $bits .= str_pad(decbin($len), $charCountBits, '0', STR_PAD_LEFT);

        $utf8 = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        for ($i = 0; $i < strlen($utf8); $i++) {
            $bits .= str_pad(decbin(ord($utf8[$i])), 8, '0', STR_PAD_LEFT);
        }

        $maxLen = self::getMaxLength(self::$level);
        $capBits = $maxLen * 8;
        while (strlen($bits) < $capBits) {
            $bits .= '0000';
            if (strlen($bits) < $capBits) {
                $bits .= '111011000100010';
            }
        }

        return $bits;
    }

    private static function getMinimumVersion(string $data, int $level): int
    {
        $chars = [
            'L' => [16, 28, 44, 64, 86, 108, 124, 154, 182, 216, 254, 290, 334, 365, 415, 453, 507, 563, 627, 669],
            'M' => [10, 22, 36, 52, 70, 86, 114, 138, 167, 196, 228, 262, 300, 328, 370, 428, 461, 523, 589, 647],
            'Q' => [7, 16, 26, 36, 46, 60, 74, 86, 100, 122, 140, 158, 180, 197, 223, 253, 283, 317, 341, 385],
            'H' => [4, 12, 20, 28, 36, 44, 60, 72, 86, 100, 122, 140, 158, 180, 197, 223, 243, 283, 325, 352],
        ];
        $ecList = [self::ECC_M => 'M', self::ECC_L => 'L', self::ECC_Q => 'Q', self::ECC_H => 'H'];
        $ecName = $ecList[$level] ?? 'L';
        $list = $chars[$ecName] ?? $chars['L'];
        $len = mb_strlen($data, 'UTF-8');
        foreach ($list as $version => $max) {
            if ($len <= $max) {
                return $version + 1;
            }
        }
        return 20;
    }

    private static function getMaxLength(int $level): int
    {
        $table = [
            1 => [17, 14, 11, 7],
            2 => [32, 26, 20, 14],
            3 => [53, 42, 32, 24],
            4 => [78, 62, 46, 34],
            5 => [106, 84, 60, 44],
        ];
        $ecIdx = [self::ECC_M => 1, self::ECC_L => 0, self::ECC_Q => 2, self::ECC_H => 3];
        $idx = $ecIdx[$level] ?? 0;
        return $table[self::$version][$idx] ?? 0;
    }

    private static function getRsBlocks(int $version, int $level): array
    {
        $specs = [
            1 => [[1, 26, 19], [1, 26, 16], [1, 26, 13], [1, 26, 9]],
            2 => [[1, 44, 34], [1, 44, 28], [1, 44, 22], [1, 44, 16]],
            3 => [[1, 70, 55], [1, 70, 44], [2, 35, 17], [2, 35, 13]],
            4 => [[1, 100, 80], [2, 50, 32], [2, 50, 24], [4, 25, 9]],
            5 => [[2, 134, 108], [2, 67, 43], [2, 33, 15, 2, 34, 16], [2, 33, 11, 2, 34, 12]],
        ];
        $ecIdx = [self::ECC_M => 1, self::ECC_L => 0, self::ECC_Q => 2, self::ECC_H => 3];
        $rs = $specs[$version][$ecIdx[$level]] ?? $specs[1][0];
        $blocks = [];
        if (count($rs) === 3) {
            $blocks[] = [$rs[0], $rs[1], $rs[2]];
        } else {
            $blocks[] = [$rs[0], $rs[1], $rs[2]];
            $blocks[] = [$rs[3], $rs[4], $rs[5]];
        }
        return $blocks;
    }

    private static function interleaveDataBytes(array $bytes): array
    {
        $offset = 0;
        $result = [];
        foreach (self::$rsBlocks as $block) {
            $count = $block[0];
            $length = $block[1];
            $ecLength = $block[2];
            $data = array_slice($bytes, $offset, $count * ($length - $ecLength));
            $offset += $count * ($length - $ecLength);
            for ($i = 0; $i < $count; $i++) {
                for ($j = 0; $j < $length - $ecLength; $j++) {
                    $result[] = $data[$i * ($length - $ecLength) + $j] ?? 0;
                }
            }
        }
        return $result;
    }

    private static function interleaveErrorCorrection(array $bytes): array
    {
        $offset = 0;
        $result = [];
        foreach (self::$rsBlocks as $block) {
            $count = $block[0];
            $length = $block[1];
            $ecLength = $block[2];
            $blockBytes = array_slice($bytes, $offset, $count * ($length - $ecLength));
            $offset += $count * ($length - $ecLength);
            for ($i = 0; $i < $count; $i++) {
                $segment = array_slice($blockBytes, $i * ($length - $ecLength), $length - $ecLength);
                $ecc = self::rsEncode($segment, $ecLength);
                $result = array_merge($result, $ecc);
            }
        }
        return $result;
    }

    private static function rsEncode(array $data, int $ecLength): array
    {
        $gen = self::rsGenerator($ecLength);
        $msg = array_pad($data, count($data) + $ecLength, 0);
        for ($i = 0; $i < count($data); $i++) {
            $coef = $msg[$i];
            if ($coef !== 0) {
                for ($j = 0; $j < count($gen); $j++) {
                    $msg[$i + $j] ^= self::gfMul($gen[$j], $coef);
                }
            }
        }
        return array_slice($msg, count($data));
    }

    private static function rsGenerator(int $ecLength): array
    {
        $g = [1];
        for ($i = 0; $i < $ecLength; $i++) {
            $g = self::polyMul($g, [1, self::gfExp($i)]);
        }
        return $g;
    }

    private static function polyMul(array $a, array $b): array
    {
        $r = array_fill(0, count($a) + count($b) - 1, 0);
        for ($i = 0; $i < count($a); $i++) {
            for ($j = 0; $j < count($b); $j++) {
                $r[$i + $j] ^= self::gfMul($a[$i], $b[$j]);
            }
        }
        return $r;
    }

    private static function gfMul(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }
        return self::gfExp((self::gfLog($x) + self::gfLog($y)) % 255);
    }

    private static function gfExp(int $x): int
    {
        static $exp = null;
        if ($exp === null) {
            $exp = [1];
            $v = 1;
            for ($i = 1; $i < 256; $i++) {
                $v = ($v << 1) ^ ($v & 128 ? 0x11D : 0);
                $exp[] = $v & 0xFF;
            }
        }
        return $exp[$x % 255];
    }

    private static function gfLog(int $x): int
    {
        static $log = null;
        if ($log === null) {
            $log = array_fill(0, 256, 0);
            for ($i = 0; $i < 256; $i++) {
                $log[self::gfExp($i)] = $i;
            }
        }
        return $log[$x] ?? 0;
    }

    private static function placeCodewords(array $codewords): void
    {
        $size = self::$size;
        $bits = '';
        foreach ($codewords as $c) {
            $bits .= str_pad(decbin($c), 8, '0', STR_PAD_LEFT);
        }

        $col = $size - 1;
        $row = $size - 1;
        $bitIdx = 0;
        $goingUp = true;

        while ($col >= 0) {
            if ($col === 6) {
                $col--;
            }
            for ($dir = 0; $dir < 2; $dir++) {
                $r = $goingUp ? ($size - 1 - $dir) : $dir;
                if (!self::$matrix[$r][$col] && !self::isReservedModule($r, $col)) {
                    self::$matrix[$r][$col] = $bits[$bitIdx] === '1' ? 1 : 0;
                    $bitIdx++;
                }
            }
            $col -= 2;
            if (!$goingUp && $col >= 0) {
                $col += $goingUp ? 0 : 0;
                $goingUp = true;
            } elseif ($goingUp && $col >= 0) {
                $goingUp = false;
            }
        }

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if (self::isReservedModule($r, $c)) {
                    self::$matrix[$r][$c] = 0;
                }
            }
        }
    }

    private static function isReservedModule(int $row, int $col): bool
    {
        if (self::isFinderPattern($row, $col)) return true;
        if ($row === 6 || $col === 6) return true;
        if (self::$version >= 2 && self::isAlignmentPattern($row, $col)) return true;
        if ($row === 8 && $col >= 8 && $col < 9) return true;
        if ($col === 8 && $row >= 8 && $row < 9) return true;
        return false;
    }

    private static function isFinderPattern(int $row, int $col): bool
    {
        foreach ([[0, 0], [0, self::$size - 7], [self::$size - 7, 0]] as $pos) {
            $pr = $pos[0];
            $pc = $pos[1];
            if ($row >= $pr && $row < $pr + 7 && $col >= $pc && $col < $pc + 7) {
                return true;
            }
        }
        return false;
    }

    private static function isAlignmentPattern(int $row, int $col): bool
    {
        $centers = [6, 18];
        foreach ($centers as $r) {
            foreach ($centers as $c) {
                if ($r === 6 || $c === 6) continue;
                $dist = abs($row - $r);
                $dist2 = abs($col - $c);
                if ($dist <= 2 && $dist2 <= 2) {
                    return true;
                }
            }
        }
        return false;
    }

    private static function applyMask(int $maskPattern): void
    {
        $size = self::$size;
        for ($row = 0; $row < $size; $row++) {
            for ($col = 0; $col < $size; $col++) {
                if (self::applyMaskCondition($maskPattern, $row, $col)) {
                    self::$matrix[$row][$col] ^= 1;
                }
            }
        }
    }

    private static function applyMaskCondition(int $pattern, int $row, int $col): bool
    {
        if (self::isReservedModule($row, $col)) return false;
        switch ($pattern) {
            case 0:
                return ($row + $col) % 2 === 0;
            case 1:
                return $row % 2 === 0;
            case 2:
                return $col % 3 === 0;
            case 3:
                return ($row + $col) % 3 === 0;
            case 4:
                return (intdiv($row, 2) + intdiv($col, 3)) % 2 === 0;
            case 5:
                return (($row * $col) % 2 + ($row * $col) % 3) === 0;
            case 6:
                return ((($row * $col) % 2) + (($row * $col) % 3)) % 2 === 0;
            case 7:
                return ((($row + $col) % 2) + (($row * $col) % 3)) % 2 === 0;
        }
        return false;
    }

    private static function writeFormatInfo(int $maskPattern): void
    {
        $bits = (self::$level << 3) | $maskPattern;
        $data = 0b101010000010010;
        for ($i = 0; $i < 15; $i++) {
            $dataBit = ($bits >> (14 - $i)) & 1;
            $dataBit ^= ($data >> (14 - $i)) & 1;
            if ($dataBit) {
                $data ^= (1 << (14 - $i));
            }
        }

        $size = self::$size;
        for ($i = 0; $i < 15; $i++) {
            $bit = ($data >> (14 - $i)) & 1;
            if ($i < 6) {
                self::$matrix[8][$i] = $bit;
            } elseif ($i === 6) {
                self::$matrix[8][7] = $bit;
            } elseif ($i < 8) {
                self::$matrix[8][5 - ($i - 7)] = $bit;
            } else {
                self::$matrix[8][self::$size - 15 + $i] = $bit;
            }
            if ($i < 8) {
                self::$matrix[self::$size - $i - 1][8] = $bit;
            } elseif ($i === 8) {
                self::$matrix[self::$size - 7 - 1][8] = $bit;
            } else {
                self::$matrix[14 - $i + 1][8] = $bit;
            }
        }
    }

    private static function str_pad(int $val, int $len): string
    {
        $s = '';
        for ($i = $len - 1; $i >= 0; $i--) {
            $s .= ($val & (1 << $i)) ? '1' : '0';
        }
        return $s;
    }
}
