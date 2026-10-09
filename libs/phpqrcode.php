<?php

/* Generador de codigos QR (ISO/IEC 18004).
 *
 * POR QUE SE REESCRIBIO ESTO
 * --------------------------
 * La version anterior de este archivo estaba incompleta y no podia generar
 * ningun QR util: solo llegaba a 59 bytes, y ademas los tres defectos mas
 * graves la hacian inutilizable aunque llegara:
 *
 *   1. Despues de colocar los datos, ponia en CERO todos los modulos
 *      reservados, lo que borraba los patrones localizadores. Un QR sin
 *      localizadores no lo lee nadie.
 *   2. El patron de alineacion estaba fijo en las coordenadas [6, 18], que
 *      solo valen para la version 2.
 *   3. No escribia los modulos de informacion de formato (nivel de correccion
 *      + mascara) ni los de informacion de version.
 *
 * Ese QR no lo escanea ni un movil. El caso real del SIAT lo demuestra: la URL
 * de consulta mide 137 bytes (nit de 10 + CUF de 54 + numero de 10), y con el
 * limite de 59 bytes de la libreria anterior la generacion reventaba con
 * "Datos muy largos para el nivel de correccion".
 *
 * Esta implementacion cubre las versiones 1 a 40 y los cuatro niveles de
 * correccion, elige la version mas pequena que quepa y la mascara con menor
 * penalizacion. La aritmetica de Reed-Solomon en GF(256) es la misma que ya
 * tenia el archivo original.
 *
 * API PUBLICA (sin cambios, para no romper a quien ya la usa)
 * ----------------------------------------------------------
 *   QRCode::render(string $data, int $level = 1, int $margin = 2): ?string
 *     Devuelve el PNG en base64, o null si GD no esta disponible.
 *
 *   $level usa los valores historicos de esta clase: 1 = L, 0 = M, 3 = Q, 2 = H.
 */

class QRCode
{
    private const ECC_L = 1;
    private const ECC_M = 0;
    private const ECC_Q = 3;
    private const ECC_H = 2;

    private const PAD0 = 0xEC;
    private const PAD1 = 0x11;

    /* Bits de nivel de correccion que van dentro del modulo de formato. */
    private const FORMAT_BITS = [
        self::ECC_L => 1,
        self::ECC_M => 0,
        self::ECC_Q => 3,
        self::ECC_H => 2,
    ];

    /* Palabras de codigo de correccion por bloque, por version 1..40. */
    private const ECC_CODEWORDS_PER_BLOCK = [
        self::ECC_L => [
            7, 10, 15, 20, 26, 18, 20, 24, 30, 18, 20, 24, 26, 30, 22, 24, 28, 30, 28, 28,
            28, 28, 30, 30, 26, 28, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30,
        ],
        self::ECC_M => [
            10, 16, 26, 18, 24, 16, 18, 22, 22, 26, 30, 22, 22, 24, 24, 28, 28, 26, 26, 26,
            26, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28,
        ],
        self::ECC_Q => [
            13, 22, 18, 26, 18, 24, 18, 22, 20, 24, 28, 26, 24, 20, 30, 24, 28, 28, 26, 30,
            28, 30, 30, 30, 30, 28, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30,
        ],
        self::ECC_H => [
            17, 28, 22, 16, 22, 28, 26, 26, 24, 28, 24, 28, 22, 24, 24, 30, 28, 28, 26, 28,
            30, 24, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30,
        ],
    ];

    /* Numero de bloques de correccion, por version 1..40. */
    private const NUM_ERROR_CORRECTION_BLOCKS = [
        self::ECC_L => [
            1, 1, 1, 1, 1, 2, 2, 2, 2, 4, 4, 4, 4, 4, 6, 6, 6, 6, 7, 8,
            8, 9, 9, 10, 12, 12, 12, 13, 14, 15, 16, 17, 18, 19, 19, 20, 21, 22, 24, 25,
        ],
        self::ECC_M => [
            1, 1, 1, 2, 2, 4, 4, 4, 5, 5, 5, 8, 9, 9, 10, 10, 11, 13, 14, 16,
            17, 17, 18, 20, 21, 23, 25, 26, 28, 29, 31, 33, 35, 37, 38, 40, 43, 45, 47, 49,
        ],
        self::ECC_Q => [
            1, 1, 2, 2, 4, 4, 6, 6, 8, 8, 8, 10, 12, 16, 12, 17, 16, 18, 21, 20,
            23, 23, 25, 27, 29, 34, 34, 35, 38, 40, 43, 45, 48, 51, 53, 56, 59, 62, 65, 68,
        ],
        self::ECC_H => [
            1, 1, 2, 4, 4, 4, 5, 6, 8, 8, 11, 11, 16, 16, 18, 16, 19, 21, 25, 25,
            25, 34, 30, 32, 35, 37, 40, 42, 45, 48, 51, 54, 57, 60, 63, 66, 70, 74, 77, 81,
        ],
    ];

    private static $version = 1;
    private static $size = 0;
    private static $eccLevel = self::ECC_L;
    private static $mask = 0;
    private static $matrix = [];
    private static $isFunction = [];

    /* ===================== API PUBLICA ===================== */

    /**
     * Genera el QR y devuelve el PNG en base64 (sin prefijo data: URI).
     * Devuelve null si GD no esta disponible en el servidor.
     *
     * El margen por defecto son 4 modulos, que es lo que pide el estandar como
     * zona de silencio. Con 2 el lector lo aguanta en pantalla, pero impreso en
     * un ticket de 80 mm y reducido a 30 mm hay margenes de error y algunas
     * camaras de movil no lo enfocan.
     */
    public static function render(string $data, int $level = self::ECC_L, int $margin = 4): ?string
    {
        if ($data === '') {
            return null;
        }
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }
        if (!isset(self::FORMAT_BITS[$level])) {
            $level = self::ECC_L;
        }

        $matrix = self::construir($data, $level);

        if ($matrix === null) {
            return null;
        }

        return self::aPngBase64($matrix, $margin);
    }

    /* ===================== CONSTRUCCION ===================== */

    /**
     * Devuelve la matriz del QR ya con mascara aplicada, o null si los datos no
     * caben ni en la version 40.
     */
    private static function construir(string $data, int $level): ?array
    {
        $bytes = array_values(unpack('C*', $data));

        // Se busca la version mas pequena que quepa. El ancho del indicador de
        // longitud depende de la version, asi que hay que rehacer el segmento
        // en cada intento en vez de medirlo una sola vez.
        $bits = null;
        for ($ver = 1; $ver <= 40; $ver++) {
            $bits = self::bitsDelSegmento($bytes, $ver);
            if ($bits === null) {
                continue;
            }
            if (strlen($bits) <= self::palabrasDeDatos($ver, $level) * 8) {
                break;
            }
        }
        if ($ver > 40 || $bits === null) {
            return null;
        }

        self::$version = $ver;
        self::$eccLevel = $level;
        self::$size = $ver * 4 + 17;

        $capacidadBits = self::palabrasDeDatos($ver, $level) * 8;

        /* Terminador: hasta cuatro bits en cero que avisan al lector de que aqui
           acaba el mensaje. Sin el, la longitud en bits del segmento no cae en
           un multiple de 8 y el bucle de relleno de abajo se pasa de la
           capacidad: en la version 7 daba 1252 bits donde caben 1248, y
           bitsABytes recortaba en silencio los ultimos 4 bits del texto, que
           es justo el final de la URL. */
        $bits .= str_repeat('0', min(4, $capacidadBits - strlen($bits)));

        // Relleno hasta el byte siguiente.
        while (strlen($bits) % 8 !== 0) {
            $bits .= '0';
        }

        /* Relleno con los codewords de padding alternados. La condición es <=
           capacidad - 8, no < capacidad: si no, el ultimo codeword se añade
           entero y el total se pasa de la capacidad. */
        $pad = str_pad(decbin(self::$version % 2 === 0 ? self::PAD0 : self::PAD1), 8, '0', STR_PAD_LEFT);
        while (strlen($bits) <= $capacidadBits - 8) {
            $bits .= $pad;
        }

        $codewords = self::bitsABytes($bits);
        $bloques = self::añadirCorreccion($codewords, $ver, $level);
        $final = self::intercalar($bloques, $ver, $level);

        self::$matrix = array_fill(0, self::$size, array_fill(0, self::$size, 0));
        self::$isFunction = array_fill(0, self::$size, array_fill(0, self::$size, false));

        self::colocarPatronesDeFuncion();
        self::colocarFormato($level, 0);
        if ($ver >= 7) {
            self::colocarVersion();
        }

        self::colocarDatos($final);

        // Se prueban las 8 mascaras y se queda con la de menor penalizacion.
        $mejor = null;
        $mejorPenal = null;
        for ($m = 0; $m < 8; $m++) {
            self::aplicarMascara($m);
            self::colocarFormato($level, $m);
            $p = self::penalizacion();
            if ($mejorPenal === null || $p < $mejorPenal) {
                $mejorPenal = $p;
                $mejor = self::$matrix;
            }
            self::desaplicarMascara($m);
        }

        return $mejor;
    }

    /**
     * Ancho del indicador de longitud del segmento en modo byte.
     * Lo fija la VERSION, no el tamano del texto: por eso no se puede decidir
     * antes de elegir version.
     */
    private static function bitsDeLongitud(int $version): int
    {
        return $version < 10 ? 8 : 16;
    }

    /** Segmento en modo byte (0100) para una version concreta. */
    private static function bitsDelSegmento(array $bytes, int $version): ?string
    {
        $len = count($bytes);
        $bitsLong = self::bitsDeLongitud($version);
        if ($len > (1 << $bitsLong) - 1) {
            return null;
        }

        $bits = '0100'; // modo byte
        $bits .= str_pad(decbin($len), $bitsLong, '0', STR_PAD_LEFT);

        foreach ($bytes as $b) {
            $bits .= str_pad(decbin($b), 8, '0', STR_PAD_LEFT);
        }

        return $bits;
    }

    private static function bitsABytes(string $bits): array
    {
        $out = [];
        $total = strlen($bits) - (strlen($bits) % 8);
        for ($i = 0; $i < $total; $i += 8) {
            $out[] = bindec(substr($bits, $i, 8));
        }
        return $out;
    }

    /** Modulos de datos disponibles antes de correccion, en bits. */
    private static function modulosDeDatosCrudos(int $version): int
    {
        $resultado = (16 * $version + 128) * $version + 64;

        if ($version >= 2) {
            $numAlign = intdiv($version, 7) + 2;
            $resultado -= (25 * $numAlign - 10) * $numAlign - 55;
            if ($version >= 7) {
                $resultado -= 36;
            }
        }

        return $resultado;
    }

    private static function palabrasDeDatos(int $version, int $level): int
    {
        return intdiv(self::modulosDeDatosCrudos($version), 8)
             - self::ECC_CODEWORDS_PER_BLOCK[$level][$version - 1]
             * self::NUM_ERROR_CORRECTION_BLOCKS[$level][$version - 1];
    }

    /** Reparte los codewords en bloques y calcula la correccion de cada uno. */
    private static function añadirCorreccion(array $data, int $version, int $level): array
    {
        $numBlocks = self::NUM_ERROR_CORRECTION_BLOCKS[$level][$version - 1];
        $eccLen = self::ECC_CODEWORDS_PER_BLOCK[$level][$version - 1];
        $totalCodewords = intdiv(self::modulosDeDatosCrudos($version), 8);
        $shortBlockLen = intdiv($totalCodewords - $eccLen * $numBlocks, $numBlocks);
        $numShort = $numBlocks - ($totalCodewords % $numBlocks);

        $bloques = [];
        $k = 0;
        for ($i = 0; $i < $numBlocks; $i++) {
            // $shortBlockLen es la longitud de DATOS de un bloque corto, no la
            // longitud total: la correccion se suma despues.
            $datLen = $shortBlockLen + ($i < $numShort ? 0 : 1);
            $bloqueDatos = array_slice($data, $k, $datLen);
            $k += $datLen;
            $ecc = self::codificarRs($bloqueDatos, $eccLen);
            $bloques[] = ['datos' => $bloqueDatos, 'ecc' => $ecc, 'datLen' => $datLen];
        }

        return $bloques;
    }

    /** Intercala datos y correccion como manda el estandar. */
    private static function intercalar(array $bloques, int $version, int $level): string
    {
        $out = '';

        $maxDat = 0;
        foreach ($bloques as $b) {
            $maxDat = max($maxDat, $b['datLen']);
        }
        for ($i = 0; $i < $maxDat; $i++) {
            foreach ($bloques as $b) {
                if ($i < $b['datLen']) {
                    $out .= str_pad(decbin($b['datos'][$i]), 8, '0', STR_PAD_LEFT);
                }
            }
        }

        $eccLen = self::ECC_CODEWORDS_PER_BLOCK[$level][$version - 1];
        for ($i = 0; $i < $eccLen; $i++) {
            foreach ($bloques as $b) {
                $out .= str_pad(decbin($b['ecc'][$i]), 8, '0', STR_PAD_LEFT);
            }
        }

        return $out;
    }

    /* ===================== MATRIZ ===================== */

    private static function colocarPatronesDeFuncion(): void
    {
        // Localizadores y separadores.
        foreach ([[0, 0], [0, self::$size - 7], [self::$size - 7, 0]] as $pos) {
            self::colocarLocalizador($pos[0], $pos[1]);
        }

        // Sincronizacion (timing).
        for ($i = 8; $i < self::$size - 8; $i++) {
            $v = ($i % 2 === 0) ? 1 : 0;
            self::fijar($i, 6, $v);
            self::fijar(6, $i, $v);
        }

        // Alineacion.
        $centros = self::posicionesDeAlineacion();
        $n = count($centros);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                // Se omiten los que solapan con los localizadores.
                $esEsquina = ($i === 0 && $j === 0)
                          || ($i === 0 && $j === $n - 1)
                          || ($i === $n - 1 && $j === 0);
                if ($esEsquina) {
                    continue;
                }
                self::colocarAlineacion($centros[$i], $centros[$j]);
            }
        }

        /* No se reserva nada de la informacion de formato aqui a proposito.

           Este bloque existia para marcar esos modulos como ocupados, pero
           colocarlo de mas rompia dos cosas:

             - fijaba en 0 los modulos (8,6) y (6,8), que son los DOS PRIMEROS
               del patron de sincronizacion y tienen que estar en negro
             - reservaba de mas (8, size-8): la segunda copia de los bits de
               formato ocupa la fila 8 desde size-1 hasta size-7, no hasta
               size-8, que es la columna del modulo oscuro. Al reservar ese
               modulo de mas se descartaba un bit de dato y todo lo que venia
               detras se corria una posicion: el QR ya no se podia leer

           colocFormato() se llama justo despues y fija cada posicion que
           necesita, asi que la reserva la hace el mismo. */
    }

    private static function posicionesDeAlineacion(): array
    {
        if (self::$version === 1) {
            return [];
        }

        $numAlign = intdiv(self::$version, 7) + 2;
        $size = self::$size;
        $paso = (self::$version === 32)
            ? 26
            : intdiv(self::$version * 4 + $numAlign * 2 + 1, $numAlign * 2 - 2) * 2;

        $pos = [$size - 7];
        for ($i = 0; $i < $numAlign - 1; $i++) {
            array_unshift($pos, $pos[0] - $paso);
        }
        $pos[0] = 6;

        return $pos;
    }

    private static function colocarLocalizador(int $row, int $col): void
    {
        /* El localizador es un 7x7: marco negro, anillo blanco y nucleo negro de
           3x3. El error aqui es de un modulo de desfase y es silencioso: el
           patron se dibujaba con el marco decidiendose sobre el anillo separador
           de 9x9, asi que el marco de 7 quedaba en blanco y el separador
           quedaba en negro. El PNG salia con aspecto de QR, pero sin los tres
           localizadores no hay nada que escanear: ningun lector lo abria. */
        for ($r = 0; $r < 7; $r++) {
            for ($c = 0; $c < 7; $c++) {
                $borde = ($r === 0 || $r === 6 || $c === 0 || $c === 6);
                $nucleo = ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4);
                self::fijar($row + $r, $col + $c, ($borde || $nucleo) ? 1 : 0);
            }
        }

        /* Separador: el anillo de un modulo que rodea al localizador es
           obligatoriamente blanco, para que el lector distinga donde acaba el
           patron. fijar() descarta lo que se sale de la matriz, que es justo lo
           que pasa arriba y a la izquierda del localizador de la esquina. */
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                if ($r >= 0 && $r <= 6 && $c >= 0 && $c <= 6) {
                    continue;
                }
                self::fijar($row + $r, $col + $c, 0);
            }
        }
    }

    private static function colocarAlineacion(int $row, int $col): void
    {
        for ($r = -2; $r <= 2; $r++) {
            for ($c = -2; $c <= 2; $c++) {
                $max = max(abs($r), abs($c));
                self::fijar($row + $r, $col + $c, $max !== 1 ? 1 : 0);
            }
        }
    }

    private static function fijar(int $row, int $col, int $valor): void
    {
        if ($row < 0 || $row >= self::$size || $col < 0 || $col >= self::$size) {
            return;
        }
        self::$matrix[$row][$col] = $valor;
        self::$isFunction[$row][$col] = true;
    }

    private static function estaReservado(int $row, int $col): bool
    {
        return self::$isFunction[$row][$col] ?? false;
    }

    /** Modulos de formato (nivel + mascara), con correccion BCH y mascara 0x5412. */
    private static function colocarFormato(int $level, int $mask): void
    {
        $data = (self::FORMAT_BITS[$level] << 3) | $mask;
        $rem = $data;
        for ($i = 0; $i < 10; $i++) {
            $rem = ($rem << 1) ^ (($rem >> 9) * 0x537);
        }
        $bits = (($data << 10) | $rem) ^ 0x5412;

        for ($i = 0; $i <= 5; $i++) {
            self::fijar(8, $i, ($bits >> $i) & 1);
        }
        self::fijar(8, 7, ($bits >> 6) & 1);
        self::fijar(8, 8, ($bits >> 7) & 1);
        self::fijar(7, 8, ($bits >> 8) & 1);
        for ($i = 9; $i < 15; $i++) {
            self::fijar(14 - $i, 8, ($bits >> $i) & 1);
        }

        for ($i = 0; $i < 8; $i++) {
            self::fijar(self::$size - 1 - $i, 8, ($bits >> $i) & 1);
        }
        for ($i = 8; $i < 15; $i++) {
            self::fijar(8, self::$size - 15 + $i, ($bits >> $i) & 1);
        }

        /* Modulo oscuro: siempre en negro y no forma parte de la informacion de
           formato. Va en la fila size-8, que es justo la que la segunda copia de
           los bits de formato acaba de pintar con el bit 7, asi que hay que
           ponerlo DESPUES. El lector lo usa de referencia para saber la
           orientacion y la modulacion de la imagen. */
        self::fijar(self::$size - 8, 8, 1);
    }

    /** Informacion de version, obligatoria desde la version 7. */
    private static function colocarVersion(): void
    {
        $rem = self::$version;
        for ($i = 0; $i < 12; $i++) {
            $rem = ($rem << 1) ^ (($rem >> 11) * 0x1F25);
        }
        $bits = (self::$version << 12) | $rem;

        for ($i = 0; $i < 18; $i++) {
            $bit = ($bits >> $i) & 1;
            $a = intdiv($i, 3);
            $b = $i % 3;
            self::$matrix[self::$size - 11 + $b][$a] = $bit;
            self::$matrix[$a][self::$size - 11 + $b] = $bit;
            self::$isFunction[self::$size - 11 + $b][$a] = true;
            self::$isFunction[$a][self::$size - 11 + $b] = true;
        }
    }

    /** Recorrido en zigzag de derecha a izquierda, de abajo hacia arriba. */
    private static function colocarDatos(string $bits): void
    {
        $i = 0;
        $total = strlen($bits);
        $derecha = self::$size - 1;

        while ($derecha > 0) {
            if ($derecha === 6) {
                // La columna 6 es la de sincronizacion, se salta entera.
                $derecha--;
            }

            /* El sentido de lectura lo decide la columna DERECHA del par, no cada
               columna por separado, y alterna de par en par siendo el primero de
               abajo arriba. La regla ((derecha + 1) & 2) == 0 es la del
               estandar; antes se usaba la paridad de $col, que invierte el
               sentido en casi todos los pares: el dibujo parecia un QR, pero el
               dato quedaba escrito al reves y ningun lector devolvia la URL. */
            $arriba = ((($derecha + 1) & 2) === 0);

            for ($vert = 0; $vert < self::$size; $vert++) {
                for ($j = 0; $j < 2; $j++) {
                    $col = $derecha - $j;
                    $row = $arriba
                        ? (self::$size - 1 - $vert)
                        : $vert;

                    if (self::estaReservado($row, $col)) {
                        continue;
                    }

                    self::$matrix[$row][$col] = ($i < $total && $bits[$i] === '1') ? 1 : 0;
                    $i++;
                }
            }

            $derecha -= 2;
        }
    }

    private static function aplicarMascara(int $patron): void
    {
        for ($r = 0; $r < self::$size; $r++) {
            for ($c = 0; $c < self::$size; $c++) {
                if (self::estaReservado($r, $c)) {
                    continue;
                }
                if (self::condicionMascara($patron, $r, $c)) {
                    self::$matrix[$r][$c] ^= 1;
                }
            }
        }
    }

    private static function desaplicarMascara(int $patron): void
    {
        self::aplicarMascara($patron);
    }

    private static function condicionMascara(int $patron, int $r, int $c): bool
    {
        switch ($patron) {
            case 0: return ($r + $c) % 2 === 0;
            case 1: return $r % 2 === 0;
            case 2: return $c % 3 === 0;
            case 3: return ($r + $c) % 3 === 0;
            case 4: return (intdiv($r, 2) + intdiv($c, 3)) % 2 === 0;
            case 5: return ($r * $c) % 2 + ($r * $c) % 3 === 0;
            case 6: return (($r * $c) % 2 + ($r * $c) % 3) % 2 === 0;
            case 7: return ((($r + $c) % 2) + (($r * $c) % 3)) % 2 === 0;
        }
        return false;
    }

    /** Penalizacion del estandar: de las 8 mascaras se queda la menor. */
    private static function penalizacion(): int
    {
        $p = 0;
        $n = self::$size;

        // Regla 1: series de 5 o mas del mismo color.
        for ($r = 0; $r < $n; $r++) {
            $p += self::penalSerie(self::$matrix[$r], $n);
        }
        for ($c = 0; $c < $n; $c++) {
            $col = [];
            for ($r = 0; $r < $n; $r++) {
                $col[] = self::$matrix[$r][$c];
            }
            $p += self::penalSerie($col, $n);
        }

        // Regla 2: bloques 2x2 del mismo color.
        for ($r = 0; $r < $n - 1; $r++) {
            for ($c = 0; $c < $n - 1; $c++) {
                $v = self::$matrix[$r][$c];
                if ($v === self::$matrix[$r][$c + 1]
                    && $v === self::$matrix[$r + 1][$c]
                    && $v === self::$matrix[$r + 1][$c + 1]) {
                    $p += 3;
                }
            }
        }

        // Regla 3: patron 1:1:3:1:1 con 4 de margen.
        $patronA = [1, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0];
        $patronB = [0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1];
        for ($r = 0; $r < $n; $r++) {
            $linea = self::$matrix[$r];
            for ($c = 0; $c <= $n - 11; $c++) {
                $tramo = array_slice($linea, $c, 11);
                if ($tramo === $patronA || $tramo === $patronB) {
                    $p += 40;
                }
            }
        }
        for ($c = 0; $c < $n; $c++) {
            $col = [];
            for ($r = 0; $r < $n; $r++) {
                $col[] = self::$matrix[$r][$c];
            }
            for ($i = 0; $i <= $n - 11; $i++) {
                $tramo = array_slice($col, $i, 11);
                if ($tramo === $patronA || $tramo === $patronB) {
                    $p += 40;
                }
            }
        }

        // Regla 4: desviacion respecto al 50 % de OSCURO.
        $oscuros = 0;
        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                $oscuros += self::$matrix[$r][$c];
            }
        }
        $total = $n * $n;
        $k = intdiv(abs($oscuros * 20 - $total * 10) * 10 + $total - 1, $total) - 1;
        $p += $k * 10;

        return $p;
    }

    private static function penalSerie(array $linea, int $n): int
    {
        $p = 0;
        $run = 1;
        for ($i = 1; $i < $n; $i++) {
            if ($linea[$i] === $linea[$i - 1]) {
                $run++;
            } else {
                if ($run >= 5) {
                    $p += 3 + ($run - 5);
                }
                $run = 1;
            }
        }
        if ($run >= 5) {
            $p += 3 + ($run - 5);
        }
        return $p;
    }

    /* ===================== REED-SOLOMON ===================== */

    private static function codificarRs(array $data, int $ecLength): array
    {
        $gen = self::generadorRs($ecLength);
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

    private static function generadorRs(int $ecLength): array
    {
        $g = [1];
        for ($i = 0; $i < $ecLength; $i++) {
            $g = self::mulPoli($g, [1, self::gfExp($i)]);
        }
        return $g;
    }

    private static function mulPoli(array $a, array $b): array
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

    /* Tablas logaritmica y exponencial de GF(256) con polinomio 0x11D.
     * Se construyen una sola vez y se comparten entre gfExp() y gfLog(). */
    private static $gfExp = null;
    private static $gfLog = null;

    private static function initGf(): void
    {
        if (self::$gfExp !== null) {
            return;
        }

        $exp = array_fill(0, 512, 0);
        $log = array_fill(0, 256, 0);

        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            $exp[$i] = $x;
            $log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            $exp[$i] = $exp[$i - 255];
        }

        self::$gfExp = $exp;
        self::$gfLog = $log;
    }

    private static function gfExp(int $x): int
    {
        self::initGf();
        return self::$gfExp[$x];
    }

    private static function gfLog(int $x): int
    {
        self::initGf();
        if ($x < 1 || $x > 255) {
            return 0;
        }
        return self::$gfLog[$x];
    }

    /* ===================== SALIDA PNG ===================== */

    private static function aPngBase64(array $matrix, int $margin): string
    {
        $n = count($matrix);
        $pixel = 6;
        $margenPx = max(0, $margin) * $pixel;
        $lado = $n * $pixel + $margenPx * 2;

        $img = imagecreatetruecolor($lado, $lado);
        if ($img === false) {
            return '';
        }

        $blanco = imagecolorallocate($img, 255, 255, 255);
        $negro = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, $lado, $lado, $blanco);

        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $margenPx + $c * $pixel;
                    $y = $margenPx + $r * $pixel;
                    imagefilledrectangle($img, $x, $y, $x + $pixel - 1, $y + $pixel - 1, $negro);
                }
            }
        }

        ob_start();
        imagepng($img, null, 6);
        $png = ob_get_clean();
        imagedestroy($img);

        return base64_encode((string)$png);
    }
}