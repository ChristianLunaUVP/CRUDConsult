<?php
require_once 'conexion.php';

$pacientes = $conexion->query("SELECT ID_Paciente, Nombre FROM Paciente");

while ($paciente = $pacientes->fetch_assoc()) {
    echo "<option value='" . $paciente['ID_Paciente'] . "'>" . $paciente['Nombre'] . "</option>";
}
?>