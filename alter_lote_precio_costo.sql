-- ============================================================
-- Script: agregar columna lm_precio_costo a lote_medicamento
-- Propósito: soportar la lógica de "Costo Lista" y "Precio Costo" por caja
--            aplicada en el módulo de lote (editar lote), ingreso masivo,
--            compraOrden y demás módulos que manejan precios por caja.
--
-- LÓGICA:
--   costo_lista  = precio catálogo del proveedor por CAJA
--   precio_costo = precio efectivamente pagado por CAJA (con descuento)
--   precio_compra (BD) = precio_costo / unidades_por_caja   (unitario)
--   precio_venta (BD) = derivado de precio_compra + margen_unitario
--   precio_min_u (BD) = (precio_costo / unidades_por_caja) * (1 + margen_u/100)
--   precio_min_c (BD) = precio_costo * (1 + margen_c/100)
-- ============================================================

ALTER TABLE `lote_medicamento`
  ADD COLUMN `lm_precio_costo` DECIMAL(12,2) DEFAULT NULL
  COMMENT 'Precio costo por CAJA (precio efectivamente pagado con descuento)';

-- (Opcional) Si la columna ya existe, este script no fallará si se ejecutó
-- anteriormente. Verificar primero con:
--   SHOW COLUMNS FROM lote_medicamento LIKE 'lm_precio_costo';
