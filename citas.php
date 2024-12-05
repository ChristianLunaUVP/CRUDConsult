<?php
// Incluir la conexión a la base de datos
require_once 'conexion.php';  // Asegura que $conexion esté disponible
include 'navbar.php';  // Incluir la barra de navegación
ob_start();  // Inicia el almacenamiento en búfer de salida
$errorModal = ""; // Variable para manejar el mensaje de error del modal

// Manejo de formularios (Agregar/Editar/Eliminar)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {  // Agregar
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        $motivo = $_POST['motivo'];
        $id_doctor = $_POST['id_doctor'];
        $id_paciente = $_POST['id_paciente'];

        $sql = "INSERT INTO Cita (Fecha, Hora, Motivo, ID_Doctor, ID_Paciente) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssss", $fecha, $hora, $motivo, $id_doctor, $id_paciente);
        if ($stmt->execute()) {
            echo "<script>window.location.href='citas.php';</script>";
            exit();
        } else {
            $errorModal = "Error al agregar la cita.";
        }
        $stmt->close();
    }

    if (isset($_POST['edit'])) {  // Editar
        $id_cita = $_POST['id_cita'];
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        $motivo = $_POST['motivo'];
        $id_doctor = $_POST['id_doctor'];
        $id_paciente = $_POST['id_paciente'];

        $sql = "UPDATE Cita SET Fecha=?, Hora=?, Motivo=?, ID_Doctor=?, ID_Paciente=? WHERE ID_Cita=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssi", $fecha, $hora, $motivo, $id_doctor, $id_paciente, $id_cita);
        if ($stmt->execute()) {
            echo "<script>window.location.href='citas.php';</script>";
            exit();
        } else {
            $errorModal = "Error al actualizar la cita.";
        }
        $stmt->close();
    }

    if (isset($_POST['delete'])) {  // Eliminar
        $id_cita = $_POST['id_cita'];
        try {
            $sql = "DELETE FROM Cita WHERE ID_Cita=?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id_cita);
            if ($stmt->execute()) {
                echo "<script>window.location.href='citas.php';</script>";
                exit();
            }
        } catch (mysqli_sql_exception $e) {
            $errorModal = "Error: No se puede eliminar esta cita porque está siendo utilizada en otras operaciones.";
            echo "<script>$('#errorModal').modal('show');</script>";
        }
        $stmt->close();
    }
}

// Obtener todos los doctores y pacientes para los formularios
$doctors = $conexion->query("SELECT * FROM Doctor");
$patients = $conexion->query("SELECT * FROM Paciente");

// Obtener todas las citas
$result = $conexion->query("SELECT C.ID_Cita, C.Fecha, C.Hora, C.Motivo, D.Nombre AS Doctor, P.Nombre AS Paciente, C.ID_Doctor, C.ID_Paciente 
                            FROM Cita C 
                            JOIN Doctor D ON C.ID_Doctor = D.ID_Doctor
                            JOIN Paciente P ON C.ID_Paciente = P.ID_Paciente");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Citas</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Animaciones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body {
            background-color: #f1f5f8;
            font-family: 'Arial', sans-serif;
        }
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 12px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        h2 {
            color: #3a3f47;
            font-weight: 700;
        }
        .table {
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary, .btn-warning, .btn-danger {
            transition: all 0.3s ease;
        }
        .btn-primary:hover, .btn-warning:hover, .btn-danger:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }
        .modal-content {
            border-radius: 12px;
            border: none;
        }
        .form-control, .modal-body input, .modal-body select {
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .form-control:focus {
            box-shadow: 0 0 5px rgba(68, 129, 255, 0.6);
        }
        .modal-header {
            background-color: #17a2b8;
            color: white;
            font-weight: bold;
        }
        .modal-footer .btn-secondary {
            background-color: #ddd;
            border: none;
        }
        .modal-body select {
            height: calc(2.25rem + 10px);
        }
    </style>
</head>
<body>
    <div class="container mt-5 animate__animated animate__fadeIn">
        <h2 class="mb-4 text-center">Gestión de Citas</h2>
        
        <!-- Botón para agregar cita -->
        <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addCitaModal">
            <i class="fas fa-calendar-plus"></i> Agregar Cita
        </button>

        <!-- Tabla de citas -->
        <?php
        if ($result->num_rows > 0) {
            echo "<table class='table table-bordered table-striped'>";
            echo "<thead class='thead-dark'><tr><th>ID</th><th>Fecha</th><th>Hora</th><th>Motivo</th><th>Doctor</th><th>Paciente</th><th>Acciones</th></tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['ID_Cita'] . "</td>";
                echo "<td>" . $row['Fecha'] . "</td>";
                echo "<td>" . $row['Hora'] . "</td>";
                echo "<td>" . $row['Motivo'] . "</td>";
                echo "<td>" . $row['Doctor'] . "</td>";
                echo "<td>" . $row['Paciente'] . "</td>";
                echo "<td>
                        <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editCitaModal' 
                        data-id='" . $row['ID_Cita'] . "' data-fecha='" . $row['Fecha'] . "' 
                        data-hora='" . $row['Hora'] . "' data-motivo='" . $row['Motivo'] . "' 
                        data-id_doctor='" . $row['ID_Doctor'] . "' data-id_paciente='" . $row['ID_Paciente'] . "'>
                            <i class='fas fa-edit'></i> Editar
                        </button>
                        <button class='btn btn-danger btn-sm' data-toggle='modal' data-target='#confirmDeleteModal' 
                        data-id='" . $row['ID_Cita'] . "'>
                            <i class='fas fa-trash'></i> Eliminar
                        </button>
                    </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No se encontraron citas.</p>";
        }
        ?>

    </div>

    <!-- Modal para agregar cita -->
    <div class="modal fade" id="addCitaModal" tabindex="-1" role="dialog" aria-labelledby="addCitaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCitaModalLabel">Agregar Cita</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fecha">Fecha:</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required>
                        </div>
                        <div class="form-group">
                            <label for="hora">Hora:</label>
                            <input type="time" class="form-control" id="hora" name="hora" required>
                        </div>
                        <div class="form-group">
                            <label for="motivo">Motivo:</label>
                            <input type="text" class="form-control" id="motivo" name="motivo" required>
                        </div>
                        <div class="form-group">
                            <label for="id_doctor">Doctor:</label>
                            <select class="form-control" id="id_doctor" name="id_doctor" required>
                                <?php while($doctor = $doctors->fetch_assoc()): ?>
                                    <option value="<?= $doctor['ID_Doctor']; ?>"><?= $doctor['Nombre']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_paciente">Paciente:</label>
                            <select class="form-control" id="id_paciente" name="id_paciente" required>
                                <?php while($patient = $patients->fetch_assoc()): ?>
                                    <option value="<?= $patient['ID_Paciente']; ?>"><?= $patient['Nombre']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" name="add">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de error -->
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo $errorModal; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmar eliminación -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de eliminar esta cita?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="#" class="btn btn-danger" id="deleteCitaButton">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edición de cita -->
    <div class="modal fade" id="editCitaModal" tabindex="-1" role="dialog" aria-labelledby="editCitaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCitaModalLabel">Editar Cita</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_cita" id="edit_id">
                        <div class="form-group">
                            <label for="edit_fecha">Fecha:</label>
                            <input type="date" class="form-control" id="edit_fecha" name="fecha" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_hora">Hora:</label>
                            <input type="time" class="form-control" id="edit_hora" name="hora" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_motivo">Motivo:</label>
                            <input type="text" class="form-control" id="edit_motivo" name="motivo" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_id_doctor">Doctor:</label>
                            <select class="form-control" id="edit_id_doctor" name="id_doctor" required>
                                <?php while($doctor = $doctors->fetch_assoc()): ?>
                                    <option value="<?= $doctor['ID_Doctor']; ?>"><?= $doctor['Nombre']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_id_paciente">Paciente:</label>
                            <select class="form-control" id="edit_id_paciente" name="id_paciente" required>
                                <?php while($patient = $patients->fetch_assoc()): ?>
                                    <option value="<?= $patient['ID_Paciente']; ?>"><?= $patient['Nombre']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" name="edit">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Script para manejar la edición
        $('#editCitaModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id');
            var fecha = button.data('fecha');
            var hora = button.data('hora');
            var motivo = button.data('motivo');
            var id_doctor = button.data('id_doctor');
            var id_paciente = button.data('id_paciente');
            
            var modal = $(this);
            modal.find('#edit_id').val(id);
            modal.find('#edit_fecha').val(fecha);
            modal.find('#edit_hora').val(hora);
            modal.find('#edit_motivo').val(motivo);

            // Llenar los select de doctores y pacientes
            $.ajax({
                url: 'get_doctors.php',
                method: 'GET',
                success: function(data) {
                    modal.find('#edit_id_doctor').html(data);
                    modal.find('#edit_id_doctor').val(id_doctor);
                }
            });

            $.ajax({
                url: 'get_patients.php',
                method: 'GET',
                success: function(data) {
                    modal.find('#edit_id_paciente').html(data);
                    modal.find('#edit_id_paciente').val(id_paciente);
                }
            });
        });

        // Script para manejar la eliminación
        $('#confirmDeleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id');
            var modal = $(this);
            modal.find('#deleteCitaButton').attr('href', 'citas.php?eliminar=' + id);
        });
    </script>
</body>
</html>