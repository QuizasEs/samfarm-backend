ALTER TABLE lote_medicamento ADD COLUMN lm_costo_lista DECIMAL(12,2) DEFAULT NULL;
ALTER TABLE lote_medicamento ADD COLUMN lm_margen_u DECIMAL(5,2) DEFAULT NULL;
ALTER TABLE lote_medicamento ADD COLUMN lm_margen_c DECIMAL(5,2) DEFAULT NULL;
ALTER TABLE lote_medicamento ADD COLUMN lm_precio_min_u DECIMAL(12,2) DEFAULT NULL;
ALTER TABLE lote_medicamento ADD COLUMN lm_precio_min_c DECIMAL(12,2) DEFAULT NULL;