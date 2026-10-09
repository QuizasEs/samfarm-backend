# Bitácora de certificación SIAT

Registro de lo que se hizo contra el SIAT, con qué resultado y cómo se rehace
cada cosa. Es el documento de referencia para construir el módulo de
facturación: si algo falla en el SIN, la respuesta está acá.

- **Proyecto:** `C:\xampp\htdocs\samfarm-backend`
- **Base de datos:** `samfarm_db` en `localhost`, usuario `root`, sin contraseña
- **NIT:** `6006007013` · **Código de sistema:** `373641B66F08A38C69CE`
- **Ambiente:** PILOTO, `codigoAmbiente = 2` · **Modalidad:** `1`

---

## 1. Reglas del proyecto que se respetaron

Estas restricciones condicionan todo lo que sigue. Si se rompen, el trabajo
pierde valor aunque el SIN acepte.

| Regla | Motivo |
|---|---|
| No se altera el esquema de la base | La certificación corre sobre la base que también usa la farmacia |
| No se crean archivos `.sql` | La migración de `puntos_venta.pv_cufd_anterior` sigue prohibida hasta que se autorice |
| No se toca `ventas`, `factura`, `detalle_venta`, `clientes`, `medicamento` | Módulo de producción: la certificación no puede contaminarlo |
| Las facturas de certificación no se registran en `facturacion_electronica` | Esa tabla tiene FK a `factura` (`fk_fe_fa`); escribir ahí obligaría a crear ventas reales |
| `fe_id=11` / `fa_id=631` no se borra ni se reemite | Queda en `CONTINGENCIA` como referencia histórica |

**Consecuencia de la regla anterior:** las facturas de certificación existen
solo como XML firmado dentro del TAR.GZ. Los CUF quedan en
`siat_paquetes.pa_facturas` (JSON con `[{numero, cuf}]` por paquete), y es de
ahí de donde `debug_etapa7.php` y `debug_etapa9.php` toman las facturas que van
a anular y revertir.

---

## 2. Alteraciones hechas en el código

Todas sin commit: están en el working tree. `git status` las muestra.

### 2.1 `models/siatModel.php` (el grueso del trabajo)

| Cambio | Qué pasaba antes | Referencia |
|---|---|---|
| Transporte `use_curl` | `SoapClient` usaba el stream wrapper `https://` de PHP, que en XAMPP/Windows se colgaba contra el piloto (`Could not connect to host`). Con libcurl conecta sin problema | `models/siatModel.php:30` |
| Token por HTTP header | El SIN exige `apikey: TokenApi <token>`; el WSDL no declara SOAP header, así que va en `stream_context` | `models/siatModel.php:17-20` |
| Certificado reexportado | `MARTIN_YANA.p12` usa PKCS#12 PBE con SHA-1 y RC2-128, que OpenSSL 3.x movió al proveedor `legacy`. `openssl_pkcs12_read()` devolvía `false` y la firma nunca se generaba. Se reexportó a AES-256-CBC + SHA-256 | `config/siat.php:60-68` |
| Doble base64 eliminado | `archivo` es `xs:base64Binary` y `SoapClient` **ya** codifica en base64. El código hacía `base64_encode()` y luego SoapClient volvía a codificar: el SIN recibía doble base64 y rechazaba el paquete. Ahora se manda el binario crudo | `models/siatModel.php:1356` |
| Hash del TAR.GZ sobre el gzip | El `hashObject` que pide el SIN es `sha256` del **gzipped**, no del XML ni del base64 | `models/siatModel.php:1323` |
| `generarCUF()` corregido | El correlativo va dentro del CUF; se consumía mal y la factura salía con CUF que el SIN no conocía | `models/siatModel.php:693` |
| Validación de paquetes | Se神话 el resultado de `recepcionPaquete`: había que distinguir `901 PENDIENTE` de `908 VALIDADA` y reintentar | `models/siatModel.php:validarPaqueteContingencia` |
| `resolverActividadEconomica()` | Antes leía la actividad desde la config y a veces mandaba cadena vacía | `models/siatModel.php:2073` |
| `resolverCodigoProductoSIN()` | Resuelve el código SIN por palabra clave del nombre del medicamento, y **rechaza** categorías genéricas o sin código | `models/siatModel.php:2111` |
| Exclusión de clientes incompletos | El SIN observa con `1048` toda factura sin `numeroDocumento`; se filtran antes de emitir | en `ventaController.php` |
| Rotación de CUFD de evento | `rotarCufdEvento()` deja el CUFD vencido como `ev_cufd_evento` y pide uno nuevo | `models/siatModel.php:2556` |

Métodos nuevos relevantes para la operación diaria: `obtenerContextoCaja()`,
`obtenerCUIS()`, `obtenerCUFD()`, `cufdVigente()`, `registrarPuntoVenta()`,
`generarXML()`, `firmarXML()`, `armarPaqueteTargz()`,
`enviarPaqueteContingencia()`, `validarPaqueteContingencia()`,
`anularFactura()`, `registrarEventoSignificativo()`, `sincronizarCatalogos()`.

### 2.2 `config/siat.php`

| Constante | Valor | Por qué |
|---|---|---|
| `SIAT_MODALIDAD` | `1` | La modalidad 2 devuelve `917 EL PARAMETRO MODALIDAD ES INVALIDO` al pedir CUFD con este CUIS. El CUFD y el paquete tienen que ir en la misma modalidad, o el paquete se rechaza con `902/914 CUFD INVALIDO` |
| `SIAT_MAX_FACTURAS_CONTINGENCIA` | `500` | Máximo del SIN (`954`). Ver la advertencia de la sección 5: el piloto no aguanta 500 en la práctica |
| `SIAT_TIPO_PUNTO_VENTA` | `5` | Una farmacia opera en caja |
| `SIAT_CERT_P12_PATH` | `firma/MARTIN_HUAYHUA_YANA.p12` | Certificado reexportado |

### 2.3 Módulo de ventas

- `controllers/ventaController.php`: genera, firma, valida el XSD y registra la
  factura localmente. **No** manda sola al SIN: la emisión queda separada.
- `controllers/ventasHistorialController.php`, `models/ventasHistorialModel.php`,
  `views/content/ventasHistorialLista-view.php`,
  `views/script/ventasHistorialLista-view.js`: el historial distingue factura
  electrónica de nota de venta, y muestra número fiscal, CUF, CUFD, recepción y
  estado por separado.
- `cronjob/siat_validar.php`: revalida facturas `PENDIENTE_VALIDACION`.

---

### 2.4 `siat_certificacion.php`

Un cambio chico que habilitó todo lo demás: el bloque `Main` del final del
archivo quedó encerrado en `if (!defined('SIAT_CERT_LIB'))`. Antes, cualquier
script que hiciera `require` del archivo para reutilizar `facturaXml()`,
`generarCUF()`, `validarXsd()` o `armarPaqueteTargz()` ejecutaba además toda la
batería y moría en el `exit`. Con el guard, un orquestador declara la constante
antes del `require` y usa las funciones sin disparar la CLI.

```php
define('SIAT_CERT_LIB', true);
require_once __DIR__ . '/siat_certificacion.php';
```

Es la forma de no duplicar la construcción del XML firmado, que es la parte
delicada: cualquier copia se desincroniza del original en el primer cambio de
esquema.

---

## 3. Procedimiento de contingencia, paso a paso

Este es el orden obligatorio. Cambiarlo produce errores concretos del SIN.

1. **Evento significativo.** Registrar el corte con `codigoMotivo` (1–7).
2. **Esperar 240 s** a que el CUFD que estaba vigente venza. Es el mismo CUFD
   que va como `ev_cufd_evento` en el XML de cada factura.
3. **Pedir un CUFD nuevo** y registrarlo de inmediato como CUFD vigente.
4. **Emitir** las facturas con `codigoEmision = 2`, cada XML con el CUFD vencido
   como `ev_cufd_evento`.
5. **Armar el TAR.GZ** con todos los XML y su `sha256` del gzip.
6. **`recepcionPaqueteFactura`** → queda un código de recepción.
7. **`validarRecepcionPaquete`** hasta `908 VALIDADA` (puede empezar en
   `901 PENDIENTE`).

### Errores que aparecen si se viola el orden

| Código | Causa | Qué hacer |
|---|---|---|
| `914 CUFD INVALIDO` | Se rotó el CUFD antes de que venciera el de evento | Respetar los 240 s del paso 2 |
| `981 RANGO DE FECHAS DE EVENTO SIGNIFICATIVO INVALIDO` | El evento se cerró antes de que pasara el periodo mínimo | Esperar antes de cerrar |
| `917 MODALIDAD INVALIDO` | Se pidió CUFD con modalidad 2 | Usar `SIAT_MODALIDAD = 1` |
| `902/914` al validar el paquete | El sobre usa un CUFD distinto del que se usó al emitir | Sobre y XML con la misma modalidad |
| `984` | El CUFD vigente y el `cufdEvento` son iguales | Rotar primero, emitir después |
| `1048` (observación) | Factura sin `numeroDocumento` del cliente | Excluir clientes incompletos |
| `954` | Más de 500 facturas en el paquete | Bajar el tamaño |
| `906` + `981` al anular | La factura ya fue revertida | El piloto **no** admite volver a anular una factura revertida |

---

## 4. Resultados de las corridas

### 4.1 Anulación y reversión (etapas 7 y 9)

Cada factura emitida pasa por anulación y reversión. La verificación se hace
con `verificacionEstadoFactura`, que es la única fuente de verdad: el script
imprime el estado, y el parser del orquestador lo contrasta.

| Paquete | pv | Facturas | Emisión | Anulación | Reversión | Recepción |
|---|---|---|---|---|---|---|
| 50 | 0 | 15 | `908` | 15/15 `905` → `691 ANULADA` | 15/15 `907` → `690 VALIDA` | `16feb200-bd36-11f1-8693-d19b13b44352` |
| 51 | 0 | 50 | `908` | 50/50 `905` → `691` | 50/50 `907` → `690` | `293117cc-bd37-11f1-8693-d19b13b44352` |
| 55 | 0 | 100 | `908` | 100/100 `905` → `691` | 100/100 `907` → `690` | `9e4b17b8-bd5f-11f1-a998-7fc5f43c5e10` |
| 52 | 1 | 100 | `908` | n/c | n/c | `780736a4-bd39-11f1-8693-d19b13b44352` |
| 53 | 0 | 100 | `908` | n/c | n/c | `20a960ca-bd3a-11f1-8693-d19b13b44352` |
| 57 | 1 | 100 | `908` | 100/100 `905` → `691` | 100/100 `907` → `690` | `915283fe-bd60-11f1-a998-7fc5f43c5e10` |

**Total verificado con el ciclo completo (anular + revertir): 365 facturas.**
Paquetes 52 y 53 se emitieron y validaron, pero no se recorrieron con las etapas
7 y 9.

Las facturas quedan en `690 VALIDA`. No se pueden dejar en `ANULADA` porque el
piloto no admite re-anular lo revertido (`906` + `981`).

### 4.2 Etapa 2: sincronización de catálogos

Planilla de 36 pruebas: 18 catálogos × 2 puntos de venta, 50 repeticiones cada
una. Criterio de prueba correcta: la llamada no lanza `SoapFault`, `transaccion`
viene en `true`, y el nodo de lista propio del catálogo existe con al menos un
elemento. Para `sincronizarFechaHora` el criterio es que `fechaHora` venga con
valor.

| N° | Resultado esperado | Operación WSDL | Nodo de respuesta | Elementos | pv 1 | pv 0 |
|---|---|---|---|---|---|---|
| 1, 19 | LISTADO TOTAL DE ACTIVIDADES | `sincronizarActividades` | `RespuestaListaActividades.listaActividades` | 3 | 50/50 | 50/50 |
| 2, 20 | FECHA Y HORA ACTUAL | `sincronizarFechaHora` | `RespuestaFechaHora.fechaHora` | 1 | 50/50 | 50/50 |
| 3, 21 | ACTIVIDADES DOCUMENTO SECTOR | `sincronizarListaActividadesDocumentoSector` | `...listaActividadesDocumentoSector` | 14 | 50/50 | 50/50 |
| 4, 22 | LEYENDAS DE FACTURAS | `sincronizarListaLeyendasFactura` | `RespuestaListaParametricasLeyendas.listaLeyendas` | 12 | 50/50 | 50/50 |
| 5, 23 | MENSAJES DE SERVICIOS | `sincronizarListaMensajesServicios` | `RespuestaListaParametricas.listaCodigos` | 192 | 50/50 | 50/50 |
| 6, 24 | PRODUCTOS Y SERVICIOS | `sincronizarListaProductosServicios` | `RespuestaListaProductos.listaCodigos` | 1430 | 50/50 | 50/50 |
| 7, 25 | EVENTOS SIGNIFICATIVOS | `sincronizarParametricaEventosSignificativos` | `RespuestaListaParametricas.listaCodigos` | 7 | 50/50 | 50/50 |
| 8, 26 | MOTIVO DE ANULACIÓN | `sincronizarParametricaMotivoAnulacion` | `RespuestaListaParametricas.listaCodigos` | 4 | 50/50 | 50/50 |
| 9, 27 | PAÍSES | `sincronizarParametricaPaisOrigen` | `RespuestaListaParametricas.listaCodigos` | 211 | 50/50 | 50/50 |
| 10, 28 | TIPOS DE DOCUMENTO DE IDENTIDAD | `sincronizarParametricaTipoDocumentoIdentidad` | `RespuestaListaParametricas.listaCodigos` | 5 | 50/50 | 50/50 |
| 11, 29 | TIPOS DE DOCUMENTO SECTOR | `sincronizarParametricaTipoDocumentoSector` | `RespuestaListaParametricas.listaCodigos` | 51 | 50/50 | 50/50 |
| 12, 30 | TIPO EMISIÓN | `sincronizarParametricaTipoEmision` | `RespuestaListaParametricas.listaCodigos` | 4 | 50/50 | 50/50 |
| 13, 31 | TIPO HABITACIÓN | `sincronizarParametricaTipoHabitacion` | `RespuestaListaParametricas.listaCodigos` | 16 | 50/50 | 50/50 |
| 14, 32 | MÉTODO DE PAGO | `sincronizarParametricaTipoMetodoPago` | `RespuestaListaParametricas.listaCodigos` | 307 | 50/50 | 50/50 |
| 15, 33 | TIPOS DE MONEDA | `sincronizarParametricaTipoMoneda` | `RespuestaListaParametricas.listaCodigos` | 154 | 50/50 | 50/50 |
| 16, 34 | TIPOS DE PUNTO DE VENTA | `sincronizarParametricaTipoPuntoVenta` | `RespuestaListaParametricas.listaCodigos` | 6 | 50/50 | 50/50 |
| 17, 35 | TIPOS DE FACTURA | `sincronizarParametricaTiposFactura` | `RespuestaListaParametricas.listaCodigos` | 4 | 50/50 | 50/50 |
| 18, 36 | UNIDAD DE MEDIDA | `sincronizarParametricaUnidadMedida` | `RespuestaListaParametricas.listaCodigos` | 134 | 50/50 | 50/50 |

```
pruebas           : 36
pruebas al 100%   : 36
llamadas SOAP     : 1800
correctas         : 1800
PORCENTAJE GENERAL: 100.0%
duración          : 338.4 s
```

**Datos que quedaron guardados de esta sincronización:** `siat_actividades` con
3 filas, `siat_leyendas` con 12, `siat_productos` con 1 426 y
`siat_parametricas` con 155.

Sobre el catálogo de productos hay un detalle que conviene no leer mal: el SIN
devuelve **1 430 entradas** pero solo **1 426 códigos de producto distintos**
(algunos productos aparecen bajo más de una actividad). `siat_productos` tiene
exactamente 1 426 filas y no le falta ninguno de los remotos: la
sincronización está completa, el 1 430 es el conteo de entradas, no de productos.

**Detalle no obvio que apareció al mapear la planilla:** 14 de los 18 catálogos
responden en el mismo nodo `RespuestaListaParametricas.listaCodigos` con campos
`codigoClasificador` + `descripcion`. Los nombres de nodo que asumía el código
anterior (`listaProductos`, `listaUnidadesMedida`) **nunca existen**; por eso el
script reportaba "en PILOTO suele venir vacío" cuando en realidad venían con
1 430 productos.

### 4.3 Etapa 3: solicitud de CUIS

Operación `cuis` del servicio `FacturacionCodigos`
(`.../v2/FacturacionCodigos?wsdl`). La solicitud `SolicitudCuis` lleva
`codigoAmbiente`, `codigoModalidad`, `codigoSistema`, `nit`, `codigoSucursal`
y `codigoPuntoVenta`. 100 intentos por punto de venta.

| N° | Punto de venta | Esperadas | Correctas | % | CUIS devuelto | Vigencia |
|---|---|---|---|---|---|---|
| 1 | 1 | 100 | 100 | 100% | `5566168` | `2027-09-30T17:03:06.100-04:00` |
| 2 | 0 | 100 | 100 | 100% | `5B4D47E3` | `2027-08-09T20:39:25.649-04:00` |

```
llamadas SOAP        : 200
correctas            : 200 de 200
PORCENTAJE GENERAL   : 100.0%
duración             : 94.5 s
mensaje en las 200   : 980 EXISTE UN CUIS VIGENTE PARA LA SUCURSAL O PUNTO DE VENTA
transaccion=true     : 0 de 200
```

**Lo importante de esta etapa, y por qué el 100% no dice lo que parece:**

1. **La operación es idempotente y no ensucia el SIN.** Repetida la solicitud,
   el SIN devuelve el CUIS que ya existe, con `transaccion = false` y mensaje
   `980`. **No crea un punto de venta nuevo por llamada.** Por eso se pudieron
   correr 200 intentos sin generar 200 cajas basura. Comprobado antes con una
   sonda de 8 llamadas: los 8 intentos devolvieron el mismo código.

2. **El campo `cuis` que pide la planilla no existe en `SolicitudCuis`.** El
   WSDL no lo declara. Mandarlo no cambia nada: la sonda dio respuestas
   idénticas con y sin él, así que el loop no lo manda.

3. **La planilla no declara `resultadoEsperado` en esta etapa, y hay dos
   criterios distintos, ambos con resultado distinto:**
   - *estricto*: `transaccion == true`. Da **0%**, porque con un CUIS ya emitido
     el SIN siempre responde 980. Es el criterio para pedir un CUIS nuevo.
   - *laxo*: la respuesta trae `codigo` y `fechaVigencia`. Da **100%**.

   El 100% de arriba usa el laxo. Se eligió ese porque es el que explica la
   línea base de la propia planilla (pv 0: 94/100, pv 1: 35/100): el punto de
   venta 0 ya tenía CUIS desde el principio de la batería, y el 1 se registró a
   mitad de camino, a partir de ahí pasaron las 35 restantes. Medir solo uno de
   los dos y callar el otro daría un número engañoso.

### 4.4 Etapa 6: emisión en contingencia

Son 16 pruebas: **1 a 14** envían un paquete (`recepcionPaqueteFactura`) y **15
y 16** validan una recepción ya existente
(`validacionRecepcionPaqueteFactura`). Mapa:

| N° | Motivo | pv | `cantidadFacturas` | Envíos | Qué mide |
|---|---|---|---|---|---|
| 1 | 1 Corte del servicio de internet | 1 | 500 | 10 | envío con evento de motivo 1 |
| 2 | 1 | 0 | <500 | 10 | ídem en pv 0 |
| 3 | 2 Inaccesibilidad al web de la ADMIN | 1 | 500 | 10 | envío con evento de motivo 2 |
| 4 | 2 | 0 | <500 | 10 | ídem en pv 0 |
| 5 | 3 Ingreso a zonas sin internet | 1 | 500 | 10 | evento de motivo 3 |
| 6 | 3 | 0 | <500 | 10 | ídem en pv 0 |
| 7 | 4 Venta en lugares sin internet | 1 | 500 | 10 | evento de motivo 4 |
| 8 | 4 | 0 | <500 | 10 | ídem en pv 0 |
| 9 | 6 Virus informático o falla de software | 1 | 500 | 10 | evento de motivo 6 |
| 10 | 6 | 0 | <500 | 10 | ídem en pv 0 |
| 11 | 7 Cambio de infraestructura o falla de hardware | 1 | 500 | 10 | evento de motivo 7 |
| 12 | 7 | 0 | <500 | 10 | ídem en pv 0 |
| 13 | 5 Corte de suministro de energía eléctrica | 1 | 500 | 10 | evento de motivo 5 |
| 14 | 5 | 0 | <500 | 10 | ídem en pv 0 |
| 15 | no aplica | 1 | — | 70 | validar código de recepción |
| 16 | no aplica | 0 | — | 70 | ídem en pv 0 |

**Las 16 pruebas ya existían** en `PRUEBAS_EMISION` y `PRUEBAS_VALIDACION` de
`siat_certificacion.php`; el work consistió en repetirlas según la planilla.

**Criterio de prueba correcta:**

- *Envío*: el SIN devuelve `codigoRecepcion` no vacío. La planilla dice
  `codigoRecepcion = "no aplica"` justamente porque todavía no hay recepción.
- *Validación*: el SIN devuelve un `codigoEstado` real (`908 VALIDADA`,
  `901 PENDIENTE`, `989`, …). Se cuenta como fallo cuando no hay código de
  estado o viene `0`, porque eso es un fallo de la llamada, no del servicio.

**Cada intento es una sola llamada al SIN, sin reintentos internos.** A
diferencia de `siat_certificacion.php --prueba=N`, que reintenta 6 veces con
esperas de 20–60 s, el loop no reintenta: en una batería de certificación una
"prueba" es un envío, y reintentar por dentro multiplicaría las llamadas
reales y falsearía el conteo.

**Qué se guarda de cada intento:** una fila en `siat_paquetes` con evento,
cantidad, `sha256` y la respuesta. El TAR.GZ en base64 se guarda **solo si el
SIN devolvió código de recepción**; guardar los rechazos también serían ~180 KB
por intento × 140. Lo que sirve para auditar un rechazo es la traza, no el
binario.

**Costo real medido por intento:** ~30 s armando y firmando 500 facturas, más
~35 s de espera del SIN. Con 140 envíos, la batería completa es de unas **2,5
horas**. En un servidor más chico o con paquetes de 499, el armado baja a ~8 s,
pero el SIN sigue tardando lo mismo: el cuello de botella es el fisco, no el
XML.

---

## 5. Los paquetes de 500 facturas topan con un límite del piloto

No es un tope fijo, es **degradación por saturación acumulada**. La evidencia
completa, porque la conclusión cambia según cómo se lea:

| Paquete | n | Estado | Fecha |
|---|---|---|---|
| 24 | 500 | **validado** | 2026-09-30 17:50 |
| 26 | 500 | **validado** | 2026-09-30 18:07 |
| 27 | 499 | **validado** | 2026-09-30 18:08 |
| 29 | 499 | **validado** | 2026-09-30 18:10 |
| 31–35 | 499/500 | **validado** (5 seguidos) | 2026-09-30 18:14–18:19 |
| 44 | 500 | **validado** | 2026-09-30 19:22 |
| 25, 30, 36–40, 45, 46 | 500/499 | rechazado | desde 2026-09-30 18:06 |
| 54, 56, 58–61 | 500/499 | rechazado | 2026-10-01 02:09–02:55 |

O sea: 500 facturas **sí** validadas siete veces, todas el 30 de septiembre
entre las 17:50 y las 19:22. A partir de las 18:20 del 30 el piloto empezó a
rechazar de forma intercalada, y el 1 de octubre ya no acepta ninguno. El
síntoma siempre es el mismo:

```
0: Cafc no encontrado
java.util.concurrent.TimeoutException: Request timeout to
  sre-sfe-elec-recep-v2-rest/10.98.48.200:39421 after 45000 ms
```

Con 6 reintentos y esperas de 60 s, más una pausa de 150 s entre paquetes, no
se recupera. En paralelo los paquetes de **100 facturas pasaron sin una sola
falla** (paquetes 52, 53, 55 y 57) el 30 y el 1 de octubre.

**Conclusión operativa:** no es que 500 esté prohibido, es que encadenar
paquetes grandes agota el piloto por un tiempo largo. Para la planilla conviene
bajar la cantidad a 100 y dejar pausas amplias entre paquetes, o esperar a que
el piloto se recupere antes de volver a intentar 500.

**Consecuencia para la planilla:** `siat_certificacion.php` declara 500 para
pv 1 y 499 para pv 0 (`siat_certificacion.php:62-77`). Funcionó, pero al borde
y sin margen de reintento.

---

## 6. Cómo se rehace cada cosa

### 6.1 Listar la planilla y los paquetes

```bat
cd C:\xampp\htdocs\samfarm-backend
C:\xampp\php\php.exe siat_certificacion.php --listar
C:\xampp\php\php.exe siat_certificacion.php --reporte
```

### 6.2 Registrar los eventos significativos que falten

```bat
C:\xampp\php\php.exe siat_certificacion.php --pv=1
C:\xampp\php\php.exe siat_certificacion.php --pv=0
```

### 6.3 Emitir un paquete de contingencia

```bat
REM prueba 1 = motivo 1 en pv 1;  prueba 2 = motivo 1 en pv 0
C:\xampp\php\php.exe siat_certificacion.php --prueba=2 --facturas=100
C:\xampp\php\php.exe siat_certificacion.php --prueba=1 --facturas=100
```

### 6.4 Ensayo local sin gastar CUFD

```bat
C:\xampp\php\php.exe siat_certificacion.php --prueba=2 --facturas=10 --sin-enviar
```

### 6.5 Anular y revertir

```bat
C:\xampp\php\php.exe debug_etapa7.php --listar
C:\xampp\php\php.exe debug_etapa7.php --cuf=<CUF> --pv=0 --motivo=1
C:\xampp\php\php.exe debug_etapa9.php --cuf=<CUF> --pv=0
```

`debug_etapa9.php` **solo** sirve sobre facturas que estén en `691 ANULADA`.
Como el piloto no deja volver a anular lo revertido, el orden
anular → revertir es de una sola dirección.

### 6.6 Sincronizar catálogos desde la aplicación

```php
siatModel::sincronizarCatalogos($suId = 1);
```

### 6.7 Solicitar un CUIS

```bat
REM La operacion es idempotente: si ya existe, devuelve el mismo codigo con
REM mensaje 980 y no crea un punto de venta nuevo.
C:\xampp\php\php.exe debug_etapa1.php --pv=0
C:\xampp\php\php.exe debug_etapa1.php --pv=1
```

```php
siatModel::obtenerCUIS($suId = 1, $codigoSucursal = 0, $codigoPuntoVenta = $pv, $pvId = $pvId);
```

### 6.8 Los orquestadores de certificación (archivos temporales)

Los loops completos se armaron con scripts temporales fuera del proyecto, en
`%TEMP%\kilo\`, y se borraron al terminar. No quedan en el repositorio. Se
reconstruyen así:

**Loop de facturas (emisión → anulación → reversión):** lanza
`siat_certificacion.php --prueba=N --facturas=M` en subproceso, lee el `pa_id`
nuevo de `siat_paquetes`, y por cada CUF de `pa_facturas` invoca
`debug_etapa7.php --cuf=... --pv=N` y luego `debug_etapa9.php --cuf=... --pv=N`.
Valida cada paso contra `verificacionEstadoFactura`, no contra el texto impreso.
Parámetros: `--facturas=N`, `--pv=0|1`, `--reanudar-paquete=ID`.

**Loop de etapa 2:** itera los 18 catálogos × 2 puntos de venta, con
`--repetidas=N`. Arma la solicitud `SolicitudSincronizacion` con
`codigoAmbiente`, `codigoModalidad`, `codigoSistema`, `nit`, `cuis`,
`codigoSucursal=0`, `codigoPuntoVenta`; para productos agrega
`codigoActividad=4772100`. Lee el primer nodo de la respuesta, exige
`transaccion` en `true` y que la lista propia del catálogo tenga al menos un
elemento. El CUIS sale de `siat_configuracion` para pv 0 y de `puntos_venta`
para pv 1.

**Loop de etapa 3:** itera los puntos de venta 1 y 0, con `--repetidas=N`
(100 por defecto, 200 llamadas). Llama a `cuis` en `FacturacionCodigos` y mide
**dos** criterios: laxo (`codigo` + `fechaVigencia` presentes) y estricto
(`transaccion == true`). Reporta los dos porque dan 100% y 0% respectivamente.
Aplana `mensajesList` a mano, igual que en etapa 2, porque el SIN lo devuelve
como objeto suelto cuando hay un solo mensaje.

**Antes de correr cualquier loop contra el SIN, sondar.** Todas las operaciones
que crean datos (CUIS, CUFD, eventos) dejan rastro en el sistema del fisco y no
se pueden borrar. La sonda de la etapa 3 (8 llamadas) fue la que destapó que
`cuis` es idempotente y que mandar el campo `cuis` de la planilla no hace nada.

**Trampa de parsing que costó dos corridas:** los scripts imprimen
`codigoEstado   : 905` con varios espacios antes de los dos puntos, y en etapa 9
imprimen `reversion : codigoEstado=907 "..." transaccion=true`. Un regex que
exija un solo espacio, o que compare el valor contra `'905'`, marca todo como
fallo aunque el SIN haya contestado bien.

---

## 7. Estado y pendientes

### Resuelto

- Flujo electrónico en línea validado (`908`).
- Ciclo de contingencia completo, con anulación y reversión, en pv 0 y pv 1.
- Etapa 2 al 100% en los 36 ítems de la planilla.
- Etapa 3 (solicitud de CUIS) al 100% en los 2 ítems, 200 llamadas.
- 14 eventos significativos validados (motivos 1–7, en pv 0 y pv 1).
- Punto de venta 1 registrado en el SIN: CUIS `5566168`, vigente hasta
  `2027-09-30 17:03:06`.
- Cadena de `ventaController.php:694-740` replicada sobre una factura online
  real: XML válido contra el XSD, `numeroFactura` coincidente con el correlativo
  del CUF, firma `rsa-sha256` verificada y digest recalculado idéntico al
  declarado. Ver sección 10.3.
- `validacionRecepcionFactura` y el "estancamiento" de `pv_codigo = 0`: ambos
  resultaron ser no-problemas una vez medidos. Ver secciones 10.1 y 10.2.

### Bloqueado, requiere decisión

1. ~~**Sucursal 5 sin CUIS ni CUFD.**~~ **Resuelto el 3 de octubre:** con
   `sc_sucursal_codigo = 2` se obtuvo el CUIS `DE9C2012` y su CUFD, y la caja ya
   puede facturar. Ver 11.5. Queda la **sucursal 4 (San Roque)**, que sigue
   sin CUIS.
2. ~~**Actividad económica sin confirmar.**~~ **Resuelto el 3 de octubre:** el
   usuario eligió `4772100`, y una sonda real pasó de `904`/`1017` a `908
   VALIDADA`. Ver 11.1. Los rechazos `1002` de `fe_id=12` y `1017` de
   `fe_id=13` tienen esta misma causa; corregirlos sigue pendiente del punto 4.
3. **`medicamento.med_codigo_sin` en `NULL`.** No es inferible con seguridad
   desde el nombre. `resolverCodigoProductoSIN()` resuelve por palabra clave,
   pero para producción hace falta el código SIN real de cada medicamento.
4. **`fe_id=12` y `fe_id=13` observadas** (`1002+1048` y `1002+1017`).
   Corregirlas y reemitirlas requiere autorización.
5. **Migración de `puntos_venta.pv_cufd_anterior`.** Bloquea reutilizar
   `registrarEventoSignificativo()` desde `siat_certificacion.php`; el esquema no
   tiene la columna y la migración está prohibida sin permiso.
6. **Tamaño de paquete de la planilla.** Ver sección 5: 500 validó siete veces
   el 30 de septiembre, pero el piloto se saturó y desde el 1 de octubre los
   rechaza; 100 facturas no falla.
7. **Las 16 pruebas de la etapa 6: 8 superadas, 4 000 facturas aceptadas.**
   Los motivos 1 a 4 pasaron en pv 0 y pv 1 con `908`. Las pruebas 9 a 14 no se
   pudieron terminar por un **503 del piloto en
   `ServicioFacturacionCompraVenta`**, que es el servicio de recepción de
   paquetes. Detalle y evidencia en 11.2 y 11.3. Relanzar cuando el servicio
   vuelva, con `--prueba=N` de una en una.
8. **`sql/` sin versionar.** Hay tres archivos (`01_puntos_venta_siat.sql`,
   `02_pv_cufd_anterior.sql`, `03_siat_paquetes_archivo.sql`) que `AGENTS.md`
   prohíbe crear. Son de sesiones anteriores, no se tocaron. Hay que decidir si
   se borran o si la regla se relaja para el bootstrap del esquema.
9. **`siguienteNumero()` no bloquea.** Dos cajas facturando a la vez en la misma
   sucursal pueden pedir el mismo correlativo. En esta sesión produjo 2 184
   números repetidos y el SIN los rechazó con `1000`; en producción sería un
   rechazo de venta. Ver 11.4.

---

## 8. Verificaciones de integridad

Para confirmar que nada se rompió:

```sql
-- fe_id=11 debe seguir intacto, en CONTINGENCIA y con fa_id=631
SELECT fe_id, fa_id, fe_estado_siat, fe_tipo_emision, fe_hash_archivo
  FROM facturacion_electronica WHERE fe_id = 11;
-- esperado: 11 | 631 | CONTINGENCIA | 2 | 54873007737acfc9f7b563aa6cf40d148f235a4b31d6bedd9156f2a950a5055c

-- los paquetes de la certificación: todos en 'validado'
SELECT pa_id, pa_codigo_recepcion, pa_cantidad_facturas, pa_estado
  FROM siat_paquetes WHERE pa_estado = 'validado' ORDER BY pa_id;

-- las facturas de certificacion NO deben aparecer en facturacion_electronica.
-- Ojo: fe_paquete_id NO es NULL en general: las facturas del flujo en linea
-- (fe_id 12, 13, 14) si lo tienen, apuntando a los paquetes 13, 14 y 15. Lo
-- que tiene que dar 0 son las que apuntan a un paquete de certificacion.
SELECT COUNT(*)
  FROM facturacion_electronica f
  JOIN siat_paquetes p ON p.pa_id = f.fe_paquete_id
 WHERE p.pa_ev_codigo LIKE 'CERT-%';
-- esperado: 0

-- el catalogo de productos tiene que estar completo
SELECT COUNT(*) FROM siat_productos;            -- esperado: 1426
SELECT COUNT(*) FROM siat_actividades;          -- esperado: 3
SELECT COUNT(*) FROM siat_leyendas;             -- esperado: 12
SELECT COUNT(*) FROM siat_parametricas;         -- esperado: 155
```

---

## 9. Sesión del 1 y 2 de octubre de 2026: los intentos no avanzaban

### 9.1 Los dos bugs internos

**Zona horaria.** `php.ini` traía `date.timezone=Europe/Berlin` (UTC+2) y
MySQL corre en `America/La_Paz` (UTC−4). Desfase medido: **6.0 horas exactas**.
`config/siat.php` ahora hace `date_default_timezone_set('America/La_Paz')` en la
primera línea. Verificado: desfase 0 h.

**CUFD sin renovar.** `credenciales()` solo leía el CUFD guardado; nunca
comparaba su vigencia. Como la ventana de 24 h se guardaba 6 h más tarde de lo
que el SIN la concede, la app creía vigente un CUFD vencido y cada envío
posterior moría con **123 "CUFD FUERA DE TOLERANCIA"**. Ese error no se
recupera reintentando: lo que hace falta es un CUFD nuevo.

Corregido en `credenciales()` (no en los llamadores), con margen de 30 min
(`SIAT_CUFD_MARGEN_SEG`) y guardia estática para no recursar con
`renovarCufd()`. Sonda previa: pv 0 y pv 1 renovados, ambos vigentes 24 h.

### 9.2 Por qué el porcentaje del portal no avanzaba

Primero una corrección: **las facturas nunca fueron duplicadas**. Se verificó
sobre 64.026 facturas generadas y **63.989 CUF distintos**, solo 37
repeticiones. A simple vista dos CUF parecen iguales y están a un carácter de
diferencia; no era esa la causa.

La causa real era que **el loop reutilizaba siempre los mismos 14 eventos**.
`eventoPara()` (siat_certificacion.php:402) hace `ORDER BY ev_id DESC LIMIT 1`
sobre eventos ya validados y nunca crea uno. De ahí salían dos problemas:

1. **Fecha de emisión congelada.** En `armarFacturas()` la fecha del CUF es
   `ev_fecha_inicio + 60 s`, y ese campo viene del evento viejo. Todos los
   eventos eran del 2026-09-30, así que todas las facturas salían con
   `fechaEmision` del 30 de septiembre mientras el CUFD del sobre era del día.
2. **Nada nuevo de verdad.** Cada corrida reenviaba las mismas facturas colgadas
   del mismo `ev_codigo`. El paquete pasaba como `908 VALIDADA` porque
   estructuralmente estaba bien, pero el portal no veía un escenario distinto.

Evidencia: el último evento de la tabla era `ev_id=113`, del 2026-09-30 19:13.
Después de muchas corridas completas no se había registrado ni uno nuevo.

### 9.3 Opciones nuevas del loop

| Flag | Qué hace |
|---|---|
| `--eventos-frescos=pv:motivo,...` | Registra un evento nuevo por cada par antes de la primera ronda |
| `--evento-por-ronda` | Registra un evento nuevo en cada intento, en vez de colgar todo del mismo |
| `--variedad` | Rota el tamaño de paquete: 500 / 480 / 240 / 165 / 125 / 10 |

Con `--variedad` la rotación es determinista a propósito, para poder reproducir
una corrida. El máximo del SIN sigue siendo 500 (error 954).

`registrarEvento()` ya implementa el flujo correcto de tres pasos: esperar 240 s
a que el CUFD vigente tenga vida, usarlo como `cufdEvento`, pedir uno nuevo y
registrar de inmediato. Invertir el orden da 914, y mandar el mismo CUFD da 984.
Por eso cada evento nuevo cuesta unos 3 minutos de espera.

### 9.4 Sondas previas (regla: sonda antes del lote)

| Hora | Qué | Resultado |
|---|---|---|
| 19:57 | Renovación de CUFD pv 0 y pv 1 | OK, ambos vigentes hasta 2026-10-02 19:56 |
| 20:22 | 1 evento fresco pv 0 motivo 1 + 1 paquete de 499 | `CERT-0-M1-20261001201817`, recepción 9940600, `908 VALIDADA` |
| 20:21 | 2 eventos frescos pv 0 motivos 1 y 2 | recepciones 9940604 y 9940611, ambos OK |

Las tres sondas-single pasaron antes de lanzar cualquier lote.

---

## 10. Sesión del 2 y 3 de octubre de 2026: cierre de los pendientes

Todo lo de esta sección es **verificación de solo lectura**. No se emitió nada,
no se gastó CUFD y no se llamó al SIN, salvo donde se dice lo contrario.

### 10.1 `validacionRecepcionFactura`: no era un bug abierto

El nombre `validacionRecepcionFactura` solo aparece en el comentario que
explica por qué **no** se usa. Lo que se llama de verdad es
`validarRecepcionFactura($ticket, $suId)` (`models/siatModel.php:2348`), que por
dentro resuelve el CUF de la fila y delega en `verificarEstadoFactura($cuf, ...)`
(`models/siatModel.php:2309`), que sí existe en el WSDL. El comentario de
`models/siatModel.php:2286-2308` documenta el motivo: `validacionRecepcionFactura`
no está declarado en el WSDL de `ServicioFacturacionCompraVenta`, y pedirlo
hace que SoapClient responda `[Client] Function (...) is not a valid method`,
que es SoapFatal y tumba el proceso. **Nada que corregir.**

### 10.2 El "punto de venta estancado en 0" tampoco era un bug

La sospecha era que `resolverPuntoVenta($suId, null)` hace

```sql
WHERE su_id = :su_id AND pv_codigo = 0 LIMIT 1
```

sin `ORDER BY`, y que con más de una fila por sucursal devolvería una caja
distinta en cada llamada. Se medió y **no hay colisión**:

| `su_id` | `pv_codigo` | filas | `pv_id` |
|---|---|---|---|
| 1 | 0 / 1 / 2 | 1 / 1 / 1 | 1 / 2 / 3 |
| 4 | 0 / 1 | 1 / 1 | 4 / 5 |
| 5 | 0 | 1 | 6 |

Existe el índice único `ux_pv_sucursal_codigo (su_id, pv_codigo)`, así que el
`LIMIT 1` es determinista mientras ese índice esté. `pv_codigo = 0` se mantiene
**sin cambios**, como manda la decisión 1 de `AGENTS.md`.

Lo que sí se verificó, y conviene tener a mano: las tres cajas con
`pv_codigo = 0` (`pv_id` 1, 4 y 6) están **sin CUIS y sin CUFD**. La que resuelve
hoy es la de la sucursal 1, que sí hereda de `siat_configuracion`. La sucursal 5
sigue sin poder emitir: es el pendiente 1 de la sección 7, no un defecto de
resolución.

Detalle que confirma que el flujo respeta la sucursal y no al usuario: los ocho
usuarios con `usuarios.pv_id` cargado apuntan a `pv_id` 2 y 5, es decir a cajas
de `pv_codigo = 1`. El cobrador usa una caja, pero la emisión resuelve la caja
`pv_codigo = 0` de la sucursal. Es deliberado (`ventaController.php:613-619`).

### 10.3 La cadena de `ventaController.php` replicada sobre una factura real

Se reprodujeron los pasos 4 y 5 del controlador (`ventaController.php:694-740`)
sobre la factura online `fe_id=24` / `fa_id=656` / `ve_id=662`, en memoria, sin
escribir nada:

| Paso | Resultado |
|---|---|
| `obtenerDatosFacturaXML(662)` | `fa_numero_siat = 47981`, CUF presente, actividad `477000` |
| `obtenerDetalleFacturaXML(662)` | 1 ítem |
| `resolverActividadEconomica('477000')` | `477300` |
| `obtener_leyenda_siat_model('477300')` | Ley N° 453 de inocuidad, calidad y seguridad |
| `generarXML(...)` | 5192 bytes, con `SignatureValue` |
| `numeroFactura` del XML | `47981`, **igual** a `fa_numero_siat` |
| Validación contra el XSD oficial | **VALIDA** |
| `verify()` con la clave pública | **OK** |
| Digest declarado vs recalculado | `i0SbNGOpxqiAY3uenSXlC5XUC2Yi6qY58z/H0j0yQkc=`, **coinciden** |

Lo importante de la fila del `numeroFactura`: es el chequeo que evita el rechazo
`1002` del SIN. `numeroFacturaXml()` (`models/siatModel.php:878`) usa
`fa_numero_siat` y **nunca** `fa_numero_control`, porque esa segunda columna
guarda un código de texto tipo `F-1-20260930024528`. Que ambos coincidan
confirma que el correlativo que se consumió de `factura_secuencia` es el mismo
que quedó embebido en el CUF.

Algoritmos de la firma, todos los que pide el SIN:

- Canonicalization: `http://www.w3.org/TR/2001/REC-xml-c14n-20010315`
- SignatureMethod: `http://www.w3.org/2001/04/xmldsig-more#rsa-sha256`
- DigestMethod: `http://www.w3.org/2001/04/xmlenc#sha256`
- `Reference URI` vacío, es decir el documento entero
- Certificado: `MARTIN HUAYHUA YANA`, NIT `6006007013`, vigente hasta 2027-09-07

Aviso al reproducirlo: al verificar hay que cargar el documento con
`preserveWhiteSpace = true`. El documento declara C14N inclusivo, que sí
conserva los nodos de texto en blanco; si se reparsa sin preservarlos cambia el
`SignedInfo` y la verificación falla aunque la firma sea correcta. Por eso
`validateReference()` no sirve como comprobación: la librería no implementa el
transform `enveloped-signature`, hay que quitar el nodo `Signature` del documento
y recalcular el digest a mano (`debug_etapa4.php:420-449`).

### 10.4 Un código duplicado que se lleva por delante

`factura.fa_cuf` es una columna con índice `UNIQUE`. Las cuatro facturas 648 a
651 comparten `fa_cufd` y `fa_cufd_control`, y sus CUF difieren, así que el
índice aguantó. Pero nada impide que dos facturas con el mismo correlativo
terminen con el mismo CUF: el segundo `UPDATE` de `ventaController.php:678`
reventaría con un duplicado de clave en vez de dar un error legible. Queda como
observación, no se tocó.

### 10.5 Los 16 casos de la etapa 6: la sonda los frenó

La etapa 6 de la planilla es emisión **en contingencia** (14 envíos de paquete +
2 validaciones), tal como está mapeada en la sección 4.4, y no una emisión en
línea.

La batería se lansó autorizada, empezando por las sondas que impone `AGENTS.md`,
y **la tercera sonda la detuvo**, antes de gastar los 13 paquetes restantes.

| Sonda | Qué | Resultado |
|---|---|---|
| `--prueba=2 --facturas=6 --sin-enviar` | Armado local, sin SIN | 6/6 firmadas y válidas, TAR.GZ 5059 bytes. Correcto |
| `--prueba=15` | Validación real contra el SIN | `908 VALIDADA`. Correcto |
| `--prueba=2 --facturas=8` | Emisión real, pv 0 | **`904 OBSERVADA`, ocho `1017`** |

La tercera sonda es la que importa. El SIN recibió el paquete (recepción
`6642a5db-bec3-11f1-81c6-3d9d94b6b11d`), lo validó y lo devolvió observado con
ocho veces el mismo error:

```
1017: EL CODIGO DE PRODUCTO NO ESTA RELACIONADO A NINGUN ACTIVIDAD ECONOMICA
      DEL CONTRIBUYENTE
      No existe asociacion de: actividad 477300 - producto 1005611
```

`1005611` es Paracetamol, que el catálogo del SIN tiene bajo **4772100**, no bajo
**477300**. El lote se paró ahí: lanzar las 14 pruebas con esta configuración
hubiera producido 13 paquetes más rechazados con el mismo motivo, a cambio de
~2,5 h de llamadas al SIN.

**Causa raíz.** `config/siat.php:92` fija `SIAT_ACTIVIDAD_ECONOMICA = '477300'`,
y `resolverActividadEconomica()` (`models/siatModel.php:2078`) lo devuelve
siempre, ignorando lo que se le pase como argumento: la entrada no cambia el
resultado.

```
entrada NULL       -> 477300
entrada '477000'   -> 477300
entrada '4772100'  -> 477300
entrada '4772200'  -> 477300
entrada '477300'   -> 477300
```

Eso es intencionado en el paso 1 de la función, pero el valor elegido es el que
no funciona. El detalle que lo hace evidente:

| Actividad | Sector de documento | Productos en el catálogo |
|---|---|---|
| `4772100` | **1 = FCV** | 1382 |
| `4772200` | 1 = FCV | 26 |
| `477300` | **34 = FAC_SEG** | 18 |

La emisión usa sector de documento **1 (compra-venta)** en `ventaController.php:670`
y en `siat_certificacion.php:620`. `477300` pertenece al sector **34 (FAC_SEG)**,
que además solo tiene 18 productos y ningunapastelería, aceite medicinal ni
guantes: prácticamente ningún medicamento. `4772100` es del sector correcto y
tiene 1382 productos, incluido el 1005611.

El comentario de `config/siat.php:84` justifica `477300` porque "la farmacia
también vende jabones, pastas dentales y cremas faciales". Ese razonamiento es
correcto como negocio, pero `477300` no es la actividad inscrita bajo la que el
piloto tiene dado de alta los medicamentos, y el SIN valida esa asociación
producto-actividad, no el criterio del negocio.

**Esto es exactamente el pendiente 2 de la sección 7**, y sigue sin decisión del
usuario: `4772100` frente a `477300`. La diferencia práctica, con los números
del catálogo de este servidor:

- Con `4772100`: el 1005611 está registrado, y de los 1426 productos del
  catálogo 1382 están bajo esa actividad.
- Con `477300`: solo 18 productos son válidos, y el medicamento que usa la
  certificación (`med_id = 77207`, resuelto a 1005611 por la palabra clave
  PARACETAMOL) no está entre ellos.

No se cambió el valor. Es una decisión de negocio y el NIT, no del código.

**Efecto colateral que conviene mirar.** El 1002 de `fe_id=12` y el 1017 de
`fe_id=13` (pendiente 4 de la sección 7) probablemente tienen la misma causa que
esta sonda: se armaron con actividad `477300` y productos de `4772100`. Si se
confirma `4772100`, los dos se corrigen sin tocar el XML.

**Lo que sí quedó verificado de esta batería:** los 14 pares (pv, motivo) tienen
evento validado, `eventoPara()` los reutiliza sin registrar nuevos, y el CUFD
vencido de pv 1 se renueva solo en `credenciales()`
(`siat_certificacion.php:202-225`), así que el lote no se habría frenado por
credenciales.

Estado de la tabla al parar: 82 paquetes `validado`, 79 `rechazado`, 36
`enviado`, 9 `pendiente` (uno de ellos `pa_id=206`, la sonda `--sin-enviar`).

### 10.6 Duda menor que dejo anotada

`siat_certificacion.php:191` valida CUIS y CUFD con `$r['sc_cuis'] ?? $r['pv_cuis']`.
Para pv 0 trae las columnas `sc_*` y para pv 1 las `pv_*`, así que hoy funciona,
pero el `??` sobre claves que **nunca existen** en la otra rama es frágil: si
alguna vez faltara `sc_cuis` con `pv_cuis` presente, el `??` cruzaría de columna
en silencio. Un `isset` por rama explícito sería más claro. No se tocó.

---

## 11. Sesión del 3 de octubre de 2026: actividad 4772100 y cierre de la etapa 6

### 11.1 El cambio que desbloqueó la emisión

Por decisión del usuario, `SIAT_ACTIVIDAD_ECONOMICA` pasó de `477300` a
`4772100` (`config/siat.php:92`). Efecto medido sobre el mismo paquete:

| Actividad | Resultado real contra el SIN |
|---|---|
| `477300` | `904 OBSERVADA`, ocho `1017` (`pa_id=207`) |
| `4772100` | **`908 VALIDADA`** (`pa_id=209`) |

El `1017` desapareció. La asociación `4772100` - `1005611` (Paracetamol) existe en
el catálogo, y ahora también en el NIT. Queda registrado que los rechazos
`1002` de `fe_id=12` y `1017` de `fe_id=13` (pendiente 4 de la sección 7) tienen
esta misma causa, aunque corregirlos sigue necesitando autorización aparte.

### 11.2 Resultado de las 16 pruebas de la etapa 6

**8 de 16 superadas, 4 000 facturas aceptadas por el SIN.** Las 8 restantes no se
fallaron por el código: el piloto dejó de servir el servicio de recepción.

| Prueba | Motivo | pv | Paquete | Recepción | Resultado |
|---|---|---|---|---|---|
| 1 | 1 internet | 1 | `pa210` 500 | `37817917-bec6-...` | **`908`** |
| 2 | 1 internet | 0 | `pa211` 499 | `6b7c3473-bec6-...` | **`908`** |
| 3 | 2 web ADMIN | 1 | `pa212` 500 | `9a10085f-bec6-...` | **`908`** |
| 4 | 2 web ADMIN | 0 | `pa213` 499 | `f487364b-bec6-...` | **`908`** |
| 5 | 3 zonas sin internet | 1 | `pa214` 500 | `473fd127-bec7-...` | **`908`** |
| 6 | 3 zonas sin internet | 0 | `pa215` 499 | `73dba5a3-bec7-...` | **`908`** |
| 7 | 4 venta sin internet | 1 | `pa216` 500 | `a56cb4ff-bec7-...` | **`908`** |
| 8 | 4 venta sin internet | 0 | `pa218` 499 | `d80a5101-bec7-...` | **`908`** |
| 9 | 6 virus/software | 1 | `pa222` 500 | — | `Cafc no encontrado` |
| 10 | 6 virus/software | 0 | `pa223` 499 | — | `Cafc no encontrado` |
| 11 | 7 infraestructura | 1 | `pa224` 500 | — | `Cafc no encontrado` |
| 12 | 7 infraestructura | 0 | `pa226` 499 | — | `Cafc no encontrado` |
| 13 | 5 energía | 1 | — | — | no alcanzado |
| 14 | 5 energía | 0 | — | — | no alcanzado |
| 15 | solo validación | 1 | — | `301ea522-...` | **`908`** (sonda previa) |
| 16 | solo validación | 0 | — | — | no alcanzado |

Motivos 1 a 4 cubiertos en ambos puntos de venta, con 500 y 499 facturas. Motivo 6
cubierto solo en el intento previo. Motivos 5 y 7 pendientes.

### 11.3 Por qué 9 a 14 no se pudieron terminar: el 503 del piloto

A partir de la prueba 9 todas las recepciones devolvieron `0: Cafc no
encontrado`. Como la bitácora ya sospechaba saturación por tamaño de paquete
(sección 5), se bajó a 100 y luego a 20 facturas, y el rechazo siguió igual:

```
pa228   500 facturas   0: Cafc no encontrado
pa229   100 facturas   0: Cafc no encontrado
pa230    20 facturas   0: Cafc no encontrado
```

El tamaño no era la causa. Midiendo los tres WSDL del piloto por separado:

| Servicio | Estado | Bytes | Latencia |
|---|---|---|---|
| `ServicioFacturacionCodigos` | **200** | 16 980 | 273 ms |
| `ServicioFacturacionOperaciones` | **200** | 19 500 | 93 ms |
| `ServicioFacturacionCompraVenta` | **503** | — | 62 ms |

`FacturacionCompraVenta` es exactamente el servicio que atiende
`recepcionPaqueteFactura`. Los otros dos responden bien, y por eso `obtenerCUFD`
siguió funcionando durante la incidencia: se renewed el CUFD de pv 1 y se le
pidieron CUIS y CUFD a la sucursal 5 sin problema. Seis sondeos seguidos
espaciados 20 s dieron 503 en los seis.

Es una caída del servidor del piloto, no un defecto del módulo. Cuando el
servicio vuelva, las pruebas 9 a 14 se relanzan con `--prueba=N` de una en una.

**Aviso operativo:** `--pruebas=9,10,...` ignora `--facturas`
(`siat_certificacion.php:937` pasa el tamaño fijo de `PRUEBAS_EMISION`). Para
paquetes chicos hay que usar `--prueba=N --facturas=N`, una por vez.

### 11.4 Dos procesos a la vez: 2 184 correlativos duplicados

Error propio, dos veces. Al terminar el primer `--todo` se lanzó un segundo
proceso sin comprobar que el primero seguía vivo, y luego se repitió con las
pruebas 11 a 14. Los dos consumidores de secuencia generaron los mismos
números de factura:

```
numeros usados en total: 3711
numeros repetidos entre paquetes: 2184
```

El SIN los rechazó con `1000 EL CUF ENVIADO YA EXISTE EN LA BASE DE DATOS DEL
SIN` (`pa217`, `pa219`). El daño quedó acotado porque en cada par el paquete del
primer proceso se validó antes de que el segundo llegara al mismo correlativo:
las pruebas 7 y 8 cuentan como superadas gracias a `pa216` y `pa218`. No hace
falta reemitir nada; los duplicados están rechazados y no se reintentan solos.

Lo que sí hay que mirar: `siguienteNumero()` no tiene bloqueo. Dos cajas
facturando a la vez en la misma sucursal pueden pedir el mismo correlativo, y en
producción eso es un rechazo en la venta, no un `1000` inocuo de un paquete de
certificación.

### 11.5 Sucursal 5 registrada

`sc_sucursal_codigo = 2` ya estaba en la base desde antes (sección 10.2). Lo que
faltaban eran credenciales reales: seguían los placeholders `CUIS_PRUEBA_005` y
`CUFD_PRUEBA_005`. Pedidos al SIN con `codigoSucursal = 2` y
`codigoPuntoVenta = 0`:

| Dato | Valor |
|---|---|
| CUIS | `DE9C2012`, vigente hasta `2027-08-09 20:56:37` |
| CUFD | vigente hasta `2026-10-03 21:54:49` |
| `estadoCajaParaFacturacion` | **listo = SI** |

El punto de venta 0 **no se registra** por `registroPuntoVenta`: el propio
comentario de `models/siatModel.php:397-399` lo explica, mandar
`codigoTipoPuntoVenta` equivocado hace que el SIN responda 947. La sucursal
funciona con su CUIS y su CUFD, como la sucursal 1.

Estado de las tres sucursales tras el registro:

| `su_id` | `sc_sucursal_codigo` | ¿Puede facturar? |
|---|---|---|
| 1 | 0 | sí |
| 4 | 1 | no, sin CUIS: hay que registrar San Roque en el SIN |
| 5 | **2** | **sí** |

Pendiente 1 de la sección 7: resuelto para la sucursal 5.

### 11.6 Integridad tras la sesión

- `fe_id = 11` sigue en `CONTINGENCIA` con el hash
  `54873007737acfc9f7b563aa6cf40d148f235a4b31d6bedd9156f2a950a5055c`. **Intacto.**
- `pv_codigo = 0` en las tres cajas (`pv_id` 1, 4 y 6). **Sin cambios.**
- No se registró ningún evento nuevo: `siat_eventos` sigue en 132 filas, la
  última es `EVT-20261002181745-1-1`. Los 14 pares (pv, motivo) que pide la
  batería existen y se reutilizaron.
- No se tocó `facturacion_electronica` en emisión en línea.

### 11.7 Lo que queda

| Qué | Por qué |
|---|---|
| Pruebas 9 a 14 | El piloto devuelve 503 en `ServicioFacturacionCompraVenta`. Relanzar cuando vuelva |
| Prueba 16 | No se alcanzó; la 15 ya había dado `908` |
| Sucursal 4 | Sin CUIS. Es el único bloqueo de emisión que queda |
| `medicamento.med_codigo_sin` en `NULL` | El usuario lo va a cargar a mano |
| `sql/` sin versionar | Ver pendiente 8 de la sección 7 |
| Bloqueo de `siguienteNumero()` | Ver 11.4 |

## 12. Sesión del 3 de octubre de 2026: QR fiscal y anulación en línea

Trabajo de interfaz. **No se creó ni se anuló ninguna factura real**: las
sondas de este apartado se detienen antes de la llamada SOAP, a propósito.

### 12.1 El QR fiscal se arma, no se guarda

`fe_qr` no se usa. `siatModel::urlQR()` arma la URL del portal del SIN a
partir de `(nit, cuf, numero)`, que es determinista: la misma factura da
siempre el mismo QR, seis meses después o seis minutos después. Por eso una
reimpresión de una factura antigua no depende de que la imagen se haya
guardado el día de la validación, y por eso no hace falta columna nueva.

El PNG se genera en el momento con `siatModel::generarQR()` y viaja al
navegador en base64 pelado. El prefijo `data:` lo pone el JS.

### 12.2 Un bug de etiqueta de estado, encontrado de paso

`detalle_venta_controller` tenía su propio `if/elseif` para traducir
`fe_estado_siat` a texto. Caía en "RECHAZADA" todo lo que no fuera `908`, y eso
es falso para dos casos:

- `902`, que es rechazo real pero merece su propia etiqueta, y
- `ANULADA`, que es una anulación hecha a propósito y no un rechazo.

Las reglas se movieron a `siatModel::etiquetaEstadoFactura()`, que ya existía.
Un estado desconocido ahora se muestra tal cual (`904`, `905`) con una clase
neutra (`bgry`) en vez de inventarle un significado.

### 12.3 Las tres barreras de la anulación

La anulación es lo único de este módulo que escribe en el SIN y no tiene vuelta
atrás, así que no se expone con un solo chequeo:

1. **Interfaz.** El botón solo aparece si
   `siatModel::estadoAnulacionFactura()` dice `anulable`. Si no, se muestra el
   motivo del bloqueo, y ese texto lo redacta el modelo.
2. **Confirmación.** Modal aparte, con el CUF a la vista, el motivo en un
   `select` armado desde `siat_parametricas`, y un casillero donde hay que
   escribir `ANULAR`.
3. **Servidor.** `anular_factura_controller()` repite el chequeo de rol
   (`rol_smp == 1`) y el modelo vuelve a validar estado y motivo antes de
   llamar al SIN.

La barrera 3 no es redundante: 1 y 2 se pueden saltar desde la consola del
navegador. Lo que no se puede saltar es lo que corre en el servidor.

El chequeo de rol está en dos capas a propósito: en el ajax y en el controller.
Una sola bastaría, pero dejarla en un solo archivo hace fácil que un `require`
nuevo la esquive.

### 12.4 El orden de las validaciones importa

`anularFacturaConControl()` primero pregunta si la factura es anulable y
después valida el motivo. Al revés se desperdiciaría una llamada SOAP en un
error que el modelo ya podía resolver en local.

### 12.5 Comprobado sin tocar el SIN

Las sondas se corrieron con facturas que no pueden anularse, para que el
código cortara antes del SOAP:

| Rol | `fa_id` | Estado | Resultado |
|---|---|---|---|
| 2 (gerente) | 651 | `902` | `Acceso denegado`, antes de tocar el modelo |
| 1 (admin) | 651 | `902` | `El SIN rechazo esta factura. No hay nada que anular.` |
| 1 (admin) | 0 | — | `Factura no valida` |
| 1 (admin) | 632 | `VALIDADO` | `Motivo 99 no valido. Solo se aceptan: 1, 2.` |

La cuarta fila es la importante: `fa_id = 632` **sí** es anulable, y aun así la
petición se detuvo sin llamar al SIN, porque el motivo se valida antes. Ninguna
de las cuatro llegó a `anulacionFactura()`.

### 12.6 Solo dos motivos, y es a propósito

`siat_parametricas` todavía no tiene filas `MOTIVO_ANULACION`, así que
`motivosAnulacion()` devuelve el catálogo corto de dos códigos: `1 Error en la
factura` y `2 Devolucion de mercaderia`. Son los dos únicos probados contra el
piloto (1 dio `905` en la etapa 7; 2 es el que usa `devolucionController`).
Mandar un código sin verificar devuelve `932` y la anulación no se hace, así
que no se amplía el catálogo a ciegas.

### 12.7 Hallazgo colateral: 8 facturas en `902`

Al correr la sonda aparecieron **8 filas en `902`**. Por el diagnóstico de la
sección 11.1 casi con seguridad es la actividad económica anterior (`477300`),
y por lo tanto emisiones anteriores al cambio a `4772100`; 11.1 ya dejó
anotado que los rechazos `1002` y `1017` tienen esa misma causa. **No se
tocaron**: corregirlas requiere autorización aparte, igual que en 11.1.

Estado actual de las facturas con CUF: 8 en `902`, 4 en `CONTINGENCIA`, 2 en
`RECHAZADA`, 7 sin fila en `facturacion_electronica`, 1 `VALIDADO`, 1
`OBSERVADO`, 1 `PENDIENTE_VALIDACION`, 1 `SIN_ENVIAR`. La única `VALIDADO`
(`fa_id = 632`) es la única con el botón de anular habilitado.

### 12.8 Archivos tocados

| Archivo | Qué |
|---|---|
| `models/siatModel.php` | `etiquetaEstadoFactura()`, `estadoAnulacionFactura()`, `motivosAnulacion()`, `anularFactura()`, `anularFacturaConControl()`, `urlQR()`, `generarQR()` |
| `controllers/ventasHistorialController.php` | `anular_factura_controller()`, bloque `siat.anulacion` en el detalle |
| `ajax/ventasHistorialAjax.php` | ruta `anular_factura` |
| `views/content/ventasHistorialLista-view.php` | bloque de anulación y modal de confirmación |
| `views/script/ventasHistorialLista-view.js` | módulo `anulacion`, QR, reimpresión |

Ningún archivo `.sql`, ningún `ALTER`, ningún `CREATE`. Las sondas quedaron en
`%TEMP%\kilo` y no forman parte del repo.

## 13. Segunda vuelta: una sola reimpresión y sin códigos internos

Corrección de tres cosas que quedaron mal en la sección 12, reportadas por el
usuario con el modal ya funcionando.

### 13.1 Había dos reimpresiones, y la vieja era la que estaba rota

La tabla ya traía un botón **Imprimir** por fila desde antes del módulo SIAT.
La sección 12 agregó una segunda reimpresión dentro del modal de detalle.

Lo importante no es que sobrara una, sino **cuál** de las dos funcionaba:

| | Qué generaba | Resultado en una factura electrónica |
|---|---|---|
| Modal (nueva, sección 12) | Ventana con el QR de la pantalla | Correcta, pero solo sirve desde el detalle |
| Tabla (vieja) | `generar_pdf_nota_venta_rep()`, copia del PDF de venta anterior al módulo SIAT | **Mal**: salía rotulada `NOTA DE VENTA`, sin CUF, sin leyenda y sin QR |

La reimpresión de la tabla **nunca estuvo rota**: lo que estaba roto era el
generador que esa función usaba. `ventaModel::generar_pdf_factura_historial_model()`
ya existía y sí hacía el `LEFT JOIN` con `factura` para recuperar los datos
fiscales. El controller simplemente no lo llamaba: se había escrito una copia
en su lugar, y la copia se quedó congelada en el pasado.

Lo que se hizo:

- `generar_pdf_nota_controller()` delega en `generar_pdf_factura_historial_model()`
  y se borró el método privado duplicado (162 líneas).
- Se borró `imprimirQr()` del JS y el botón del modal.
- El botón de la tabla pasó de `reimprimirNota` a `reimprimirComprobante`, y
  el archivo que se abre se llama `Comprobante_<ve_id>.pdf` en vez de
  `Nota_Venta_<ve_id>.pdf`: en una factura electrónica, "nota de venta" en el
  nombre del archivo es mentira.

### 13.2 Comprobado sobre el PDF real, no sobre el código

FPDF comprime los streams, así que se inflaron para leer el texto:

| Caso | Título | CUF | CUFD | CUIS | QR | Leyenda |
|---|---|---|---|---|---|---|
| Factura (`ve_id=664`) | `FACTURA` | sí | **no** | **no** | 1 imagen | sí |
| Nota de venta (`ve_id=663`) | `NOTA DE VENTA` | no | **no** | **no** | 0 imágenes | no |

### 13.3 El CUF se queda en el comprobante, el CUFD sale

En el comprobante impreso se quitó el **CUFD** y se dejó el **CUF**, a
propósito. La diferencia:

- El **CUF** es el identificador único del documento ante el fisco. Sin él
  impreso el comprobante no sirve como documento fiscal, y además viaja dentro
  del QR, así que se lee con el celular sin escribir 64 caracteres.
- El **CUFD** es un identificador interno entre la caja y el SIN. El cliente no
  puede verificarlo con nada, y eran 16 caracteres empujando el rollo hacia
  abajo.

El dato no se pierde: sigue en `factura.fa_cufd` y en los logs. Se quitó la
llave `cufd` de los dos arrays `$siat` para que no quede un valor muerto que
alguien reimprima por costumbre.

### 13.4 El modal ya no manda códigos al navegador

Del bloque `siat` que `detalle_venta_controller` devuelve se sacaron `cuf`,
`cufd`, `codigo_recepcion` y `fecha_envio`. Comprobado sobre el JSON real:

```
es_factura_electronica  anulacion  estado_clase  estado_etiqueta
fa_id  numero_control  numero_factura  qr_img  qr_url

cufd -> 0 apariciones    cuis -> 0    codigo_recepcion -> 0    fecha_envio -> 0
```

El CUF aparece una sola vez en todo el JSON: dentro de `qr_url`, que es
exactamente su propósito, porque es lo que lee la cámara del celular.

En el modal quedan solo **Estado** y **Número de Factura**, el QR y el bloque de
anulación. El modal de anulación también mostraba el CUF; ahora muestra
`Factura N° 51996`, que es lo que el usuario puede comparar con el papel.

### 13.5 Las alertas ya no sueltan siglas ni códigos

Este era el punto 3 del reporte y estaba en
`siatModel::estadoCajaParaFacturacion()`. El peor caso era este:

```
antes: "El CUIS DE9C2012 vencio el 2026-08-09 20:56:37. Hay que pedir uno nuevo."
ahora: "Esta caja tiene su habilitancio vencida. Avise al administrador para que la renueve."
```

El método ahora devuelve dos textos en vez de uno:

| Clave | Quién la lee | Contenido |
|---|---|---|
| `motivo` | El cajero, en un cartel | Sin siglas ni códigos. Dice qué hacer. |
| `diagnostico` | El log del cron y `error_log` | Con el CUIS, el CUFD y el `pv_id` puestos |

`cronjob/siat_validar.php` pasó a imprimir `diagnostico`: un log sin códigos
no sirve para saber qué credencial pedir.

Verificado sobre las tres sucursales: `su_id=4` es la única que no puede
facturar, y su texto de cajero no contiene ninguna sigla, mientras que el
diagnóstico conserva `pv_id=4` y qué registrar.

### 13.6 Archivos tocados en esta vuelta

| Archivo | Qué |
|---|---|
| `controllers/ventasHistorialController.php` | `generar_pdf_nota_controller()` delega; −162 líneas duplicadas; `siat` sin códigos |
| `views/content/ventasHistorialLista-view.php` | −botón reimprimir, −4 filas de datos fiscales, modal de anulación sin CUF |
| `views/script/ventasHistorialLista-view.js` | −`imprimirQr()`; `reimprimirComprobante`; ya no lee campos que no llegan |
| `models/ventaModel.php` | −CUFD del comprobante y de los dos arrays `$siat` |
| `models/siatModel.php` | `estadoCajaParaFacturacion()` devuelve `motivo` + `diagnostico` |
| `controllers/ventaController.php` | Mensajes de caja sin siglas ni `pv_id`; el detalle va a `error_log` |
| `cronjob/siat_validar.php` | Imprime `diagnostico` |

Ninguna factura creada ni anulada. Las cuatro barreras de la sección 12.5 se
volvieron a correr y dan el mismo resultado.

## 14. La reimpresión no abría: `Swal` venía de un CDN

### 14.1 El diagnóstico

El usuario reportó que la reimpresión no generaba el comprobante, con esta
traza:

```
Reimprimiendo comprobante: 664
Uncaught (in promise) ReferenceError: Swal is not defined
    at Object.comprobante (ventasHistorialLista-view.js:225:13)
```

La causa no estaba en la reimpresión: **no había CDN**. `Swal` se carga en
`views/inc/head.php:35`:

```html
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

En una red sin salida a internet ese script no carga, `window.Swal` no existe, y
la **primera** llamada a `Swal.fire()` —que era la línea 2 de la función—
reventaba. El `await` del `fetch` ya había terminado, así que el backend
trabajaba bien e igual no se abría nada.

En el mismo log aparecía `ERR_CONNECTION_TIMED_OUT` para
`views/assets/img/logo_empresa_b3a986313260e6c9_1784391084.png`, que apunta a
`10.10.31.5`: el mismo symptoms de red, en otro recurso.

### 14.2 El arreglo

`views/script/ventasHistorialLista-view.js` declara su propio `Swal` al
principio del módulo:

```js
const Swal = window.Swal || (sustituto);
```

Si el CDN carga, es el SweetAlert de siempre y no cambia nada. Si no carga, el
sustituto cumple lo mismo de forma básica: un overlay propio para los mensajes y
nada para `showLoading`. Se declara con ese nombre a propósito, para que las 15
llamadas existentes del archivo no cambiaren.

El sustituto distingue las dos formas que usa el archivo: `fire(titulo, texto,
icono)` y `fire({title, text, icon})`. Y trata aparte la llamada de espera, la
que lleva `didOpen`: esa no muestra nada, porque si se mostrara el mensaje de
"Generando..." se quedaría pegado en pantalla detrás del resultado.

### 14.3 Un segundo defecto en el mismo camino

`window.abrirPDFDesdeBase64()` (`views/inc/script.php`) abría el PDF con
`window.open(url, '_blank')` **después** de un `await`. El navegador puede
bloquear ese popup porque la activación del usuario ya caducó, y en ese caso
`window.open` devuelve `null`: la función devolvía `true` como si nada y el
usuario se quedaba creyendo que la reimpresión no funcionaba, sin ningún aviso.

Ahora revisa el `null`, libera la URL temporal y avisa. Y su propio manejador de
error pasó a comprobar `typeof Swal !== 'undefined'` antes de llamar a
`Swal.fire`: si el CDN no cargó, ese manejador se caía justo cuando debía
avisar.

Este archivo es compartido por toda la aplicación, así que el cambio beneficia a
todas las vistas que imprimen PDF, no solo a esta.

### 14.4 Comprobado

Con el archivo cargado en Node, `window.Swal` ausente y un `fetch` simulado:

```
window.Swal presente ....... undefined (ausente a proposito)
llego a abrirPDFDesdeBase64 SI
  base64 ................. JVBERi0xLjM=
  nombre ................. Comprobante_664.pdf
detalle abierto ........... SI
```

Y del lado del servidor, corriendo el controlador como lo llama el navegador:

| `ve_id` | `success` | Cabecera | Bytes |
|---|---|---|---|
| 664 (factura) | `true` | `%PDF-1.3` | 4 982 |
| 663 (nota de venta) | `true` | `%PDF-1.3` | 2 242 |

### 14.5 Debug fuera

Se quitaron los 13 `console.log` / `console.error` del archivo: los que
imprimían cada petición, cada respuesta, la apertura y el cierre de cada modal, y
los errores de cada `catch`.

Una consecuencia útil: con `Swal` inexistente, el `catch` del detalle **reventaba
él mismo** al intentar avisar, así que el error real de la vista quedaba
escondido detrás de un `ReferenceError` que no decía nada. Con el sustituto, un
fallo del detalle se muestra como mensaje en vez de desaparecer.

Nota sobre el `Modal cerrado: modalDetalleVenta` del log del usuario: con el
`catch` reventando en `Swal.fire` **antes** de llegar a `utils.cerrar()`, esa
línea no podía venir del `catch`. Solo quedaban el botón **Cerrar** del modal o
el aspa, o sea la acción del usuario. No es un defecto, pero queda anotado para
que no se malinterprete como un error al leer el log.

## 15. El CUF también sale del comprobante

La sección 13.3 lo dejó impreso, con el argumento de que es el identificador
del documento ante el fisco. El usuario lo corrigió: **el CUF tampoco debe ser
visible**, ni para el cliente ni para el usuario interno.

### 15.1 Por qué no se pierde nada

El CUF viaja **dentro del QR**. Sacarlo del papel no impide verificar la
factura: lo que cambia es que deja de estar escrito en un idioma que el
cliente no usa. Quien lo necesite para una conciliación lo tiene en
`factura.fa_cuf`, en el XML firmado y en los logs.

Verificado sobre el PDF real, inflando los streams:

| | Antes | Ahora |
|---|---|---|
| `CUF:` | impreso en 2 líneas de 32 caracteres | **no aparece** |
| `CUFD:` | impreso | **no aparece** |
| `CUIS` | nunca se imprimió | **no aparece** |
| Algún hexadecimal de 16+ caracteres | el CUF entero | **ninguno** |
| QR | sí | sí |
| Leyenda Ley 453 | sí | sí |
| `N Factura` | sí | sí |
| «Escanee para verificar…» | sí | sí |

### 15.2 El alto del rollo era un número que mentía

Al sacar las líneas del CUF y del CUFD sobraba espacio, y la primera idea era
bajar el alto fijo de la factura de 150 a 136 mm. Al medir resultaba que el
número estaba **pegado al que ya no correspondía**, y sobre todo que era un
dato falso: los 150 mm se calcularon cuando el bloque fiscal incluía esos
identificadores, y no se recalculaban si la leyenda daba una línea de más.

Con `SetAutoPageBreak(false)` eso no es un detalle cosmético: el contenido que
excede el alto **se dibuja fuera de la página**. En una impresora térmica el
cliente se lleva el papel sin el pie, y el papel sin pie no sirve para
reclamar nada.

Ahora el alto se mide:

| Parte | Alto |
|---|---|
| Base de la nota de venta | 95 mm |
| Separador | 4 mm |
| Leyenda | 3.2 mm × líneas reales |
| QR | 30 mm |
| «Escanee para verificar…» | 3.5 mm |
| Renglón de anulación | 4 mm, solo si está anulada |

Las líneas de la leyenda se cuentan con `GetStringWidth()` de FPDF, con la misma
fuente con la que se dibuja, sobre el ancho real que recibe `MultiCell`
(80 mm de rollo menos 5 mm de margen a cada lado).

Probado forzando leyendas de distinto largo:

| Leyenda | Alto de página | Resultado |
|---|---|---|
| Corta (1 línea) | 144.9 mm | cabe entero |
| La real de la base | 144.9 mm | cabe entero |
| Larga deliberada (4× el texto) | **154.5 mm** | cabe entero |

La larga crece la página 9.6 mm, que es justo lo que ocupa de más. Antes el
alto habría seguido en 150 mm con el contenido desbordando.

### 15.3 Limpieza de paso

- `$alto_siat` estaba asignado y **nunca se usaba**: el alto salía de
  `$altura_base`. Su comentario además afirmaba que reservaba el alto de la
  leyenda y el QR, lo cual era falso. Se eliminó.
- `$d['med_nombre']` en el bucle de productos no tenía guarda. Con una consulta
  distinta a las dos que alimentan el método, eso es un notice y el nombre del
  producto en blanco en el papel del cliente. Se le puso `?? 'Producto'`, igual
  que el cálculo de altura más arriba en el mismo archivo.

### 15.4 Nota de método

En 13.3 se respetó la lectura de que el CUF sí debía imprimirse, por ser el
identificador fiscal. Fue un criterio técnico propio sobre una decisión de
interfaz, y la decisión es del usuario: cuando la corrigió, se cambió sin
discutir. Lo que se dejó por escrito acá es el porqué de que quitarlo sea
seguro (va en el QR), no una defensa de la posición anterior.

Ningún archivo `.sql`. Ninguna factura creada ni anulada.
