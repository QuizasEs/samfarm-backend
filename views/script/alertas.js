// Seleccionamos todos los formularios con clase FormularioAjax
const formularios_ajax = document.querySelectorAll(".FormularioAjax");

function enviar_formulario_ajax(e) {
    e.preventDefault();

    let data = new FormData(this);
    let method = this.getAttribute("method");
    let action = this.getAttribute("action");
    let tipo = this.getAttribute("data-form");

    let encabezados = new Headers();

    let config = {
        method: method,
        headers: encabezados,
        mode: "cors",
        cache: "no-cache",
        body: data,
    };

    let texto_alerta;

    switch (tipo) {
        case "save":
            texto_alerta = "Los datos quedarán guardados en el sistema";
            break;
        case "delete":
            texto_alerta = "Los datos en el sistema serán borrados";
            break;
        case "disable":
            texto_alerta = "Quiere desabilitar estos datos en el sistema?";
            break;
        case "update":
            texto_alerta = "Los datos en el sistema serán actualizados";
            break;
        case "search":
            texto_alerta = "Se eliminará el término de búsqueda y se deberá ingresar uno nuevo";
            break;
        case "loans":
            texto_alerta = "¿Desea remover los datos seleccionados?";
            break;
        default:
            texto_alerta = "¿Quiere realizar la operación solicitada?";
            break;
    }

    Swal.fire({
        title: "¿Estás seguro?",
        text: texto_alerta,
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.value) {
            fetch(action, config)
                .then((respuesta) => respuesta.json())
                .then((respuesta) => {
                    return alertas_ajax(respuesta);
                });
        }
    });
}

// Asociar evento a cada formulario
formularios_ajax.forEach((formulario) => {
    formulario.addEventListener("submit", enviar_formulario_ajax);
});

// Función para abrir PDF desde base64
function abrirPDFDesdeBase64(base64Data, nombreArchivo) {
    try {
        console.log('📄 Intentando abrir PDF...');
        
        // Decodificar base64
        const byteCharacters = atob(base64Data);
        const byteNumbers = new Array(byteCharacters.length);
        
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: 'application/pdf' });
        
        // Crear URL temporal
        const url = URL.createObjectURL(blob);
        
        // Abrir en nueva ventana
        const ventanaPDF = window.open(url, '_blank', 'width=800,height=600');
        
        // Si fue bloqueado por el navegador
        if (!ventanaPDF || ventanaPDF.closed || typeof ventanaPDF.closed === 'undefined') {
            console.warn('⚠️ Pop-up bloqueado por el navegador');
            
            // Crear enlace de descarga como alternativa
            const link = document.createElement('a');
            link.href = url;
            link.download = nombreArchivo;
            link.textContent = 'Descargar PDF';
            
            Swal.fire({
                title: 'Pop-up bloqueado',
                html: `Tu navegador bloqueó la ventana del PDF.<br><br>
                       <a href="${url}" target="_blank" style="display:inline-block; padding:10px 20px; background:#3085d6; color:white; text-decoration:none; border-radius:5px; margin:10px;">
                           📄 Abrir PDF en nueva pestaña
                       </a>`,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                didClose: () => {
                    URL.revokeObjectURL(url);
                }
            });
        } else {
            // Limpiar URL cuando se cierre la ventana
            const checkClosed = setInterval(() => {
                if (ventanaPDF.closed) {
                    clearInterval(checkClosed);
                    URL.revokeObjectURL(url);
                    console.log('✅ PDF cerrado, memoria liberada');
                }
            }, 1000);
            
            console.log('✅ PDF abierto exitosamente');
        }
        
        return true;
        
    } catch (error) {
        console.error('❌ Error abriendo PDF:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo abrir el PDF. Intenta nuevamente.'
        });
        return false;
    }
}

// Función para manejar los mensajes de las alertas
function alertas_ajax(alerta) {
    console.log('📨 Respuesta recibida:', alerta);
    
    // ✅ CASO ESPECIAL: Venta exitosa con PDF
    if (alerta.Alerta === "venta_exitosa") {
        Swal.fire({
            title: alerta.Titulo,
            text: alerta.texto,
            icon: alerta.Tipo,
            showCancelButton: true,
            confirmButtonText: '📄 Ver Nota de Venta',
            cancelButtonText: 'Cerrar',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed && alerta.pdf_data) {
                // Abrir PDF desde base64
                abrirPDFDesdeBase64(alerta.pdf_data, alerta.pdf_nombre || 'documento.pdf');
            }
            
            // Recargar página después de cerrar
            setTimeout(() => {
                location.reload();
            }, 500);
        });
        return;
    }
    
    // ✅ COMPATIBILIDAD: PDF por URL (sistema antiguo)
    if (alerta.pdf_url) {
        console.log('🔗 Abriendo PDF por URL:', alerta.pdf_url);
        let ventana = window.open(alerta.pdf_url, "_blank");

        // Verificar si fue bloqueado
        if (!ventana || ventana.closed || typeof ventana.closed == "undefined") {
            Swal.fire({
                title: "Pop-up bloqueado",
                html: `Tu navegador bloqueó la ventana del PDF.<br><br>
                   <a href="${alerta.pdf_url}" target="_blank" class="btn btn-primary" style="display:inline-block; padding:10px 20px; background:#3085d6; color:white; text-decoration:none; border-radius:5px;">
                       📄 Clic aquí para abrir el PDF
                   </a>`,
                icon: "warning",
                confirmButtonText: "Entendido",
            });
        }
    }

    // ✅ ALERTAS ESTÁNDAR
    if (alerta.Alerta === "simple") {
        Swal.fire({
            title: alerta.Titulo,
            text: alerta.texto,
            icon: alerta.Tipo,
            confirmButtonText: "Aceptar",
        });
    } else if (alerta.Alerta === "recargar") {
        Swal.fire({
            title: alerta.Titulo,
            text: alerta.texto,
            icon: alerta.Tipo,
            confirmButtonText: "Aceptar",
        }).then((result) => {
            if (result.isConfirmed) location.reload();
        });
    } else if (alerta.Alerta === "limpiar") {
        Swal.fire({
            title: alerta.Titulo,
            text: alerta.texto,
            icon: alerta.Tipo,
            confirmButtonText: "Aceptar",
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector(".FormularioAjax").reset();
            }
        });
    } else if (alerta.Alerta === "redireccionar") {
        Swal.fire({
            title: alerta.Titulo || "Redirigiendo...",
            text: alerta.texto,
            icon: alerta.Tipo || "info",
            confirmButtonText: "Aceptar",
        }).then(() => {
            window.location.href = alerta.URL;
        });
    }
}