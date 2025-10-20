<?php
// cancelaciones.php - Página para gestionar las cancelaciones de un préstamo específico
include 'db_connect.php'; 

$id_prestamo = isset($_GET['id_prestamo']) ? (int)$_GET['id_prestamo'] : 0;
if ($id_prestamo == 0) {
    die("❌ Error: No se ha especificado un préstamo.");
}

$message = "";
$editing = false;
$cancelacion_data = [];

// Obtener los datos del préstamo para el título y el enlace de regreso
$prestamo_sql = "SELECT P.id_deudor, P.objeto_emprenda, P.monto_prestamo, P.interes_prestamo, D.nombre AS nombre_deudor 
                 FROM Prestamo P JOIN Deudor D ON P.id_deudor = D.id_deudor 
                 WHERE P.id_prestamo = ?";
$stmt = $conn->prepare($prestamo_sql);
$stmt->bind_param("i", $id_prestamo);
$stmt->execute();
$prestamo_result = $stmt->get_result();
if ($prestamo_result->num_rows > 0) {
    $prestamo = $prestamo_result->fetch_assoc();
} else {
    die("❌ Error: Préstamo no encontrado.");
}
$stmt->close();

// Obtener el monto total cancelado
$cancelacion_sum_sql = "SELECT SUM(monto_cancelacion) as total_cancelado FROM Cancelacion WHERE id_prestamo = ?";
$stmt_sum = $conn->prepare($cancelacion_sum_sql);
$stmt_sum->bind_param("i", $id_prestamo);
$stmt_sum->execute();
$cancelacion_sum_result = $stmt_sum->get_result();
$total_cancelado = 0;
if ($row_sum = $cancelacion_sum_result->fetch_assoc()) {
    $total_cancelado = $row_sum['total_cancelado'] ?? 0;
}
$stmt_sum->close();

// Calcular monto total a pagar y saldo pendiente
$monto_total_con_interes = $prestamo['monto_prestamo'] + ($prestamo['monto_prestamo'] * $prestamo['interes_prestamo'] / 100);
$saldo_pendiente = $monto_total_con_interes - $total_cancelado;

// Lógica para AGREGAR una nueva cancelación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_cancelacion'])) {
    $fecha_cancelacion = $_POST['fecha_cancelacion'];
    $monto_cancelacion = $_POST['monto_cancelacion'];
    $forma_pago = $_POST['forma_pago'];

    $sql = "INSERT INTO Cancelacion (id_prestamo, fecha_cancelacion, monto_cancelacion, forma_pago) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isds", $id_prestamo, $fecha_cancelacion, $monto_cancelacion, $forma_pago);

    if ($stmt->execute()) {
        $message = "✅ Cancelación agregada exitosamente.";
    } else {
        $message = "❌ Error al agregar la cancelación: " . $stmt->error;
    }
    $stmt->close();
    header("Location: cancelaciones.php?id_prestamo=$id_prestamo&message=" . urlencode($message));
    exit();
}

// Lógica para ELIMINAR una cancelación
if (isset($_GET['delete_id'])) {
    $id_cancelacion = (int)$_GET['delete_id'];
    $sql = "DELETE FROM Cancelacion WHERE id_cancelacion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cancelacion);
    if ($stmt->execute()) {
        $message = "✅ Cancelación eliminada exitosamente.";
    } else {
        $message = "❌ Error al eliminar la cancelación: " . $stmt->error;
    }
    $stmt->close();
    header("Location: cancelaciones.php?id_prestamo=$id_prestamo&message=" . urlencode($message));
    exit();
}

// Lógica para EDITAR una cancelación (cargar datos en el formulario)
if (isset($_GET['edit_id'])) {
    $editing = true;
    $id_cancelacion = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM Cancelacion WHERE id_cancelacion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cancelacion);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $cancelacion_data = $result->fetch_assoc();
    } else {
        $message = "❌ Cancelación no encontrada.";
        $editing = false;
    }
    $stmt->close();
}

// Lógica para ACTUALIZAR una cancelación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cancelacion'])) {
    $id_cancelacion = $_POST['id_cancelacion'];
    $fecha_cancelacion = $_POST['fecha_cancelacion'];
    $monto_cancelacion = $_POST['monto_cancelacion'];
    $forma_pago = $_POST['forma_pago'];

    $sql = "UPDATE Cancelacion SET fecha_cancelacion = ?, monto_cancelacion = ?, forma_pago = ? WHERE id_cancelacion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsi", $fecha_cancelacion, $monto_cancelacion, $forma_pago, $id_cancelacion);
    if ($stmt->execute()) {
        $message = "✅ Cancelación actualizada exitosamente.";
    } else {
        $message = "❌ Error al actualizar la cancelación: " . $stmt->error;
    }
    $stmt->close();
    header("Location: cancelaciones.php?id_prestamo=$id_prestamo&message=" . urlencode($message));
    exit();
}

if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelaciones del Préstamo: <?php echo htmlspecialchars($prestamo['objeto_emprenda']); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        :root {
            --primary-color: #4A90E2;
            --success-color: #7ED321;
            --danger-color: #D0021B;
            --warning-color: #F8E71C;
            --text-dark: #2C3E50;
            --text-light: #F4F6F9;
            --bg-light: #F4F6F9;
            --bg-card: #FFFFFF;
            --border-light: #E0E6ED;
        }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-light); margin: 0; padding: 0; color: var(--text-dark); line-height: 1.6; }
        .container { max-width: 1200px; margin: 30px auto; padding: 20px; background: var(--bg-card); border-radius: 15px; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
        header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid var(--border-light); padding-bottom: 20px; }
        h1 { color: var(--primary-color); font-size: 2.5em; margin: 0; }
        .message { padding: 15px; margin-bottom: 25px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .message.success { background-color: #dff0d8; color: var(--success-color); }
        .message.error { background-color: #f2dede; color: var(--danger-color); }
        h2 { font-size: 1.8em; margin-top: 40px; margin-bottom: 20px; color: var(--text-dark); }
        .form-section { border: 1px solid var(--border-light); padding: 30px; border-radius: 12px; margin-bottom: 30px; background: #fafbfc; }
        .form-section h3 { margin-top: 0; color: var(--primary-color); font-size: 1.5em; }
        form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; transition: border-color 0.3s; }
        .form-group input:focus { outline: none; border-color: var(--primary-color); }
        .form-buttons { grid-column: span 2; display: flex; gap: 15px; justify-content: flex-end; }
        .form-buttons button, .form-buttons a { padding: 12px 25px; color: var(--text-light); border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 1em; text-decoration: none; transition: all 0.3s ease; }
        .form-buttons button.primary { background-color: var(--primary-color); }
        .form-buttons button.primary:hover { background-color: #3476c2; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        .form-buttons a.cancel { background-color: #6C7A89; }
        .form-buttons a.cancel:hover { background-color: #56616e; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        
        table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        th, td { padding: 15px; border-bottom: 1px solid var(--border-light); text-align: left; }
        thead tr { background-color: var(--primary-color); color: var(--text-light); }
        th:first-child, td:first-child { border-left: none; }
        th:last-child, td:last-child { border-right: none; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background-color: #f9f9fb; }
        
        .action-buttons { display: flex; gap: 8px; }
        .action-buttons a { padding: 8px 15px; text-decoration: none; color: var(--text-light); border-radius: 6px; font-weight: 600; font-size: 0.9em; transition: all 0.3s ease; }
        .action-buttons .edit { background-color: var(--warning-color); color: var(--text-dark); }
        .action-buttons .edit:hover { background-color: #f3d400; transform: translateY(-1px); }
        .action-buttons .delete { background-color: var(--danger-color); }
        .action-buttons .delete:hover { background-color: #b00219; transform: translateY(-1px); }
        .action-buttons .view { background-color: var(--primary-color); }
        .action-buttons .view:hover { background-color: #3476c2; transform: translateY(-1px); }

        .summary-box { 
            background-color: var(--bg-card); 
            border: 1px solid var(--border-light); 
            border-radius: 12px; 
            padding: 25px; 
            margin-bottom: 30px; 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .label {
            font-size: 1em;
            color: #6C7A89;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 1.8em;
            font-weight: 700;
        }
        .summary-item .value.paid { color: var(--success-color); }
        .summary-item .value.remaining { color: var(--danger-color); }
        
        .main-navigation { display: flex; justify-content: center; margin-bottom: 25px; }
        .main-navigation a {
            padding: 12px 25px;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .main-navigation a:hover { color: var(--primary-color); border-bottom-color: var(--primary-color); }

        @media (max-width: 768px) { 
            .container { padding: 15px; margin: 15px; }
            form { grid-template-columns: 1fr; } 
            .form-buttons { flex-direction: column; } 
            .summary-box { grid-template-columns: 1fr; }
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { border: 1px solid var(--border-light); margin-bottom: 10px; border-radius: 8px; }
            td { border-bottom: none; position: relative; padding-left: 50%; text-align: right; }
            td:before { position: absolute; left: 6px; width: 45%; padding-right: 10px; white-space: nowrap; text-align: left; font-weight: 600; }
            td:nth-of-type(1):before { content: "ID Cancelación"; }
            td:nth-of-type(2):before { content: "Fecha"; }
            td:nth-of-type(3):before { content: "Monto"; }
            td:nth-of-type(4):before { content: "Forma de Pago"; }
            td:nth-of-type(5):before { content: "Acciones"; }
            .action-buttons { justify-content: flex-end; }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Cancelaciones del Préstamo: <?php echo htmlspecialchars($prestamo['objeto_emprenda']); ?></h1>
    </header>

    <div class="main-navigation">
        <a href="dashboard.php">Panel de Control</a>
        <a href="index.php">Gestión de Deudores</a>
    </div>

    <a href="prestamos.php?id_deudor=<?php echo htmlspecialchars($prestamo['id_deudor']); ?>" class="btn-group">← Volver a Préstamos de <?php echo htmlspecialchars($prestamo['nombre_deudor']); ?></a>
    
    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="summary-box">
        <div class="summary-item">
            <div class="label">Monto Original</div>
            <div class="value"><?php echo number_format($prestamo['monto_prestamo'], 2, ',', '.'); ?></div>
        </div>
        <div class="summary-item">
            <div class="label">Monto con Interés</div>
            <div class="value"><?php echo number_format($monto_total_con_interes, 2, ',', '.'); ?></div>
        </div>
        <div class="summary-item">
            <div class="label">Total Pagado</div>
            <div class="value paid"><?php echo number_format($total_cancelado, 2, ',', '.'); ?></div>
        </div>
        <div class="summary-item">
            <div class="label">Saldo Pendiente</div>
            <div class="value remaining"><?php echo number_format($saldo_pendiente, 2, ',', '.'); ?></div>
        </div>
    </div>

    <div class="form-section">
        <h3><?php echo $editing ? 'Editar Cancelación' : 'Agregar Nueva Cancelación'; ?></h3>
        <form action="cancelaciones.php?id_prestamo=<?php echo $id_prestamo; ?>" method="POST">
            <?php if ($editing): ?>
                <input type="hidden" name="id_cancelacion" value="<?php echo htmlspecialchars($cancelacion_data['id_cancelacion']); ?>">
                <input type="hidden" name="update_cancelacion" value="1">
            <?php else: ?>
                <input type="hidden" name="add_cancelacion" value="1">
            <?php endif; ?>

            <div class="form-group">
                <label for="fecha_cancelacion">Fecha de Cancelación:</label>
                <input type="date" id="fecha_cancelacion" name="fecha_cancelacion" value="<?php echo htmlspecialchars($cancelacion_data['fecha_cancelacion'] ?? date('Y-m-d')); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="monto_cancelacion">Monto:</label>
                <input type="number" step="0.01" id="monto_cancelacion" name="monto_cancelacion" value="<?php echo htmlspecialchars($cancelacion_data['monto_cancelacion'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="forma_pago">Forma de Pago:</label>
                <input type="text" id="forma_pago" name="forma_pago" value="<?php echo htmlspecialchars($cancelacion_data['forma_pago'] ?? ''); ?>">
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="primary"><?php echo $editing ? 'Actualizar Cancelación' : 'Agregar Cancelación'; ?></button>
                <?php if ($editing): ?>
                    <a href="cancelaciones.php?id_prestamo=<?php echo $id_prestamo; ?>" class="cancel">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <h2>Lista de Cancelaciones</h2>
    <table>
        <thead>
            <tr>
                <th>ID Cancelación</th>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Forma de Pago</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM Cancelacion WHERE id_prestamo = ? ORDER BY fecha_cancelacion DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_prestamo);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["id_cancelacion"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["fecha_cancelacion"]) . "</td>";
                    echo "<td>" . number_format($row["monto_cancelacion"], 2, ',', '.') . "</td>";
                    echo "<td>" . htmlspecialchars($row["forma_pago"]) . "</td>";
                    echo "<td class='action-buttons'>";
                    echo "<a href='cancelaciones.php?id_prestamo=" . htmlspecialchars($id_prestamo) . "&edit_id=" . htmlspecialchars($row["id_cancelacion"]) . "' class='edit'>Editar</a>";
                    echo "<a href='cancelaciones.php?id_prestamo=" . htmlspecialchars($id_prestamo) . "&delete_id=" . htmlspecialchars($row["id_cancelacion"]) . "' class='delete' onclick=\"return confirm('¿Estás seguro de que quieres eliminar esta cancelación?');\">Eliminar</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align: center;'>No hay cancelaciones registradas para este préstamo.</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
