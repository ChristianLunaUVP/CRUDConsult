<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Consultorio</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome para los íconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Animaciones AOS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        /* Estilos para la Sidebar */
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding-top: 30px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        /* Estilos de los enlaces dentro de la sidebar */
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 25px;
            font-size: 18px;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #3498db;
            color: white;
            transform: translateX(10px);
        }

        .sidebar a.active {
            background-color: #3498db;
        }

        /* Estilos de la marca del sidebar */
        .sidebar .navbar-brand {
            font-size: 24px;
            color: #fff;
            padding-left: 20px;
            margin-bottom: 20px;
            white-space: nowrap; /* Evitar que se rompa la línea */
            overflow: hidden; /* Cortar el texto si es muy largo */
            text-overflow: ellipsis; /* Agregar puntos suspensivos si el texto es largo */
            transition: color 0.3s ease; /* Agregar transición para suavizar el cambio de color */
        }

        /* Cambiar color del texto al pasar el mouse sobre la marca */
        .sidebar .navbar-brand:hover {
            color: #fff; /* Asegurar que el texto se vuelva blanco al pasar el mouse */
            background-color: #3498db;
        }

        /* Estilos del contenido principal */
        .content {
            margin-left: 250px;
            padding: 40px;
            flex-grow: 1;
        }

        .content h1 {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .content p {
            font-size: 1.2rem;
            color: #555;
        }

        /* Estilo de las animaciones */
        .sidebar a {
            animation: fadeInLeft 0.5s ease-in-out;
        }

        @keyframes fadeInLeft {
            0% {
                opacity: 0;
                transform: translateX(-20px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" data-aos="fade-right" data-aos-duration="500">
        <a class="navbar-brand" href="index.html">
            <i class="fas fa-stethoscope"></i> Gestión de <br>
               Consultorio
        </a>
        <div class="nav flex-column">
            <a class="nav-link" href="doctores.php">
                <i class="fas fa-user-md"></i> Doctores
            </a>
            <a class="nav-link" href="pacientes.php">
                <i class="fas fa-users"></i> Pacientes
            </a>
            <a class="nav-link" href="citas.php">
                <i class="fas fa-calendar-check"></i> Citas
            </a>
            <a class="nav-link" href="tratamientos.php">
                <i class="fas fa-capsules"></i> Tratamientos
            </a>
            <a class="nav-link" href="historial_tratamiento.php">
                <i class="fas fa-history"></i> Historial
            </a>
        </div>
    </div>


    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS (Animaciones al hacer scroll) -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html>
