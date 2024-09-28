<?php
include 'conexion.php';
session_start();

if (isset($_POST['funcion'])) {
    $funcion = $_POST['funcion'];

    switch ($funcion) {
        case 'crearArticulo':
            crearArticulo();
            break;

        case 'subirImagen':
            subirImagen();
            break;

        case 'actualizarCantidad':
            actualizarCantidad();
            break;

        case 'modificarArticulo':
            modificarArticulo();
            break;

        case 'cambiarEstatusArticulo':
            cambiarEstatusArticulo();
            break;

        default:
            echo json_encode(array("mensaje" => "Función no válida"));
            break;

    }
} else {
    echo json_encode(array("mensaje" => "No se recibió ninguna función"));
}

function crearArticulo() {
    global $conexion;

    if (isset($_POST['nombre_articulo']) && isset($_POST['descripcion']) && isset($_POST['precio']) && isset($_POST['id_categoria'])) {
        $nombre_articulo = $_POST['nombre_articulo'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $id_categoria = $_POST['id_categoria'];
        $estatus = 'alta'; // Estatus por defecto al crear

        $sql = "INSERT INTO Articulos (nombre_articulo, descripcion, precio, id_categoria, fecha_creacion, estatus) 
                VALUES (?, ?, ?, ?, NOW(), ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ssiss', $nombre_articulo, $descripcion, $precio, $id_categoria, $estatus);

        if ($stmt->execute()) {
            echo json_encode(array("mensaje" => "Artículo creado exitosamente"));
        } else {
            echo json_encode(array("mensaje" => "Error al crear el artículo"));
        }

        $stmt->close();
    } else {
        echo json_encode(array("mensaje" => "Faltan datos para crear el artículo"));
    }
}

function subirImagen() {
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
        $directorio = 'ruta/donde/guardar/imagenes/'; // Cambiar por la ruta deseada
        $nombre_archivo = basename($_FILES['imagen']['name']);
        $ruta_completa = $directorio . $nombre_archivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa)) {
            echo json_encode(array("mensaje" => "Imagen subida exitosamente", "ruta_imagen" => $ruta_completa));
        } else {
            echo json_encode(array("mensaje" => "Error al subir la imagen"));
        }
    } else {
        echo json_encode(array("mensaje" => "No se recibió ninguna imagen o hubo un error en la carga"));
    }
}

function actualizarCantidad() {
    global $conexion;

    if (isset($_POST['id_articulo']) && isset($_POST['id_almacen']) && isset($_POST['cantidad'])) {
        $id_articulo = $_POST['id_articulo'];
        $id_almacen = $_POST['id_almacen'];
        $cantidad = $_POST['cantidad'];

        // Actualizar o insertar la cantidad en la tabla Existencias
        $sql = "INSERT INTO Existencias (id_articulo, id_almacen, cantidad) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE cantidad = cantidad + ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('iiii', $id_articulo, $id_almacen, $cantidad, $cantidad);

        if ($stmt->execute()) {
            echo json_encode(array("mensaje" => "Cantidad actualizada exitosamente"));
        } else {
            echo json_encode(array("mensaje" => "Error al actualizar la cantidad"));
        }

        $stmt->close();
    } else {
        echo json_encode(array("mensaje" => "Faltan datos para actualizar la cantidad"));
    }
}

// Función para modificar un artículo
function modificarArticulo() {
    global $conexion;

    if (isset($_POST['id_articulo']) && isset($_POST['nombre_articulo']) && isset($_POST['descripcion']) && isset($_POST['precio']) && isset($_POST['id_categoria'])) {
        $id_articulo = $_POST['id_articulo'];
        $nombre_articulo = $_POST['nombre_articulo'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $id_categoria = $_POST['id_categoria'];

        $sql = "UPDATE Articulos SET nombre_articulo = ?, descripcion = ?, precio = ?, id_categoria = ? 
                WHERE id_articulo = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ssisi', $nombre_articulo, $descripcion, $precio, $id_categoria, $id_articulo);

        if ($stmt->execute()) {
            echo json_encode(array("mensaje" => "Artículo modificado exitosamente"));
        } else {
            echo json_encode(array("mensaje" => "Error al modificar el artículo"));
        }

        $stmt->close();
    } else {
        echo json_encode(array("mensaje" => "Faltan datos para modificar el artículo"));
    }
}

// Función para cambiar el estatus de un artículo
function cambiarEstatusArticulo() {
    global $conexion;

    if (isset($_POST['id_articulo']) && isset($_POST['estatus'])) {
        $id_articulo = $_POST['id_articulo'];
        $estatus = $_POST['estatus']; // 'alta' o 'baja'

        $sql = "UPDATE Articulos SET estatus = ? WHERE id_articulo = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('si', $estatus, $id_articulo);

        if ($stmt->execute()) {
            echo json_encode(array("mensaje" => "Estatus del artículo actualizado exitosamente"));
        } else {
            echo json_encode(array("mensaje" => "Error al cambiar el estatus del artículo"));
        }

        $stmt->close();
    } else {
        echo json_encode(array("mensaje" => "Faltan datos para cambiar el estatus"));
    }
}
?>
