<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn_salir = document.querySelector(".btn-exit-system");

        if (!btn_salir) return; // Evita errores si el botón no existe

        btn_salir.addEventListener('click', (e) => {
            e.preventDefault();

            Swal.fire({
                title: '¿Estás seguro de cerrar sesión?',
                text: 'Tu sesión se cerrará y saldrás del sistema.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                /* alert("holis") */
                /* debug */
                if (result.isConfirmed) {
                    const url = '<?php echo SERVER_URL; ?>ajax/loginAjax.php';
                    const token = '<?php echo $lc->encryption($_SESSION['token_smp']); ?>';
                    const usuario = '<?php echo $lc->encryption($_SESSION['usuario_smp']); ?>';

                    const datos = new FormData();
                    datos.append("token", token);
                    datos.append("usuario", usuario);
                    Swal.fire({
                        html: "I will close in <b></b> milliseconds.",
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {Swal.showLoading()}
                        
                    })
                    fetch(url, {
                            method: 'POST',
                            body: datos
                        })
                        .then(res => res.json())
                        .then(respuesta => alertas_ajax(respuesta))
                        .catch(error => {
                            console.error("Error al cerrar sesión:", error);
                            Swal.fire("Error", "Ocurrió un problema al cerrar sesión.", "error");
                        });
                }
            });
        });
    });
</script>