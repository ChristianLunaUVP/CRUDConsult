<?php
require_once 'conexion.php';
$doctors = $conexion->query("SELECT * FROM Doctor");
$options = "";
while ($doctor = $doctors->fetch_assoc()) {
    $options .= "<option value='{$doctor['ID_Doctor']}'>{$doctor['Nombre']}</option>";
}
echo $options;
?>