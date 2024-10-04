<?php
include '../dbo/conexion.php';
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

        case 'agregarDisponibilidadAlmacen':
            agregarDisponibilidadAlmacen();
            break;

        case 'actualizarArticulo':
            actualizarArticulo();
            break;

        case 'actualizarDisponibilidadAlmacen':
            actualizarDisponibilidadAlmacen();
            break;

        case 'agregarAlCarrito':
            agregarAlCarrito();
            break;

        case 'actualizarCantidadCarrito':
            actualizarCantidadCarrito();
            break;

        case 'eliminarArticuloCarrito':
            eliminarArticuloCarrito();
            break;

        case 'guardarAlmacen':
            guardarAlmacen();
            break;

        case 'actualizarAlmacen':
            actualizarAlmacen();
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

    if (isset($_POST['nombre']) && isset($_POST['descripcion']) && isset($_POST['precio'])) {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = floatval($_POST['precio']); // Convertir a decimal

        // Preparar la consulta
        $sql = "INSERT INTO articulos (nombre, descripcion, precio, fecha_creacion, estatus) 
                VALUES (?, ?, ?, NOW(), 'alta')";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ssd', $nombre, $descripcion, $precio);

        if ($stmt->execute()) {
            $id_articulo = $stmt->insert_id;  // Obtener el ID del artículo insertado
            $response = [
                "ok" => 0,
                "okRef" => 0,
                "id" => $id_articulo,
                "mensaje" => "Artículo creado exitosamente"
            ];
        } else {
            $response = [
                "ok" => 1,
                "okRef" => 1,
                "mensaje" => "Error al crear el artículo"
            ];
        }

        $stmt->close();
    } else {
        $response = [
            "ok" => 1,
            "okRef" => 2,
            "mensaje" => "Faltan datos para crear el artículo"
        ];
    }

    echo json_encode($response);
}

function subirImagen() {
    global $conexion;

    if (isset($_POST['id_articulo']) && isset($_FILES['imagen'])) {
        $id_articulo = $_POST['id_articulo'];
        $imagen = $_FILES['imagen'];

        // Directorio de destino
        $target_dir = "../admin/IMG/";

        $imageFileType = strtolower(pathinfo($imagen["name"], PATHINFO_EXTENSION));

        // Construir el nuevo nombre del archivo con id, guion bajo y fecha actual (Y-m-d_H-i-s)
        $new_filename = $id_articulo . "_" . date("Y-m-d_H-i-s") . "." . $imageFileType;

        // Ruta completa del archivo con el nuevo nombre
        $target_file = $target_dir . $new_filename;
        $uploadOk = 1;

        // Verificar si es una imagen válida
        $check = getimagesize($imagen["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $uploadOk = 0;
        }

        // Subir el archivo si todo está bien
        if ($uploadOk && move_uploaded_file($imagen["tmp_name"], $target_file)) {
            // Actualizar la ruta del archivo en la base de datos
            $sql = "UPDATE articulos SET imagen_url = ? WHERE id_articulo = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('si', $target_file, $id_articulo);
            if ($stmt->execute()) {
                echo json_encode([
                    "ok" => 0,
                    "okRef" => 0,
                    "mensaje" => "Imagen subida y asociada correctamente"
                ]);
            } else {
                echo json_encode([
                    "ok" => 1,
                    "okRef" => 3,
                    "mensaje" => "Error al asociar la imagen al artículo"
                ]);
            }
        } else {
            echo json_encode([
                "ok" => 1,
                "okRef" => 4,
                "mensaje" => "Error al subir la imagen"
            ]);
        }
    } else {
        echo json_encode([
            "ok" => 1,
            "okRef" => 5,
            "mensaje" => "Faltan datos para subir la imagen"
        ]);
    }
}


function agregarDisponibilidadAlmacen() {
    global $conexion;

    if (isset($_POST['id_articulo']) && isset($_POST['id_almacen']) && isset($_POST['cantidad'])) {
        $id_articulo = $_POST['id_articulo'];
        $id_almacen = $_POST['id_almacen'];
        $stock = $_POST['cantidad']; // Cambia a 'cantidad' para mantener la consistencia

        $sql = "INSERT INTO ArtDisponibilidad (id_articulo, id_almacen, stock) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('iii', $id_articulo, $id_almacen, $stock); // Cambia 'cantidad' a 'stock'

        if ($stmt->execute()) {
            echo json_encode([
                "ok" => 0,
                "okRef" => 0,
                "mensaje" => "Cantidad agregada correctamente"
            ]);
        } else {
            echo json_encode([
                "ok" => 1,
                "okRef" => 1,
                "mensaje" => "Error al agregar la cantidad en el almacén"
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            "ok" => 1,
            "okRef" => 2,
            "mensaje" => "Datos incompletos"
        ]);
    }
}

function actualizarArticulo() {
    global $conexion;

    if (isset($_POST['id_articulo']) && isset($_POST['nombre']) && isset($_POST['descripcion']) && isset($_POST['precio']) && isset($_POST['estatus'])) {
        $id_articulo = $_POST['id_articulo'];
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $estatus = $_POST['estatus'];

        $sql = "UPDATE articulos SET nombre = ?, descripcion = ?, precio = ?, estatus = ? WHERE id_articulo = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ssdsi', $nombre, $descripcion, $precio, $estatus, $id_articulo);

        if ($stmt->execute()) {
            echo json_encode([
                "ok" => 0,
                "mensaje" => "Artículo actualizado correctamente."
            ]);
        } else {
            echo json_encode([
                "ok" => 1,
                "mensaje" => "Error al actualizar el artículo."
            ]);
        }
    } else {
        echo json_encode([
            "ok" => 1,
            "mensaje" => "Faltan datos para actualizar el artículo."
        ]);
    }
}


function actualizarDisponibilidadAlmacen() {
    global $conexion;

    if (isset($_POST['id_articulo']) && isset($_POST['id_almacen']) && isset($_POST['cantidad'])) {
        $id_articulo = $_POST['id_articulo'];
        $id_almacen = $_POST['id_almacen'];
        $cantidad = $_POST['cantidad'];

        if (empty($cantidad) || $cantidad < 0) {
            echo json_encode([
                "ok" => 1,
                "mensaje" => "Cantidad inválida para el almacén $id_almacen."
            ]);
            return;
        }

        $sql_verificar = "SELECT * FROM artdisponibilidad WHERE id_articulo = ? AND id_almacen = ?";
        $stmt_verificar = $conexion->prepare($sql_verificar);
        $stmt_verificar->bind_param('ii', $id_articulo, $id_almacen);
        $stmt_verificar->execute();
        $resultado = $stmt_verificar->get_result();

        if ($resultado->num_rows > 0) {
            $sql_update = "UPDATE artdisponibilidad SET stock = ? WHERE id_articulo = ? AND id_almacen = ?";
            $stmt_update = $conexion->prepare($sql_update);
            $stmt_update->bind_param('iii', $cantidad, $id_articulo, $id_almacen);
            $stmt_update->execute();
        } else {
            $sql_insert = "INSERT INTO artdisponibilidad (id_articulo, id_almacen, stock) VALUES (?, ?, ?)";
            $stmt_insert = $conexion->prepare($sql_insert);
            $stmt_insert->bind_param('iii', $id_articulo, $id_almacen, $cantidad);
            $stmt_insert->execute();
        }

        echo json_encode([
            "ok" => 0,
            "mensaje" => "Disponibilidad del almacén actualizada correctamente."
        ]);
    } else {
        echo json_encode([
            "ok" => 1,
            "mensaje" => "Faltan datos para actualizar la disponibilidad en el almacén."
        ]);
    }
}


function agregarAlCarrito() {
    global $conexion;
    if (isset($_POST['id_usuario']) && isset($_POST['id_articulo']) && isset($_POST['cantidad'])) {
        $id_articulo = $_POST['id_articulo'];
        $id_usuario = $_POST['id_usuario'];
        $cantidad = $_POST['cantidad'];

        // Verificar si el artículo ya está en el carrito
        $sql = "SELECT * FROM carrito WHERE id_usuario = ? AND id_articulo = ? AND estatus = 'activo'";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ii', $id_usuario, $id_articulo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Si el artículo ya está en el carrito, incrementar la cantidad en 1
            $row = $result->fetch_assoc();
            $nueva_cantidad = $row['cantidad'] + 1;  // Sumar siempre 1

            $update_sql = "UPDATE carrito SET cantidad = ? WHERE id_carrito = ?";
            $update_stmt = $conexion->prepare($update_sql);
            $update_stmt->bind_param('ii', $nueva_cantidad, $row['id_carrito']);
            $update_stmt->execute();

            if ($update_stmt->affected_rows > 0) {
                echo json_encode([
                    "ok" => 0,
                    "mensaje" => "Cantidad actualizada correctamente."
                ]);
            } else {
                echo json_encode([
                    "ok" => 1,
                    "mensaje" => "Error al actualizar la cantidad en el carrito."
                ]);
            }
        } else {
            // Si el artículo no está en el carrito, insertarlo con la cantidad enviada
            $insert_sql = "INSERT INTO carrito (id_usuario, id_articulo, cantidad, estatus) VALUES (?, ?, ?, 'activo')";
            $insert_stmt = $conexion->prepare($insert_sql);
            $insert_stmt->bind_param('iii', $id_usuario, $id_articulo, $cantidad);
            $insert_stmt->execute();

            if ($insert_stmt->affected_rows > 0) {
                echo json_encode([
                    "ok" => 0,
                    "mensaje" => "Artículo agregado al carrito correctamente."
                ]);
            } else {
                echo json_encode([
                    "ok" => 1,
                    "mensaje" => "Error al insertar el artículo en el carrito."
                ]);
            }
        }
    } else {
        echo json_encode([
            "ok" => 1,
            "mensaje" => "Faltan datos para agregar al carrito."
        ]);
    }
}

function actualizarCantidadCarrito() {
    global $conexion;

    if (isset($_POST['id_carrito']) && isset($_POST['accion'])) {
        $id_carrito = $_POST['id_carrito'];
        $accion = $_POST['accion'];

        // Obtener la cantidad actual del artículo en el carrito
        $sql = "SELECT cantidad FROM carrito WHERE id_carrito = ?";
        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            echo json_encode([
                "ok" => false,
                "mensaje" => "Error al preparar la consulta: " . $conexion->error
            ]);
            return;
        }

        $stmt->bind_param('i', $id_carrito);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $cantidad_actual = $row['cantidad'];

            // Aumentar o disminuir la cantidad según la acción
            if ($accion == 'sumar') {
                $nueva_cantidad = $cantidad_actual + 1;
            } elseif ($accion == 'restar') {
                $nueva_cantidad = $cantidad_actual - 1;
            }

            // Si la nueva cantidad es 0, eliminar el artículo del carrito
            if ($nueva_cantidad <= 0) {
                $delete_sql = "DELETE FROM carrito WHERE id_carrito = ?";
                $delete_stmt = $conexion->prepare($delete_sql);

                if (!$delete_stmt) {
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "Error al preparar la eliminación: " . $conexion->error
                    ]);
                    return;
                }

                $delete_stmt->bind_param('i', $id_carrito);
                $delete_stmt->execute();

                if ($delete_stmt->affected_rows > 0) {
                    echo json_encode([
                        "ok" => true,
                        "mensaje" => "Artículo eliminado del carrito."
                    ]);
                } else {
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "Error al eliminar el artículo."
                    ]);
                }
            } else {
                // Actualizar la cantidad en la base de datos si es mayor a 0
                $update_sql = "UPDATE carrito SET cantidad = ? WHERE id_carrito = ?";
                $update_stmt = $conexion->prepare($update_sql);

                if (!$update_stmt) {
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "Error al preparar la actualización: " . $conexion->error
                    ]);
                    return;
                }

                $update_stmt->bind_param('ii', $nueva_cantidad, $id_carrito);
                $update_stmt->execute();

                if ($update_stmt->affected_rows > 0) {
                    echo json_encode([
                        "ok" => true,
                        "mensaje" => "Cantidad actualizada correctamente."
                    ]);
                } else {
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "Error al actualizar la cantidad."
                    ]);
                }
            }
        } else {
            echo json_encode([
                "ok" => false,
                "mensaje" => "No se encontró el artículo en el carrito."
            ]);
        }
    } else {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Datos incompletos para actualizar la cantidad."
        ]);
    }
}




function eliminarArticuloCarrito() {
    global $conexion;
    
    if (isset($_POST['id_carrito'])) {
        $id_carrito = $_POST['id_carrito'];

        // Eliminar el artículo del carrito
        $delete_sql = "DELETE FROM carrito WHERE id_carrito = ?";
        $delete_stmt = $conexion->prepare($delete_sql);
        $delete_stmt->bind_param('i', $id_carrito);
        $delete_stmt->execute();

        if ($delete_stmt->affected_rows > 0) {
            echo json_encode(["ok" => true, "mensaje" => "Artículo eliminado correctamente."]);
        } else {
            echo json_encode(["ok" => false, "mensaje" => "Error al eliminar el artículo."]);
        }
    } else {
        echo json_encode(["ok" => false, "mensaje" => "Datos incompletos para eliminar el artículo."]);
    }
}


function guardarAlmacen() {
    global $conexion;

    if (isset($_POST['nombre_almacen']) && isset($_POST['ubicacion']) && isset($_POST['estatus'])) {
        $nombre_almacen = $_POST['nombre_almacen'];
        $ubicacion = $_POST['ubicacion'];
        $estatus = $_POST['estatus'];

        // Preparar la consulta SQL para insertar el nuevo almacén
        $sql = "INSERT INTO almacenes (nombre_almacen, ubicacion, estatus) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('sss', $nombre_almacen, $ubicacion, $estatus);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Enviar respuesta de éxito en formato JSON
            echo json_encode([
                "ok" => 0,
                "mensaje" => "Almacén guardado correctamente."
            ]);
        } else {
            // Enviar respuesta de error en formato JSON
            echo json_encode([
                "ok" => 1,
                "mensaje" => "Error al guardar el almacén."
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            "ok" => 1,
            "mensaje" => "Faltan datos para registrar el almacén."
        ]);
    }
}
function actualizarAlmacen()
{
    global $conexion;
    if (isset($_POST['id_almacen'], $_POST['nombre_almacen'], $_POST['ubicacion'], $_POST['estatus'])) {
        $id_almacen = intval($_POST['id_almacen']); // ID del almacén
        $nombre_almacen = trim($_POST['nombre_almacen']);
        $ubicacion = trim($_POST['ubicacion']);
        $estatus = $_POST['estatus'];

        $sql = "UPDATE almacenes SET nombre_almacen = ?, ubicacion = ?, estatus = ? WHERE id_almacen = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('sssi', $nombre_almacen, $ubicacion, $estatus, $id_almacen);

        if ($stmt->execute()) {
            echo json_encode([
                "ok" => 0,
                "mensaje" => "Almacén actualizado correctamente."
            ]);
        } else {
            echo json_encode([
                "ok" => 1,
                "mensaje" => "Error al actualizar el almacén."
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            "ok" => 1,
            "mensaje" => "Faltan datos para actualizar el almacén."
        ]);
    }
}


?>