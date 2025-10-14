// selleccionamos todos los datos de formularios que tengasn de clase formularioajax
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
        mode: 'cors',
        cache: 'no-cache',
        body: data
    };

    let texto_alerta;

    switch (tipo) {
        case "save":
            texto_alerta = "Los datos quedarán guardados en el sistema";
            break;
        case "delete":
            texto_alerta = "Los datos en el sistema serán borrados";
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
		title: '¿Estás seguro?',
		text: texto_alerta,
		type: 'question',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Aceptar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if(result.value){
			fetch(action,config)
			.then(respuesta => respuesta.json())
			.then(respuesta => {
				return alertas_ajax(respuesta);
			});
		}
	});
}

// Asociar evento a cada formulario
formularios_ajax.forEach(formulario => {
    formulario.addEventListener("submit", enviar_formulario_ajax);
});

// funcion para manera los mensajes de las alertas
function alertas_ajax(alerta) {
    if (alerta.Alerta === "simple") {
        Swal.fire({
            title: alerta.Titulo,
            text: alerta.texto,
            icon: alerta.Tipo,
            confirmButtonText: "Aceptar"
        });
    } else if (alerta.Alerta === "recargar") {
        Swal.fire({
            title: alerta.Titulo,
            text: alerta.texto,
            icon: alerta.Tipo,
            confirmButtonText: "Aceptar"
        }).then((result) => {
            if (result.isConfirmed) location.reload();
        });
    } else if (alerta.Alerta === "limpiar") {
        Swal.fire({
            title: alerta.Titulo,
            text: alerta.texto,
            icon: alerta.Tipo,
            confirmButtonText: "Aceptar"
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector(".FormularioAjax").reset();
            }
        });
    } else if (alerta.Alerta === "redireccionar") {
        window.location.href = alerta.URL;
    }
}
