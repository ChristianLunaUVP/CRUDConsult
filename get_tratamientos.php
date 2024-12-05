<?php
require_once 'conexion.php';

$tratamientos = $conexion->query("SELECT ID_Tratamiento, Tipo_Tratamiento FROM Tratamiento");

while ($tratamiento = $tratamientos->fetch_assoc()) {
    echo "<option value='" . $tratamiento['ID_Tratamiento'] . "'>" . $tratamiento['Tipo_Tratamiento'] . "</option>";
}
?>