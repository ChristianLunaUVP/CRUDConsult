<?php
// Incluir la conexión a la base de datos
include 'conexion.php';
include 'navbar.php';  // Incluye la barra de navegación

// Variable para manejar el mensaje de error del modal
$errorModal = "";

// Agregar historial de tratamiento
if (isset($_POST['add'])) {
    $id_tratamiento = $_POST['id_tratamiento'];
    $id_paciente = $_POST['id_paciente'];
    $observaciones = $_POST['observaciones'];
    $fecha = $_POST['fecha'];

    $sql = "INSERT INTO Historial_Tratamiento (ID_Tratamiento, ID_Paciente, Observaciones, Fecha) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiss", $id_tratamiento, $id_paciente, $observaciones, $fecha);
    if ($stmt->execute()) {
        echo "<script>window.location.href='historial_tratamiento.php';</script>";
        exit();
    } else {
        $errorModal = "Error al agregar el historial de tratamiento.";
    }
    $stmt->close();
}

// Eliminar historial de tratamiento
if (isset($_GET['delete'])) {
    $id_historia = $_GET['delete'];
    $sql = "DELETE FROM Historial_Tratamiento WHERE ID_Historia = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_historia);
    if ($stmt->execute()) {
        echo "<script>window.location.href='historial_tratamiento.php';</script>";
        exit();
    } else {
        $errorModal = "Error al eliminar el historial de tratamiento.";
    }
    $stmt->close();
}

// Editar historial de tratamiento
if (isset($_POST['edit'])) {
    $id_historia = $_POST['id_historia'];
    $id_tratamiento = $_POST['id_tratamiento'];
    $id_paciente = $_POST['id_paciente'];
    $observaciones = $_POST['observaciones'];
    $fecha = $_POST['fecha'];

    $sql = "UPDATE Historial_Tratamiento SET ID_Tratamiento = ?, ID_Paciente = ?, Observaciones = ?, Fecha = ? 
            WHERE ID_Historia = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iissi", $id_tratamiento, $id_paciente, $observaciones, $fecha, $id_historia);
    if ($stmt->execute()) {
        echo "<script>window.location.href='historial_tratamiento.php';</script>";
        exit();
    } else {
        $errorModal = "Error al actualizar el historial de tratamiento.";
    }
    $stmt->close();
}

// Obtener todos los historiales de tratamiento
$sql = "SELECT ht.ID_Historia, ht.Observaciones, ht.Fecha, t.Tipo_Tratamiento, p.Nombre AS Paciente, ht.ID_Tratamiento, ht.ID_Paciente
        FROM Historial_Tratamiento ht
        JOIN Tratamiento t ON ht.ID_Tratamiento = t.ID_Tratamiento
        JOIN Paciente p ON ht.ID_Paciente = p.ID_Paciente";
$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Tratamientos</title>
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
        <h2 class="mb-4 text-center">Historial de Tratamientos</h2>

        <!-- Botón para abrir el modal de agregar historial de tratamiento -->
        <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addHistorialModal">
            <i class="fas fa-plus"></i> Agregar Historial
        </button>

        <!-- Tabla de historiales de tratamiento -->
        <?php
        if ($result->num_rows > 0) {
            echo "<table class='table table-bordered table-striped'>";
            echo "<thead class='thead-dark'><tr><th>ID</th><th>Tratamiento</th><th>Paciente</th><th>Observaciones</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['ID_Historia'] . "</td>";
                echo "<td>" . $row['Tipo_Tratamiento'] . "</td>";
                echo "<td>" . $row['Paciente'] . "</td>";
                echo "<td>" . $row['Observaciones'] . "</td>";
                echo "<td>" . $row['Fecha'] . "</td>";
                echo "<td>
                        <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editHistorialModal' 
                        data-id='" . $row['ID_Historia'] . "' data-id_tratamiento='" . $row['ID_Tratamiento'] . "' 
                        data-id_paciente='" . $row['ID_Paciente'] . "' data-observaciones='" . $row['Observaciones'] . "' 
                        data-fecha='" . $row['Fecha'] . "'>
                            <i class='fas fa-edit'></i> Editar
                        </button>
                        <button class='btn btn-danger btn-sm' data-toggle='modal' data-target='#confirmDeleteModal' 
                        data-id='" . $row['ID_Historia'] . "'>
                            <i class='fas fa-trash'></i> Eliminar
                        </button>
                    </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No se encontraron historiales de tratamiento.</p>";
        }
        ?>

    </div>

    <!-- Modal para agregar historial de tratamiento -->
    <div class="modal fade" id="addHistorialModal" tabindex="-1" role="dialog" aria-labelledby="addHistorialModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addHistorialModalLabel">Agregar Historial</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="id_tratamiento">Tratamiento</label>
                            <select class="form-control" id="id_tratamiento" name="id_tratamiento" required>
                                <!-- Opciones se llenarán con AJAX -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_paciente">Paciente</label>
                            <select class="form-control" id="id_paciente" name="id_paciente" required>
                                <!-- Opciones se llenarán con AJAX -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <input type="text" class="form-control" id="observaciones" name="observaciones" required>
                        </div>
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required>
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
                    ¿Está seguro de eliminar este historial de tratamiento?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="#" class="btn btn-danger" id="deleteHistorialButton">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edición de historial de tratamiento -->
    <div class="modal fade" id="editHistorialModal" tabindex="-1" role="dialog" aria-labelledby="editHistorialModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editHistorialModalLabel">Editar Historial</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_historia" id="edit_id">
                        <div class="form-group">
                            <label for="edit_id_tratamiento">Tratamiento</label>
                            <select class="form-control" id="edit_id_tratamiento" name="id_tratamiento" required>
                                <!-- Opciones se llenarán con AJAX -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_id_paciente">Paciente</label>
                            <select class="form-control" id="edit_id_paciente" name="id_paciente" required>
                                <!-- Opciones se llenarán con AJAX -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_observaciones">Observaciones</label>
                            <input type="text" class="form-control" id="edit_observaciones" name="observaciones" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_fecha">Fecha</label>
                            <input type="date" class="form-control" id="edit_fecha" name="fecha" required>
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
        $('#editHistorialModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id');
            var id_tratamiento = button.data('id_tratamiento');
            var id_paciente = button.data('id_paciente');
            var observaciones = button.data('observaciones');
            var fecha = button.data('fecha');
            
            var modal = $(this);
            modal.find('#edit_id').val(id);
            modal.find('#edit_id_tratamiento').val(id_tratamiento);
            modal.find('#edit_id_paciente').val(id_paciente);
            modal.find('#edit_observaciones').val(observaciones);
            modal.find('#edit_fecha').val(fecha);

            // Llenar los select de tratamientos y pacientes
            $.ajax({
                url: 'get_tratamientos.php',
                method: 'GET',
                success: function(data) {
                    modal.find('#edit_id_tratamiento').html(data);
                    modal.find('#edit_id_tratamiento').val(id_tratamiento);
                }
            });

            $.ajax({
                url: 'get_pacientes.php',
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
            modal.find('#deleteHistorialButton').attr('href', 'historial_tratamiento.php?delete=' + id);
        });

        // Llenar los selects de tratamientos y pacientes al abrir el modal de agregar
        $('#addHistorialModal').on('show.bs.modal', function (event) {
            var modal = $(this);

            $.ajax({
                url: 'get_tratamientos.php',
                method: 'GET',
                success: function(data) {
                    modal.find('#id_tratamiento').html(data);
                }
            });

            $.ajax({
                url: 'get_pacientes.php',
                method: 'GET',
                success: function(data) {
                    modal.find('#id_paciente').html(data);
                }
            });
        });
    </script>
</body>
</html>