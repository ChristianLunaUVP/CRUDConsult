<?php
include 'conexion.php';  // Conectar a la base de datos
include 'navbar.php';
ob_start();  // Inicia el almacenamiento en búfer de salida
$errorModal = ""; // Variable para manejar el mensaje de error del modal

// Agregar doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $especialidad = isset($_POST['especialidad']) ? trim($_POST['especialidad']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';

    // Validar que el correo no exista
    $checkEmail = $conexion->prepare("SELECT * FROM Doctor WHERE Email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmailResult = $checkEmail->get_result();

    if ($checkEmailResult->num_rows > 0) {
        $errorModal = "Error: El correo electrónico ya está registrado.";
    } else {
        // Insertar doctor si el correo no está registrado
        $stmt = $conexion->prepare("INSERT INTO Doctor (Nombre, Especialidad, Telefono, Email, Direccion) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nombre, $especialidad, $telefono, $email, $direccion);
        if ($stmt->execute()) {
            echo "<script>window.location.href='doctores.php';</script>";
            exit();
        } else {
            $errorModal = "Error al agregar el doctor.";
        }
        $stmt->close();
    }
    $checkEmail->close();
}

// Editar doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_editar'])) {
    $id = isset($_POST['id_editar']) ? $_POST['id_editar'] : '';
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $especialidad = isset($_POST['especialidad']) ? trim($_POST['especialidad']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';

    // Validar que el correo no exista (excepto el actual doctor)
    $checkEmail = $conexion->prepare("SELECT * FROM Doctor WHERE Email = ? AND ID_Doctor != ?");
    $checkEmail->bind_param("si", $email, $id);
    $checkEmail->execute();
    $checkEmailResult = $checkEmail->get_result();

    if ($checkEmailResult->num_rows > 0) {
        $errorModal = "Error: El correo electrónico ya está registrado.";
    } else {
        // Actualizar doctor si el correo no está registrado
        $stmt = $conexion->prepare("UPDATE Doctor SET Nombre = ?, Especialidad = ?, Telefono = ?, Email = ?, Direccion = ? WHERE ID_Doctor = ?");
        $stmt->bind_param("sssssi", $nombre, $especialidad, $telefono, $email, $direccion, $id);
        if ($stmt->execute()) {
            echo "<script>window.location.href='doctores.php';</script>";
            exit();
        } else {
            $errorModal = "Error al actualizar el doctor.";
        }
        $stmt->close();
    }
    $checkEmail->close();
}

// Eliminar doctor con manejo de excepción
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];
    try {
        $stmt = $conexion->prepare("DELETE FROM Doctor WHERE ID_Doctor = ?");
        $stmt->bind_param("i", $idEliminar);
        if ($stmt->execute()) {
            echo "<script>window.location.href='doctores.php';</script>";
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        // Capturar el error y guardarlo en la variable del modal
        $errorModal = "Error: No se puede eliminar este doctor porque está siendo utilizado en otras operaciones.";
        echo "<script>$('#errorModal').modal('show');</script>";
    }
    $stmt->close();
}

// Obtener doctores
$result = $conexion->query("SELECT * FROM Doctor");
ob_end_flush();  // Envía todo el contenido al navegador al final del archivo
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Doctores</title>
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
        <h2 class="mb-4 text-center">Gestión de Doctores</h2>
        
        <!-- Botón para agregar doctor -->
        <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addDoctorModal">
            <i class="fas fa-user-plus"></i> Agregar Doctor
        </button>

        <!-- Tabla de doctores -->
        <?php
        if ($result->num_rows > 0) {
            echo "<table class='table table-bordered table-striped'>";
            echo "<thead class='thead-dark'><tr><th>ID</th><th>Nombre</th><th>Especialidad</th><th>Teléfono</th><th>Correo</th><th>Dirección</th><th>Acciones</th></tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['ID_Doctor'] . "</td>";
                echo "<td>" . $row['Nombre'] . "</td>";
                echo "<td>" . $row['Especialidad'] . "</td>";
                echo "<td>" . $row['Telefono'] . "</td>";
                echo "<td>" . $row['Email'] . "</td>";
                echo "<td>" . $row['Direccion'] . "</td>";
                echo "<td>
                        <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editDoctorModal' 
                        data-id='" . $row['ID_Doctor'] . "' data-nombre='" . $row['Nombre'] . "' 
                        data-especialidad='" . $row['Especialidad'] . "' data-telefono='" . $row['Telefono'] . "' 
                        data-email='" . $row['Email'] . "' data-direccion='" . $row['Direccion'] . "'>
                            <i class='fas fa-edit'></i> Editar
                        </button>
                        <button class='btn btn-danger btn-sm' data-toggle='modal' data-target='#confirmDeleteModal' 
                        data-id='" . $row['ID_Doctor'] . "'>
                            <i class='fas fa-trash'></i> Eliminar
                        </button>
                    </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No se encontraron doctores.</p>";
        }
        ?>

    </div>

    <!-- Modal para agregar doctor -->
    <div class="modal fade" id="addDoctorModal" tabindex="-1" role="dialog" aria-labelledby="addDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDoctorModalLabel">Agregar Doctor</h5>
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
                            <label for="especialidad">Especialidad:</label>
                            <input type="text" class="form-control" id="especialidad" name="especialidad" required>
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
                    ¿Está seguro de eliminar este doctor?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="#" class="btn btn-danger" id="deleteDoctorButton">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edición de doctor -->
    <div class="modal fade" id="editDoctorModal" tabindex="-1" role="dialog" aria-labelledby="editDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDoctorModalLabel">Editar Doctor</h5>
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
                            <label for="edit_especialidad">Especialidad:</label>
                            <input type="text" class="form-control" id="edit_especialidad" name="especialidad" required>
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
        $('#editDoctorModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id');
            var nombre = button.data('nombre');
            var especialidad = button.data('especialidad');
            var telefono = button.data('telefono');
            var email = button.data('email');
            var direccion = button.data('direccion');
            
            var modal = $(this);
            modal.find('#edit_id').val(id);
            modal.find('#edit_nombre').val(nombre);
            modal.find('#edit_especialidad').val(especialidad);
            modal.find('#edit_telefono').val(telefono);
            modal.find('#edit_email').val(email);
            modal.find('#edit_direccion').val(direccion);
        });

        // Script para manejar la eliminación
        $('#confirmDeleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id');
            var modal = $(this);
            modal.find('#deleteDoctorButton').attr('href', 'doctores.php?eliminar=' + id);
        });
    </script>
</body>
</html>