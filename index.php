<?php
// index.php - Página principal para gestionar deudores
include 'db_connect.php'; 

$message = "";
$editing = false;
$deudor_data = [];
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Lógica para AGREGAR un nuevo deudor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_deudor'])) {
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];

    $sql = "INSERT INTO Deudor (nombre, direccion, telefono) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nombre, $direccion, $telefono);
    if ($stmt->execute()) {
        $message = "✅ Deudor agregado exitosamente.";
    } else {
        $message = "❌ Error al agregar el deudor: " . $stmt->error;
    }
    $stmt->close();
    header("Location: index.php?message=" . urlencode($message));
    exit();
}

// Lógica para ELIMINAR un deudor
if (isset($_GET['delete_id'])) {
    $id_deudor = (int)$_GET['delete_id'];
    // Primero, eliminar préstamos y cancelaciones asociados
    $conn->begin_transaction();
    try {
        $sql_prestamos = "DELETE FROM Prestamo WHERE id_deudor = ?";
        $stmt_prestamos = $conn->prepare($sql_prestamos);
        $stmt_prestamos->bind_param("i", $id_deudor);
        $stmt_prestamos->execute();

        $sql_deudor = "DELETE FROM Deudor WHERE id_deudor = ?";
        $stmt_deudor = $conn->prepare($sql_deudor);
        $stmt_deudor->bind_param("i", $id_deudor);
        $stmt_deudor->execute();

        $conn->commit();
        $message = "✅ Deudor y sus registros eliminados exitosamente.";
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $message = "❌ Error al eliminar el deudor: " . $e->getMessage();
    }
    header("Location: index.php?message=" . urlencode($message));
    exit();
}

// Lógica para EDITAR un deudor (cargar datos en el formulario)
if (isset($_GET['edit_id'])) {
    $editing = true;
    $id_deudor = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM Deudor WHERE id_deudor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_deudor);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $deudor_data = $result->fetch_assoc();
    } else {
        $message = "❌ Deudor no encontrado.";
        $editing = false;
    }
    $stmt->close();
}

// Lógica para ACTUALIZAR un deudor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_deudor'])) {
    $id_deudor = $_POST['id_deudor'];
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];

    $sql = "UPDATE Deudor SET nombre = ?, direccion = ?, telefono = ? WHERE id_deudor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nombre, $direccion, $telefono, $id_deudor);
    if ($stmt->execute()) {
        $message = "✅ Deudor actualizado exitosamente.";
    } else {
        $message = "❌ Error al actualizar el deudor: " . $stmt->error;
    }
    $stmt->close();
    header("Location: index.php?message=" . urlencode($message));
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
    <title>Gestión de Deudores | CRM de Préstamos</title>
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

        .form-section { border: 1px solid var(--border-light); padding: 30px; border-radius: 12px; margin-bottom: 30px; background: #fafbfc; }
        .form-section h3 { margin-top: 0; color: var(--primary-color); font-size: 1.5em; }
        form { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; transition: border-color 0.3s; }
        .form-group input:focus { outline: none; border-color: var(--primary-color); }
        .form-buttons { grid-column: span 3; display: flex; gap: 15px; justify-content: flex-end; }
        .form-buttons button, .form-buttons a { padding: 12px 25px; color: var(--text-light); border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 1em; text-decoration: none; transition: all 0.3s ease; }
        .form-buttons button.primary { background-color: var(--primary-color); }
        .form-buttons button.primary:hover { background-color: #3476c2; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        .form-buttons a.cancel { background-color: #6C7A89; }
        .form-buttons a.cancel:hover { background-color: #56616e; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }

        .search-section { display: flex; justify-content: center; margin-bottom: 30px; gap: 15px; }
        .search-section input { width: 400px; max-width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; }
        .search-section button { padding: 12px 25px; background-color: var(--primary-color); color: var(--text-light); border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background-color 0.3s ease; }
        .search-section button:hover { background-color: #3476c2; }
        
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
        .main-navigation a.active { color: var(--primary-color); border-bottom-color: var(--primary-color); }

        @media (max-width: 768px) { 
            .container { padding: 15px; margin: 15px; }
            form { grid-template-columns: 1fr; } 
            .form-buttons { flex-direction: column; } 
            .search-section { flex-direction: column; }
            .search-section input, .search-section button { width: 100%; }
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { border: 1px solid var(--border-light); margin-bottom: 10px; border-radius: 8px; }
            td { border-bottom: none; position: relative; padding-left: 50%; text-align: right; }
            td:before { position: absolute; left: 6px; width: 45%; padding-right: 10px; white-space: nowrap; text-align: left; font-weight: 600; }
            td:nth-of-type(1):before { content: "ID Deudor"; }
            td:nth-of-type(2):before { content: "Nombre"; }
            td:nth-of-type(3):before { content: "Dirección"; }
            td:nth-of-type(4):before { content: "Teléfono"; }
            td:nth-of-type(5):before { content: "Acciones"; }
            .action-buttons { justify-content: flex-end; }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Gestión de Deudores</h1>
    </header>

    <div class="main-navigation">
        <a href="dashboard.php">Panel de Control</a>
        <a href="index.php" class="active">Gestión de Deudores</a>
    </div>
    
    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="form-section">
        <h3><?php echo $editing ? 'Editar Deudor' : 'Agregar Nuevo Deudor'; ?></h3>
        <form action="index.php" method="POST">
            <?php if ($editing): ?>
                <input type="hidden" name="id_deudor" value="<?php echo htmlspecialchars($deudor_data['id_deudor']); ?>">
                <input type="hidden" name="update_deudor" value="1">
            <?php else: ?>
                <input type="hidden" name="add_deudor" value="1">
            <?php endif; ?>

            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($deudor_data['nombre'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($deudor_data['direccion'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($deudor_data['telefono'] ?? ''); ?>">
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="primary"><?php echo $editing ? 'Actualizar Deudor' : 'Agregar Deudor'; ?></button>
                <?php if ($editing): ?>
                    <a href="index.php" class="cancel">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="search-section">
        <form action="index.php" method="GET">
            <input type="text" name="search" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <h2>Lista de Deudores</h2>
    <table>
        <thead>
            <tr>
                <th>ID Deudor</th>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consulta de deudores con funcionalidad de búsqueda
            $sql = "SELECT * FROM Deudor";
            if (!empty($search)) {
                $sql .= " WHERE nombre LIKE ?";
            }
            $sql .= " ORDER BY id_deudor DESC";
            
            $stmt = $conn->prepare($sql);
            if (!empty($search)) {
                $search_param = "%" . $search . "%";
                $stmt->bind_param("s", $search_param);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["id_deudor"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["nombre"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["direccion"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["telefono"]) . "</td>";
                    echo "<td class='action-buttons'>";
                    echo "<a href='index.php?edit_id=" . htmlspecialchars($row["id_deudor"]) . "' class='edit'>Editar</a>";
                    echo "<a href='index.php?delete_id=" . htmlspecialchars($row["id_deudor"]) . "' class='delete' onclick=\"return confirm('¿Estás seguro de que quieres eliminar a este deudor y todos sus préstamos y cancelaciones?');\">Eliminar</a>";
                    echo "<a href='prestamos.php?id_deudor=" . htmlspecialchars($row["id_deudor"]) . "' class='view'>Ver Préstamos</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align: center;'>No se encontraron deudores.</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
