<div class="container">
    <h2>Cierre de caja</h2>
    <div id="info-caja">
        <!-- Aquí muestra datos de la caja activa (AJAX o PHP) -->
        <?php
        // Cargar datos de caja activa para el usuario
        require_once "./controllers/ventaController.php";
        $vc = new ventaController();
        // Reutilizamos consulta_caja_controller pero necesitamos datos (adaptación sencilla):
        $stmt = ventaModel::consulta_caja_model(['us_id' => $_SESSION['id_smp'], 'su_id' => $_SESSION['sucursal_smp']]);
        $caja = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <p><strong>Caja:</strong> <?php echo htmlspecialchars($caja['caja_nombre'] ?? 'N/A') ?></p>
        <p><strong>Saldo inicial:</strong> <?php echo number_format($caja['caja_saldo_inicial'] ?? 0, 2) ?> Bs</p>
    </div>

    <h3>Ventas durante la sesión</h3>
    <div id="ventas-resumen">
        <!-- Se pueden cargar vía AJAX listando movimientos o ventas por caja -->
        <!-- Para simplicidad se mostrará el total en efectivo (PHP) -->
        <?php
        $total_ventas = ventaModel::sumar_ventas_por_caja_model($caja['caja_id'] ?? 0, 'efectivo');
        ?>
        <p><strong>Ventas en efectivo:</strong> <span id="ventas_efectivo"><?php echo number_format($total_ventas, 2) ?></span> Bs</p>
    </div>

    <h3>Conteo de billetes y monedas (Bs)</h3>
    <table class="table inputs-denom">
        <thead>
            <tr>
                <th>Denominación</th>
                <th>Cantidad</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $denoms = [200, 100, 50, 20, 10, 5, 2, 1, 0.5, 0.2];
            foreach ($denoms as $d): ?>
                <tr>
                    <td><?php echo $d ?></td>
                    <td><input type="number" min="0" step="1" data-denom="<?php echo $d ?>" class="count"></td>
                    <td class="subtotal" data-denom="<?php echo $d ?>">0</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="result">
        <p>Total contado: <strong id="total_contado">0.00</strong> Bs</p>
        <p>Teórico (saldo inicial + ventas efectivo): <strong id="teorico"><?php echo number_format(($caja['caja_saldo_inicial'] ?? 0) + $total_ventas, 2) ?></strong> Bs</p>
        <p>Diferencia: <strong id="diferencia">0.00</strong> Bs</p>
    </div>

    <div style="margin-top:12px;">
        <button id="btn_confirmar" class="btn success">Confirmar cierre</button>
        <a href="#" onclick="history.back()" class="btn">Cancelar</a>
    </div>
</div>

<script>
    // JS simple para sumar conteo y enviar POST (no depende de libs externas)
    function toFloat(v) {
        return parseFloat(v) || 0;
    }
    const counts = document.querySelectorAll('.count');
    const totalContadoEl = document.getElementById('total_contado');
    const teoricoEl = document.getElementById('teorico');
    const diferenciaEl = document.getElementById('diferencia');
    const ventasEfectivo = toFloat(document.getElementById('ventas_efectivo').textContent);
    const saldoInicial = toFloat(<?php echo (float)($caja['caja_saldo_inicial'] ?? 0.0) ?>);

    function recalcular() {
        let total = 0;
        counts.forEach(inp => {
            const denom = parseFloat(inp.dataset.denom);
            const qty = parseInt(inp.value) || 0;
            const sum = qty * denom;
            const td = document.querySelector('.subtotal[data-denom="' + denom + '"]');
            if (td) td.textContent = sum.toFixed(2);
            total += sum;
        });
        totalContadoEl.textContent = total.toFixed(2);
        const teorico = saldoInicial + ventasEfectivo;
        const diff = total - teorico;
        diferenciaEl.textContent = diff.toFixed(2);
    }
    counts.forEach(i => i.addEventListener('input', recalcular));

    document.getElementById('btn_confirmar').addEventListener('click', function() {
        // Recalcular por seguridad
        recalcular();
        const total = parseFloat(totalContadoEl.textContent);
        // Armar objeto counts
        const payload = {};
        counts.forEach(inp => {
            payload[inp.dataset.denom] = parseInt(inp.value) || 0;
        });

        const formData = new FormData();
        formData.append('ventaAjax', 'cerrar-caja');
        formData.append('counts', JSON.stringify(payload));

        fetch('<?php echo SERVER_URL ?>ajax/ventaAjax.php', {
            method: 'POST',
            body: formData
        }).then(r => r.json()).then(j => {
            alert(j.texto || 'Respuesta: ' + JSON.stringify(j));
            if (j.Alerta === 'recargar') location.reload();
        }).catch(err => {
            alert('Error: ' + err);
        });
    });
</script>