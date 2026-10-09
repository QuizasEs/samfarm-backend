-- Etapa 6: emision y envio de paquetes de facturas en contingencia.
--
-- El paquete se arma como un TAR.GZ (XMLs firmados de cada factura) que se
-- envia en base64 dentro de la etiqueta <archivo> de recepcionPaqueteFactura,
-- y el SIN devuelve un codigo de recepcion que despues se consulta con
-- validacionRecepcionPaquete.
--
-- Sin estas dos columnas el paquete enviado no queda registrado en ningun
-- lado: si la llamada al SIN falla despues de armar el archivo, no hay forma
-- de reintentarlo sin volver a construirlo, y el hash tendria que coincidir
-- byte a byte con el anterior.
--
-- pa_archivo  : el TAR.GZ en base64, tal cual se mando al SIN.
-- pa_facturas : JSON con los fe_id/fa_id/cuf que integran el paquete, para
--               poder marcar cada factura como validada cuando el SIN
--               confirme la recepcion.

ALTER TABLE siat_paquetes
    ADD COLUMN pa_archivo  LONGTEXT NULL COMMENT 'TAR.GZ del paquete en base64'          AFTER pa_hash_archivo,
    ADD COLUMN pa_facturas LONGTEXT NULL COMMENT 'JSON con las facturas del paquete'     AFTER pa_archivo;
