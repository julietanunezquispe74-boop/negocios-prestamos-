<?php
// dashboard.php - Panel de control principal con resumen financiero
include 'db_connect.php';

$message = "";

// Consulta para obtener el resumen de todos los deudores y préstamos
$sql_resumen = "
    SELECT 
        COUNT(DISTINCT D.id_deudor) AS total_deudores,
        SUM(P.monto_prestamo) AS monto_total_prestamos,
        SUM(P.monto_prestamo + (P.monto_prestamo * P.interes_prestamo / 100)) AS monto_total_con_interes,
        SUM(C.monto_cancelacion) AS total_monto_pagado
    FROM Deudor D
    LEFT JOIN Prestamo P ON D.id_deudor = P.id_deudor
    LEFT JOIN Cancelacion C ON P.id_prestamo = C.id_prestamo
";
$resumen_result = $conn->query($sql_resumen);
$resumen = $resumen_result->fetch_assoc();

// Calcular el saldo total pendiente
$saldo_total_pendiente = ($resumen['monto_total_con_interes'] ?? 0) - ($resumen['total_monto_pagado'] ?? 0);

if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control | Gestión de Préstamos</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #50E3C2;
            --success-color: #7ED321;
            --danger-color: #D0021B;
            --text-dark: #2C3E50;
            --text-light: #F4F6F9;
            --bg-light: #F4F6F9;
            --bg-card: #FFFFFF;
            --border-light: #E0E6ED;
        }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-light); margin: 0; padding: 0; color: var(--text-dark); line-height: 1.6; }
        .container { max-width: 1200px; margin: 30px auto; padding: 20px; }
        header { text-align: center; margin-bottom: 40px; }
        h1 { color: var(--primary-color); font-size: 2.8em; margin: 0; }
        .message { padding: 15px; margin-bottom: 25px; border-radius: 8px; font-weight: bold; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .message.success { background-color: #dff0d8; color: var(--success-color); }
        .message.error { background-color: #f2dede; color: var(--danger-color); }

        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 50px; }
        .summary-card { background: var(--bg-card); padding: 25px; border-radius: 12px; box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08); text-align: center; }
        .summary-card h3 { font-size: 1.2em; color: var(--text-dark); margin: 0 0 10px; opacity: 0.8; }
        .summary-card .value { font-size: 2.5em; font-weight: 700; }
        .summary-card .value.deudores { color: var(--primary-color); }
        .summary-card .value.prestamos { color: var(--secondary-color); }
        .summary-card .value.pagado { color: var(--success-color); }
        .summary-card .value.pendiente { color: var(--danger-color); }

        .btn-group { text-align: center; }
        .btn { padding: 12px 25px; background-color: var(--primary-color); color: var(--text-light); border-radius: 8px; text-decoration: none; font-weight: 600; margin: 0 10px; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn:hover { background-color: #3476c2; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        
        .main-navigation { display: flex; justify-content: center; gap: 20px; margin-bottom: 40px; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Panel de Control</h1>
    </header>
    
    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="summary-grid">
        <div class="summary-card">
            <h3>Total de Deudores</h3>
            <div class="value deudores"><?php echo number_format($resumen['total_deudores'] ?? 0); ?></div>
        </div>
        <div class="summary-card">
            <h3>Monto Total Prestado</h3>
            <div class="value prestamos"><?php echo number_format($resumen['monto_total_prestamos'] ?? 0, 2, ',', '.'); ?></div>
        </div>
        <div class="summary-card">
            <h3>Total Pagado</h3>
            <div class="value pagado"><?php echo number_format($resumen['total_monto_pagado'] ?? 0, 2, ',', '.'); ?></div>
        </div>
        <div class="summary-card">
            <h3>Saldo Pendiente</h3>
            <div class="value pendiente"><?php echo number_format($saldo_total_pendiente, 2, ',', '.'); ?></div>
        </div>
    </div>

    <div class="btn-group">
        <a href="index.php" class="btn">Gestión de Deudores</a>
    </div>

</div>

</body>
</html>
