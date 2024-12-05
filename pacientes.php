<?php
include 'conexion.php';  // Conectar a la base de datos
include 'navbar.php';     // Incluir la barra de navegación
ob_start();  // Inicia el almacenamiento en búfer de salida
$errorModal = ""; // Variable para manejar el mensaje de error del modal

// Agregar paciente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre']) && !isset($_POST['id_editar'])) {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $edad = isset($_POST['edad']) ? trim($_POST['edad']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';

    $checkEmail = $conexion->prepare("SELECT * FROM Paciente WHERE Email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmailResult = $checkEmail->get_result();

    if ($checkEmailResult->num_rows > 0) {
        $errorModal = "Error: El correo electrónico ya está registrado.";
    } else {
        $stmt = $conexion->prepare("INSERT INTO Paciente (Nombre, Edad, Telefono, Email, Direccion) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $nombre, $edad, $telefono, $email, $direccion);
        if ($stmt->execute()) {
            echo "<script>window.location.href='pacientes.php';</script>";
            exit();
        } else {
            $errorModal = "Error al agregar paciente: " . $stmt->error;
        }
        $stmt->close();
    }

    $checkEmail->close();
}

// Editar paciente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_editar'])) {
    $id = isset($_POST['id_editar']) ? $_POST['id_editar'] : '';
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $edad = isset($_POST['edad']) ? trim($_POST['edad']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';

    $checkEmail = $conexion->prepare("SELECT * FROM Paciente WHERE Email = ? AND ID_Paciente != ?");
    $checkEmail->bind_param("si", $email, $id);
    $checkEmail->execute();
    $checkEmailResult = $checkEmail->get_result();

    if ($checkEmailResult->num_rows > 0) {
        $errorModal = "Error: El correo electrónico ya está registrado.";
    } else {
        $stmt = $conexion->prepare("UPDATE Paciente SET Nombre = ?, Edad = ?, Telefono = ?, Email = ?, Direccion = ? WHERE ID_Paciente = ?");
        $stmt->bind_param("sisssi", $nombre, $edad, $telefono, $email, $direccion, $id);
        if ($stmt->execute()) {
            echo "<script>window.location.href='pacientes.php';</script>";
            exit();
        } else {
            $errorModal = "Error al actualizar paciente: " . $stmt->error;
        }
        $stmt->close();
    }

    $checkEmail->close();
}

// Eliminar paciente
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];
    try {
        $stmt = $conexion->prepare("DELETE FROM Paciente WHERE ID_Paciente = ?");
        $stmt->bind_param("i", $idEliminar);
        if ($stmt->execute()) {
            echo "<script>window.location.href='pacientes.php';</script>";
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        $errorModal = "Error: No se puede eliminar este paciente porque está siendo utilizado en otras operaciones.";
        echo "<script>$('#errorModal').modal('show');</script>";
    }
    $stmt->close();
}

// Obtener pacientes
$result = $conexion->query("SELECT * FROM Paciente");
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pacientes</title>
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
        .form-control, .modal-body input {
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
    </style>
</head>
<body>
    <div class="container mt-5 animate__animated animate__fadeIn">
        <h2 class="mb-4 text-center">Gestión de Pacientes</h2>
        
        <!-- Botón para agregar paciente -->
        <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addPacienteModal">
            <i class="fas fa-user-plus"></i> Agregar Paciente
        </button>

        <!-- Tabla de pacientes -->
        <?php
        if ($result->num_rows > 0) {
            echo "<table class='table table-bordered table-striped'>";
            echo "<thead class='thead-dark'><tr><th>ID</th><th>Nombre</th><th>Edad</th><th>Teléfono</th><th>Correo</th><th>Dirección</th><th>Acciones</th></tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['ID_Paciente'] . "</td>";
                echo "<td>" . $row['Nombre'] . "</td>";
                echo "<td>" . $row['Edad'] . "</td>";
                echo "<td>" . $row['Telefono'] . "</td>";
                echo "<td>" . $row['Email'] . "</td>";
                echo "<td>" . $row['Direccion'] . "</td>";
                echo "<td>
                        <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editPacienteModal' 
                        data-id='" . $row['ID_Paciente'] . "' data-nombre='" . $row['Nombre'] . "' 
                        data-edad='" . $row['Edad'] . "' data-telefono='" . $row['Telefono'] . "' 
                        data-email='" . $row['Email'] . "' data-direccion='" . $row['Direccion'] . "'>
                            <i class='fas fa-edit'></i> Editar
                        </button>
                        <button class='btn btn-danger btn-sm' data-toggle='modal' data-target='#confirmDeleteModal' 
                        data-id='" . $row['ID_Paciente'] . "'>
                            <i class='fas fa-trash'></i> Eliminar
                        </button>
                    </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No se encontraron pacientes.</p>";
        }
        ?>

    </div>

    <!-- Modal para agregar paciente -->
    <div class="modal fade" id="addPacienteModal" tabindex="-1" role="dialog" aria-labelledby="addPacienteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPacienteModalLabel">Agregar Paciente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nombre">Nombre:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="edad">Edad:</label>
                            <input type="number" class="form-control" id="edad" name="edad" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Correo Electrónico:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="direccion">Dirección:</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Agregar</button>
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
                    ¿Está seguro de eliminar este paciente?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="#" class="btn btn-danger" id="deletePatientButton">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edición de paciente -->
    <div class="modal fade" id="editPacienteModal" tabindex="-1" role="dialog" aria-labelledby="editPacienteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPacienteModalLabel">Editar Paciente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_editar" id="edit_id">
                        <div class="form-group">
                            <label for="edit_nombre">Nombre:</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_edad">Edad:</label>
                            <input type="number" class="form-control" id="edit_edad" name="edad" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_telefono">Teléfono:</label>
                            <input type="text" class="form-control" id="edit_telefono" name="telefono" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Correo Electrónico:</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_direccion">Dirección:</label>
                            <input type="text" class="form-control" id="edit_direccion" name="direccion" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Script para manejar la edición
        $('#editPacienteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id');
            var nombre = button.data('nombre');
            var edad = button.data('edad');
            var telefono = button.data('telefono');
            var email = button.data('email');
            var direccion = button.data('direccion');
            
            var modal = $(this);
            modal.find('#edit_id').val(id);
            modal.find('#edit_nombre').val(nombre);
            modal.find('#edit_edad').val(edad);
            modal.find('#edit_telefono').val(telefono);
            modal.find('#edit_email').val(email);
            modal.find('#edit_direccion').val(direccion);
        });

        // Script para manejar la eliminación
        $('#confirmDeleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id');
            var modal = $(this);
            modal.find('#deletePatientButton').attr('href', 'pacientes.php?eliminar=' + id);
        });
    </script>
</body>
</html>
