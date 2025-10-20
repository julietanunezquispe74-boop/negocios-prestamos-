<?php
// prestamos.php - Página para gestionar los préstamos de un deudor específico
include 'db_connect.php'; 

$id_deudor = isset($_GET['id_deudor']) ? (int)$_GET['id_deudor'] : 0;
if ($id_deudor == 0) {
    die("❌ Error: No se ha especificado un deudor.");
}

$message = "";
$editing = false;
$prestamo_data = [];

// Obtener los datos del deudor
$deudor_sql = "SELECT nombre FROM Deudor WHERE id_deudor = ?";
$stmt = $conn->prepare($deudor_sql);
$stmt->bind_param("i", $id_deudor);
$stmt->execute();
$deudor_result = $stmt->get_result();
if ($deudor_result->num_rows > 0) {
    $deudor = $deudor_result->fetch_assoc();
} else {
    die("❌ Error: Deudor no encontrado.");
}
$stmt->close();

// Lógica para AGREGAR un nuevo préstamo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_prestamo'])) {
    $objeto_emprenda = $_POST['objeto_emprenda'];
    $monto_prestamo = $_POST['monto_prestamo'];
    $fecha_prestamo = $_POST['fecha_prestamo'];
    $interes_prestamo = $_POST['interes_prestamo'];

    $sql = "INSERT INTO Prestamo (id_deudor, objeto_emprenda, monto_prestamo, fecha_prestamo, interes_prestamo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdss", $id_deudor, $objeto_emprenda, $monto_prestamo, $fecha_prestamo, $interes_prestamo);
    if ($stmt->execute()) {
        $message = "✅ Préstamo agregado exitosamente.";
    } else {
        $message = "❌ Error al agregar el préstamo: " . $stmt->error;
    }
    $stmt->close();
    header("Location: prestamos.php?id_deudor=$id_deudor&message=" . urlencode($message));
    exit();
}

// Lógica para ELIMINAR un préstamo
if (isset($_GET['delete_id'])) {
    $id_prestamo = (int)$_GET['delete_id'];
    // Primero, eliminar cancelaciones asociadas
    $conn->begin_transaction();
    try {
        $sql_cancelaciones = "DELETE FROM Cancelacion WHERE id_prestamo = ?";
        $stmt_cancelaciones = $conn->prepare($sql_cancelaciones);
        $stmt_cancelaciones->bind_param("i", $id_prestamo);
        $stmt_cancelaciones->execute();
        
        $sql_prestamo = "DELETE FROM Prestamo WHERE id_prestamo = ?";
        $stmt_prestamo = $conn->prepare($sql_prestamo);
        $stmt_prestamo->bind_param("i", $id_prestamo);
        $stmt_prestamo->execute();
        
        $conn->commit();
        $message = "✅ Préstamo y sus cancelaciones eliminadas exitosamente.";
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $message = "❌ Error al eliminar el préstamo: " . $e->getMessage();
    }
    header("Location: prestamos.php?id_deudor=$id_deudor&message=" . urlencode($message));
    exit();
}

// Lógica para EDITAR un préstamo (cargar datos en el formulario)
if (isset($_GET['edit_id'])) {
    $editing = true;
    $id_prestamo = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM Prestamo WHERE id_prestamo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_prestamo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $prestamo_data = $result->fetch_assoc();
    } else {
        $message = "❌ Préstamo no encontrado.";
        $editing = false;
    }
    $stmt->close();
}

// Lógica para ACTUALIZAR un préstamo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_prestamo'])) {
    $id_prestamo = $_POST['id_prestamo'];
    $objeto_emprenda = $_POST['objeto_emprenda'];
    $monto_prestamo = $_POST['monto_prestamo'];
    $fecha_prestamo = $_POST['fecha_prestamo'];
    $interes_prestamo = $_POST['interes_prestamo'];

    $sql = "UPDATE Prestamo SET objeto_emprenda = ?, monto_prestamo = ?, fecha_prestamo = ?, interes_prestamo = ? WHERE id_prestamo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssi", $objeto_emprenda, $monto_prestamo, $fecha_prestamo, $interes_prestamo, $id_prestamo);
    if ($stmt->execute()) {
        $message = "✅ Préstamo actualizado exitosamente.";
    } else {
        $message = "❌ Error al actualizar el préstamo: " . $stmt->error;
    }
    $stmt->close();
    header("Location: prestamos.php?id_deudor=$id_deudor&message=" . urlencode($message));
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
    <title>Gestión de Préstamos de <?php echo htmlspecialchars($deudor['nombre']); ?></title>
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
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid { background-color: var(--success-color); color: var(--text-light); }
        .status-pending { background-color: var(--danger-color); color: var(--text-light); }

        @media (max-width: 768px) { 
            .container { padding: 15px; margin: 15px; }
            form { grid-template-columns: 1fr; } 
            .form-buttons { flex-direction: column; } 
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { border: 1px solid var(--border-light); margin-bottom: 10px; border-radius: 8px; }
            td { border-bottom: none; position: relative; padding-left: 50%; text-align: right; }
            td:before { position: absolute; left: 6px; width: 45%; padding-right: 10px; white-space: nowrap; text-align: left; font-weight: 600; }
            td:nth-of-type(1):before { content: "ID Préstamo"; }
            td:nth-of-type(2):before { content: "Objeto"; }
            td:nth-of-type(3):before { content: "Monto"; }
            td:nth-of-type(4):before { content: "Interés"; }
            td:nth-of-type(5):before { content: "Monto Pagado"; }
            td:nth-of-type(6):before { content: "Saldo Pendiente"; }
            td:nth-of-type(7):before { content: "Estado"; }
            td:nth-of-type(8):before { content: "Acciones"; }
            .action-buttons { justify-content: flex-end; }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Préstamos de <?php echo htmlspecialchars($deudor['nombre']); ?></h1>
    </header>

    <div class="main-navigation">
        <a href="dashboard.php">Panel de Control</a>
        <a href="index.php">Gestión de Deudores</a>
    </div>

    <a href="index.php" class="btn-group">← Volver a Deudores</a>
    
    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="form-section">
        <h3><?php echo $editing ? 'Editar Préstamo' : 'Agregar Nuevo Préstamo'; ?></h3>
        <form action="prestamos.php?id_deudor=<?php echo $id_deudor; ?>" method="POST">
            <?php if ($editing): ?>
                <input type="hidden" name="id_prestamo" value="<?php echo htmlspecialchars($prestamo_data['id_prestamo']); ?>">
                <input type="hidden" name="update_prestamo" value="1">
            <?php else: ?>
                <input type="hidden" name="add_prestamo" value="1">
            <?php endif; ?>

            <div class="form-group">
                <label for="objeto_emprenda">Objeto del Préstamo:</label>
                <input type="text" id="objeto_emprenda" name="objeto_emprenda" value="<?php echo htmlspecialchars($prestamo_data['objeto_emprenda'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="monto_prestamo">Monto:</label>
                <input type="number" step="0.01" id="monto_prestamo" name="monto_prestamo" value="<?php echo htmlspecialchars($prestamo_data['monto_prestamo'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="fecha_prestamo">Fecha:</label>
                <input type="date" id="fecha_prestamo" name="fecha_prestamo" value="<?php echo htmlspecialchars($prestamo_data['fecha_prestamo'] ?? date('Y-m-d')); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="interes_prestamo">Interés (%):</label>
                <input type="number" step="0.01" id="interes_prestamo" name="interes_prestamo" value="<?php echo htmlspecialchars($prestamo_data['interes_prestamo'] ?? ''); ?>" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="primary"><?php echo $editing ? 'Actualizar Préstamo' : 'Agregar Préstamo'; ?></button>
                <?php if ($editing): ?>
                    <a href="prestamos.php?id_deudor=<?php echo $id_deudor; ?>" class="cancel">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <h2>Lista de Préstamos</h2>
    <table>
        <thead>
            <tr>
                <th>ID Préstamo</th>
                <th>Objeto</th>
                <th>Monto</th>
                <th>Interés</th>
                <th>Monto Pagado</th>
                <th>Saldo Pendiente</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT P.*, SUM(C.monto_cancelacion) AS total_pagado
                    FROM Prestamo P
                    LEFT JOIN Cancelacion C ON P.id_prestamo = C.id_prestamo
                    WHERE P.id_deudor = ?
                    GROUP BY P.id_prestamo
                    ORDER BY P.fecha_prestamo DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_deudor);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $monto_total_con_interes = $row['monto_prestamo'] + ($row['monto_prestamo'] * $row['interes_prestamo'] / 100);
                    $saldo_pendiente = $monto_total_con_interes - ($row['total_pagado'] ?? 0);
                    $estado = $saldo_pendiente <= 0 ? "Pagado" : "Pendiente";
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["id_prestamo"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["objeto_emprenda"]) . "</td>";
                    echo "<td>" . number_format($row["monto_prestamo"], 2, ',', '.') . "</td>";
                    echo "<td>" . number_format($row["interes_prestamo"], 2, ',', '.') . "%</td>";
                    echo "<td>" . number_format($row["total_pagado"] ?? 0, 2, ',', '.') . "</td>";
                    
                    $saldo_class = $saldo_pendiente <= 0 ? 'color: var(--success-color); font-weight: bold;' : 'color: var(--danger-color); font-weight: bold;';
                    echo "<td style='" . $saldo_class . "'>" . number_format($saldo_pendiente, 2, ',', '.') . "</td>";
                    
                    $badge_class = $estado === 'Pagado' ? 'status-paid' : 'status-pending';
                    echo "<td><span class='status-badge " . $badge_class . "'>" . $estado . "</span></td>";

                    echo "<td class='action-buttons'>";
                    echo "<a href='prestamos.php?id_deudor=" . htmlspecialchars($id_deudor) . "&edit_id=" . htmlspecialchars($row["id_prestamo"]) . "' class='edit'>Editar</a>";
                    echo "<a href='prestamos.php?id_deudor=" . htmlspecialchars($id_deudor) . "&delete_id=" . htmlspecialchars($row["id_prestamo"]) . "' class='delete' onclick=\"return confirm('¿Estás seguro de que quieres eliminar este préstamo?');\">Eliminar</a>";
                    echo "<a href='cancelaciones.php?id_prestamo=" . htmlspecialchars($row["id_prestamo"]) . "' class='view'>Ver Pagos</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8' style='text-align: center;'>No hay préstamos registrados para este deudor.</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
