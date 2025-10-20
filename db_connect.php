<?php
// db_connect.php - Archivo para la conexión a la base de datos

// IMPORTANTE: Asegúrate de que estos valores coincidan con tu configuración de MySQL
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "negocio_prestamos"; 

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    // Si la conexión falla, se muestra un mensaje de error y el script se detiene
    die("Conexión fallida: " . $conn->connect_error);
}
?>
