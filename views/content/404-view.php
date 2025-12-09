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
<div class="404-main" style="background: linear-gradient(135deg, #13386c 0%, #1b4681 50%, #293241 100%);">
        <div class="error-404-page error-404-design active">
            <div class="error-404-heart-monitor">
                <div class="error-404-ecg-line">
                    <svg viewBox="0 0 400 100">
                        <path class="error-404-ecg-path" d="M 0 50 L 50 50 L 60 30 L 70 70 L 80 20 L 90 50 L 400 50" />
                    </svg>
                </div>
                <div class="error-404-code">404</div>
            </div>
            <h2 class="error-404-title">Sin señales vitales</h2>
            <p class="error-404-text">Esta página no responde a nuestro diagnóstico</p>
            <a href="#" class="error-404-btn-home">Volver al inicio</a>
        </div>
    </div>