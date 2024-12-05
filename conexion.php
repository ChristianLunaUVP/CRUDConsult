<?php
// Configuración de la base de datos
$host = 'localhost'; // El host de tu base de datos (normalmente 'localhost')
$usuario = 'root';   // Tu usuario de la base de datos (por defecto es 'root' en localhost)
$contraseña = '';    // Tu contraseña de la base de datos (deja vacío si no tienes contraseña en localhost)
$nombre_base_datos = 'crudconsult';  // El nombre de tu base de datos

// Crear la conexión
$conexion = mysqli_connect($host, $usuario, $contraseña, $nombre_base_datos);

// Verificar si la conexión fue exitosa
if (!$conexion) {
    die("Conexión fallida: " . mysqli_connect_error());
}
?>
