<?php
require_once 'conexion.php';

$patients = $conexion->query("SELECT * FROM Paciente");

while ($patient = $patients->fetch_assoc()) {
    echo "<option value='" . $patient['ID_Paciente'] . "'>" . $patient['Nombre'] . "</option>";
}
?>