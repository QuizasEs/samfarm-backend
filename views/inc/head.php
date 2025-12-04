<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        $peticionAjax = false;
        require_once __DIR__ . '/../../controllers/sucursalController.php';
        $ins_sucursal = new sucursalController();
        $config_json = $ins_sucursal->datos_config_empresa_controller();
        $config = json_decode($config_json, true);
        $nombre_empresa = $config['ce_nombre'] ?? 'Farmacia';
        $logo_empresa = $config['ce_logo'] ?? SERVER_URL . 'views/assets/img/predeterminado.png';
    ?>
    <title><?php echo htmlspecialchars($nombre_empresa); ?></title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($logo_empresa); ?>">
    <link rel="stylesheet" href="<?php echo SERVER_URL; ?>views/css/style.css">
    <!-- script ion-icons -->
<!--     <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script> -->
    <!-- script apache echarts -->
    <script src="
        https://cdn.jsdelivr.net/npm/echarts@6.0.0/dist/echarts.min.js
    "></script>
    <!-- sweetalert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>