<?php
    class viewsModel{
        
        /* -------------------------------------------Obtener vistas-------------------------------------------------- */
        protected static function get_views_model($vistas){
            $listaBlanca=[
                "dashboard",
                "usuarioRegistro",
                "categoriaLista",
                "laboratorioLista",
                "presentacionLista",
                "usuarioLista",
                "usuarioActualizar",
                "medicamentoRegistro",
                "medicamentoLista",
                "medicamentoActualizar",
                "viaDeAdministracionLista", 
                "lotesRegistro",
                "proveedorRegistro", 
                "proveedorLista",
                "proveedorActualizar",
                "laboratorioRegistro",
                "laboratorioActualizar", 
                "compraRegistro",
                "compraOrden",
                "loteActualizar",
                "loteLista",
                "caja",
                "cajaCerrar",
                "inventarioLista",
                "ventasHistorialLista",
                "cajaHistorialLista",
                "comprasHistorialLista",
                "clienteLista",
                "devolucionLista",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
            ];

            if(in_array($vistas,$listaBlanca)){
                /* preguntamos si la vista a la que se quiere acceder existe dentro de los archivos */
                if(is_file("./views/content/".$vistas."-view.php")){
                    /* si existe asignamos la variable contenido la ruta de la vista */
                    $contenido = "./views/content/".$vistas."-view.php";
                } else{
                    /* si no mandamos ERROR */
                    $contenido = "404";
                }
            } elseif($vistas=="login" || $vistas=="index"){
                /* preguntamos que si la vista a la que se esta intentado ingresar es login o index*/
                /* SI es cuialquiera de estas 2 entonces devolvemos login */
                $contenido = "login";

                /* si la vista a la que se intenta acceder esta fuera de la lista de vistas permitidas votar ERROR */
            } else{
                $contenido = "404";
            }

            /* como la vista a la que se intenta acceder no pertenece a login o index pero si esta dentro de la lista de 
            vistas permiticas devolvemos la misma vista  */
            return $contenido;
        }



        /* -------------------------------------------Obtener vistas-------------------------------------------------- */
        /* -------------------------------------------Obtener vistas-------------------------------------------------- */
        /* -------------------------------------------Obtener vistas-------------------------------------------------- */
        /* -------------------------------------------Obtener vistas-------------------------------------------------- */
        /* -------------------------------------------Obtener vistas-------------------------------------------------- */
        /* -------------------------------------------Obtener vistas-------------------------------------------------- */
    }
?>