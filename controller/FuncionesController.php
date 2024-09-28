<?php
include '../dbo/conexion.php';
session_set_cookie_params(1800);
session_start();


if (isset($_POST['funcion'])) {
    $funcion = $_POST['funcion'];

    switch ($funcion) {
        case 'iniciarSesion':
            iniciarSesion();
            break;

        case 'crearUsuario':
            crearUsuario();
            break;

        case 'cambiarContraseña':
            cambiarContraseña();
            break;

        case 'cerrarSesion':
            cerrarSesion();
            break;

        default:
            echo json_encode(array("mensaje" => "Función no válida"));
            break;
    }
} else {
    echo json_encode(array("mensaje" => "No se recibió ninguna función"));
}

function iniciarSesion() {
    global $conexion;

    $response = array("ok" => 1, "okRef" => 0, "mensaje" => "");

    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Cifrar la contraseña con SHA-256
        $password_cifrada = hash('sha256', $password);

        // Preparar la consulta para buscar al usuario con email, contraseña cifrada y estatus 'alta'
        $sql = "SELECT * FROM Usuarios WHERE email = ? AND contraseña = ? AND estatus = 'alta'";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ss', $email, $password_cifrada);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            $_SESSION['usuario'] = $usuario['email'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
            $_SESSION['id'] = $usuario['tipo_usuario'];

            $response["ok"] = 0;
            $response["okRef"] = 0;
            $response["mensaje"] = "Inicio de sesión exitoso";
            $response["tipo_usuario"] = $usuario['tipo_usuario'];
            http_response_code(200);
        } else {
            $response["ok"] = 1;
            $response["okRef"] = 1;
            $response["mensaje"] = "Credenciales incorrectas o usuario inactivo";
            http_response_code(401);
        }

        $stmt->close();
    } else {
        // Faltan datos en la petición
        $response["ok"] = 1;
        $response["okRef"] = 2;
        $response["mensaje"] = "Faltan datos: email y contraseña";
        http_response_code(400);
    }

    echo json_encode($response);
}


function crearUsuario() {
    global $conexion;

    $response = array("ok" => 1, "okRef" => 0, "mensaje" => "");

    if (isset($_POST['nombre_usuario']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['tipo_usuario'])) {
        $nombre_usuario = $_POST['nombre_usuario'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $tipo_usuario = $_POST['tipo_usuario'];

        // Verificar si el correo ya está registrado
        $sql_verificar = "SELECT * FROM Usuarios WHERE email = ?";
        $stmt_verificar = $conexion->prepare($sql_verificar);
        $stmt_verificar->bind_param('s', $email);
        $stmt_verificar->execute();
        $resultado = $stmt_verificar->get_result();

        if ($resultado->num_rows > 0) {
            // Si el correo ya está registrado
            $response["ok"] = 1;
            $response["okRef"] = 1;
            $response["mensaje"] = "El correo ya está registrado";
            http_response_code(400);
        } else {
            // Cifrar la contraseña
            $password_cifrada = hash('sha256', $password);

            $sql = "INSERT INTO Usuarios (nombre_usuario, email, contraseña, tipo_usuario, fecha_registro, estatus) 
                    VALUES (?, ?, ?, ?, NOW(), 'alta')";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('ssss', $nombre_usuario, $email, $password_cifrada, $tipo_usuario);

            if ($stmt->execute()) {
                $response["ok"] = 0; // OK
                $response["okRef"] = 0; // Sin error
                $response["mensaje"] = "Usuario creado exitosamente";
                http_response_code(200);
            } else {
                // Error al crear usuario
                $response["ok"] = 1;
                $response["okRef"] = 2; // Código de error 2: error al insertar usuario
                $response["mensaje"] = "Error al crear el usuario";
                http_response_code(500);
            }

            $stmt->close();
        }

        $stmt_verificar->close();
    } else {
        $response["ok"] = 1;
        $response["okRef"] = 3; // Código de error 3: datos incompletos
        $response["mensaje"] = "Faltan datos para crear el usuario";
        http_response_code(400);
    }

    echo json_encode($response);
}



function cambiarContraseña() {
    global $conexion;

    if (isset($_POST['email']) && isset($_POST['password_antigua']) && isset($_POST['password_nueva'])) {
        $email = $_POST['email'];
        $password_antigua = $_POST['password_antigua'];
        $password_nueva = $_POST['password_nueva'];

        $password_antigua_cifrada = hash('sha256', $password_antigua);

        // Verificar la contraseña antigua
        $sql = "SELECT * FROM Usuarios WHERE email = ? AND contraseña = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ss', $email, $password_antigua_cifrada);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // Cifrar la nueva contraseña
            $password_nueva_cifrada = hash('sha256', $password_nueva);

            // Actualizar la contraseña
            $sql_update = "UPDATE Usuarios SET contraseña = ? WHERE email = ?";
            $stmt_update = $conexion->prepare($sql_update);
            $stmt_update->bind_param('ss', $password_nueva_cifrada, $email);

            if ($stmt_update->execute()) {
                echo json_encode(array("mensaje" => "Contraseña cambiada exitosamente"));
            } else {
                echo json_encode(array("mensaje" => "Error al cambiar la contraseña"));
            }

            $stmt_update->close();
        } else {
            echo json_encode(array("mensaje" => "La contraseña antigua no es correcta"));
        }

        $stmt->close();
    } else {
        echo json_encode(array("mensaje" => "Faltan datos para cambiar la contraseña"));
    }
}

function cerrarSesion() {
    session_unset();

    session_destroy();

    echo json_encode(array("mensaje" => "Sesión cerrada exitosamente"));
}
?>
