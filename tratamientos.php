<?php
// Incluir la conexión a la base de datos
include 'conexion.php';
include 'navbar.php';  // Incluye la barra de navegación
ob_start();  // Inicia el almacenamiento en búfer de salida
$errorModal = ""; // Variable para manejar el mensaje de error del modal

// Agregar tratamiento
if (isset($_POST['add'])) {
    $tipo_tratamiento = $_POST['tipo_tratamiento'];
    $costo = $_POST['costo'];
    $duracion = $_POST['duracion'];

    // Asegúrate de que las columnas coincidan con los nombres en la base de datos
    $sql = "INSERT INTO Tratamiento (Tipo_Tratamiento, Costo, Duracion) 
            VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $tipo_tratamiento, $costo, $duracion);
    if ($stmt->execute()) {
        echo "<script>window.location.href='tratamientos.php';</script>";
        exit();
    } else {
        $errorModal = "Error al agregar el tratamiento.";
    }
    $stmt->close();
}

// Eliminar tratamiento
if (isset($_GET['delete'])) {
    $id_tratamiento = $_GET['delete'];
    $sql = "DELETE FROM Tratamiento WHERE ID_Tratamiento = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_tratamiento);
    if ($stmt->execute()) {
        echo "<script>window.location.href='tratamientos.php';</script>";
        exit();
    } else {
        $errorModal = "Error al eliminar el tratamiento.";
    }
    $stmt->close();
}

// Editar tratamiento
if (isset($_POST['edit'])) {
    $id_tratamiento = $_POST['id_tratamiento'];
    $tipo_tratamiento = $_POST['tipo_tratamiento'];
    $costo = $_POST['costo'];
    $duracion = $_POST['duracion'];

    // Actualizar tratamiento
    $sql = "UPDATE Tratamiento SET Tipo_Tratamiento = ?, Costo = ?, Duracion = ? WHERE ID_Tratamiento = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssi", $tipo_tratamiento, $costo, $duracion, $id_tratamiento);
    if ($stmt->execute()) {
        echo "<script>window.location.href='tratamientos.php';</script>";
        exit();
    } else {
        $errorModal = "Error al actualizar el tratamiento.";
    }
    $stmt->close();
}

// Obtener todos los tratamientos
$sql = "SELECT ID_Tratamiento, Tipo_Tratamiento, Costo, Duracion FROM Tratamiento";
$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tratamientos</title>
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
        <h2 class="mb-4 text-center">Tratamientos</h2>

        <!-- Botón para abrir el modal de agregar tratamiento -->
        <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addTreatmentModal">
            <i class="fas fa-plus"></i> Agregar Tratamiento
        </button>

        <!-- Tabla de tratamientos -->
        <?php
        if ($result->num_rows > 0) {
            echo "<table class='table table-bordered table-striped'>";
            echo "<thead class='thead-dark'><tr><th>ID</th><th>Tipo de Tratamiento</th><th>Costo</th><th>Duración</th><th>Acciones</th></tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['ID_Tratamiento'] . "</td>";
                echo "<td>" . $row['Tipo_Tratamiento'] . "</td>";
                echo "<td>" . $row['Costo'] . "</td>";
                echo "<td>" . $row['Duracion'] . "</td>";
                echo "<td>
                        <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editTreatmentModal' 
                        data-id='" . $row['ID_Tratamiento'] . "' data-tipo_tratamiento='" . $row['Tipo_Tratamiento'] . "' 
                        data-costo='" . $row['Costo'] . "' data-duracion='" . $row['Duracion'] . "'>
                            <i class='fas fa-edit'></i> Editar
                        </button>
                        <button class='btn btn-danger btn-sm' data-toggle='modal' data-target='#confirmDeleteModal' 
                        data-id='" . $row['ID_Tratamiento'] . "'>
                            <i class='fas fa-trash'></i> Eliminar
                        </button>
                    </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No se encontraron tratamientos.</p>";
        }
        ?>

    </div>

    <!-- Modal para agregar tratamiento -->
    <div class="modal fade" id="addTreatmentModal" tabindex="-1" role="dialog" aria-labelledby="addTreatmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTreatmentModalLabel">Agregar Tratamiento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="tipo_tratamiento">Tipo de Tratamiento</label>
                            <input type="text" class="form-control" id="tipo_tratamiento" name="tipo_tratamiento" required>
                        </div>
                        <div class="form-group">
                            <label for="costo">Costo</label>
                            <input type="number" class="form-control" id="costo" name="costo" required step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="duracion">Duración</label>
                            <input type="text" class="form-control" id="duracion" name="duracion">
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
                    ¿Está seguro de eliminar este tratamiento?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="#" class="btn btn-danger" id="deleteTreatmentButton">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edición de tratamiento -->
    <div class="modal fade" id="editTreatmentModal" tabindex="-1" role="dialog" aria-labelledby="editTreatmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTreatmentModalLabel">Editar Tratamiento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_tratamiento" id="edit_id">
                        <div class="form-group">
                            <label for="edit_tipo_tratamiento">Tipo de Tratamiento</label>
                            <input type="text" class="form-control" id="edit_tipo_tratamiento" name="tipo_tratamiento" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_costo">Costo</label>
                            <input type="number" class="form-control" id="edit_costo" name="costo" required step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="edit_duracion">Duración</label>
                            <input type="text" class="form-control" id="edit_duracion" name="duracion">
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
        $('#editTreatmentModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id');
            var tipo_tratamiento = button.data('tipo_tratamiento');
            var costo = button.data('costo');
            var duracion = button.data('duracion');
            
            var modal = $(this);
            modal.find('#edit_id').val(id);
            modal.find('#edit_tipo_tratamiento').val(tipo_tratamiento);
            modal.find('#edit_costo').val(costo);
            modal.find('#edit_duracion').val(duracion);
        });

        // Script para manejar la eliminación
        $('#confirmDeleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id');
            var modal = $(this);
            modal.find('#deleteTreatmentButton').attr('href', 'tratamientos.php?delete=' + id);
        });
    </script>
</body>
</html>