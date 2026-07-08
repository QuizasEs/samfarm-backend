<?php
if (!isset($_SESSION['id_smp'])) {
?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php
    exit();
}
?>

            <div class="title">
                <h1>presentacion</h1>
            </div>
            <!---------------------------------------------lista de presentacion--------------------------------------------------->

            <div class="container">
                <div class="lista-header">
                    <div class="filtro">
                        <input type="text" name="" id=""><button><ion-icon name="search"></ion-icon></button>
                    </div>
                    <div class="header-btn-usuario">
                        <a href="">NUEVA PRESENTACION</a>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>presentacion</th>
                                <th>description</th>
                                <th>estado</th>
                                <th>acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>frasco</td>
                                <td>frasco</td>
                                <td><span class="in-active">in activo</span></td>
                                <td><a href="" class="btn-editar"> editar</a></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>frasco</td>
                                <td>frasco</td>
                                <td><span class="in-active">in activo</span></td>
                                <td><a href="" class="btn-editar"> editar</a></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>frasco</td>
                                <td>frasco</td>
                                <td><span class="in-active">in activo</span></td>
                                <td><a href="" class="btn-editar"> editar</a></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>frasco</td>
                                <td>frasco</td>
                                <td><span class="in-active">in activo</span></td>
                                <td><a href="" class="btn-editar"> editar</a></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>frasco</td>
                                <td>frasco</td>
                                <td><span class="in-active">in activo</span></td>
                                <td><a href="" class="btn-editar"> editar</a></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
