<?php
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo_usuario'])) {
    header("Location: ../../login.html");
    exit();
}
else {
    $tipo_usuario = $_SESSION['tipo_usuario'];
    $email = $_SESSION['usuario'];

    if ($tipo_usuario !== 'administrador'){
        header("Location: ../../login.html");
        exit();
    }
}
$id_articulo = 0;

if (isset($_GET['id_articulo'])) {
    $id_articulo = $_GET['id_articulo'];
    // echo $id_articulo;
}
else{
    echo "<Script>history.back()</Script>";
}

include "../dbo/conexion.php";

$sql = "SELECT * FROM articulos WHERE id_articulo = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_articulo);
$stmt->execute();
$resultado = $stmt->get_result();
$articulo = $resultado->fetch_assoc();


?>


<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Articulos</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php
            include "include/menu.php";
        ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php
                    include "include/top.php";
                ?>
                <!-- End of Topbar -->

                <!-- BODY -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Editar Artículo</h1>
                    <p class="mb-4">Modifica la información del artículo</p>

                    <form id="formEditarArticulo" enctype="multipart/form-data">
                        <input type="hidden" name="id_articulo" value="<?php echo $id_articulo; ?>">

                        <div class="form-group">
                            <label for="nombre">Nombre del Artículo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $articulo['nombre']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" required><?php echo $articulo['descripcion']; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="precio">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="<?php echo $articulo['precio']; ?>" required>
                        </div>

                        <!-- Visualizador de imagen -->
                        <div class="form-group">
                            <label>Imagen Actual</label><br>
                            <img src="<?php echo $articulo['imagen_url']; ?>" alt="Imagen del Artículo" width="150">
                        </div>

                        <!-- Input para subir una nueva imagen (opcional) -->
                        <div class="form-group">
                            <label for="imagen">Subir Nueva Imagen (opcional)</label>
                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                        </div>

                        <!-- Select para cambiar el estatus -->
                        <div class="form-group">
                            <label for="estatus">Estatus</label>
                            <select class="form-control" id="estatus" name="estatus" required>
                                <option value="alta" <?php echo $articulo['estatus'] === 'alta' ? 'selected' : ''; ?>>Alta</option>
                                <option value="baja" <?php echo $articulo['estatus'] === 'baja' ? 'selected' : ''; ?>>Baja</option>
                            </select>
                        </div>

                        <!-- Inputs de stock por almacén -->
                         <hr>
                        <div class="form-group">
                            <h4 >Disponibilidad por Almacén</h4>
                            <div id="almacenesContainer">
                                <?php
                                    include "../dbo/conexion.php";
                                    $sql_almacenes = "SELECT ad.id_almacen, a.nombre_almacen, ad.stock 
                                                        FROM artdisponibilidad ad
                                                        JOIN almacenes a ON ad.id_almacen = a.id_almacen
                                                        WHERE ad.id_articulo = ?";
                                    $stmt_almacenes = $conexion->prepare($sql_almacenes);
                                    $stmt_almacenes->bind_param('i', $id_articulo);
                                    $stmt_almacenes->execute();
                                    $resultado_almacenes = $stmt_almacenes->get_result();

                                    if ($resultado_almacenes->num_rows > 0) {
                                        while ($fila = $resultado_almacenes->fetch_assoc()) {
                                            echo '<div class="form-group">';
                                            echo '<label for="stock_' . $fila['id_almacen'] . '">Stock en ' . $fila['nombre_almacen'] . '</label>';
                                            echo '<input type="number" class="form-control" id="stock_' . $fila['id_almacen'] . '" name="stock[' . $fila['id_almacen'] . ']" value="' . $fila['stock'] . '" required>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<p>No hay almacenes disponibles.</p>';
                                    }
                                ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                </div>
                <!-- FIN DEL BODY -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php
            include "include/footer.php"
            ?>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>

    <script>
document.getElementById('formEditarArticulo').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Obtener los valores del formulario
    const idArticulo = <?php echo $id_articulo; ?>;  // Variable PHP con el ID del artículo
    const nombre = document.getElementById('nombre').value;
    const descripcion = document.getElementById('descripcion').value;
    const precio = document.getElementById('precio').value;
    const imagen = document.getElementById('imagen').files[0];  // Imagen opcional
    const stock = document.querySelectorAll('#almacenesContainer input');
    const estatus = document.getElementById('estatus').value;

    // Validar campos obligatorios
    if (nombre === "" || descripcion === "" || precio === "") {
        alert("Por favor, completa todos los campos requeridos.");
        return;
    }

    // Validar stock por almacén
    for (let i = 0; i < stock.length; i++) {
        const cantidad = stock[i].value;
        if (cantidad === "") {
            alert("Por favor, ingresa una cantidad válida para cada almacén.");
            return;
        }
    }

    try {
        // 1. Actualizar el artículo
        const formDataArticulo = new FormData();
        formDataArticulo.append('id_articulo', idArticulo);
        formDataArticulo.append('nombre', nombre);
        formDataArticulo.append('descripcion', descripcion);
        formDataArticulo.append('precio', precio);
        formDataArticulo.append('estatus', estatus);
        formDataArticulo.append('funcion', 'actualizarArticulo');

        const respuestaArticulo = await fetch('../controller/ArticuloController.php', {
            method: 'POST',
            body: formDataArticulo
        });
        const dataArticulo = await respuestaArticulo.json();
        if (dataArticulo.ok !== 0) {
            throw new Error("Error al actualizar el artículo: " + dataArticulo.mensaje);
        }

        // 2. Subir la imagen si existe
        if (imagen) {
            const formDataImagen = new FormData();
            formDataImagen.append('id_articulo', idArticulo);
            formDataImagen.append('imagen', imagen);
            formDataImagen.append('funcion', 'subirImagen');

            const respuestaImagen = await fetch('../controller/ArticuloController.php', {
                method: 'POST',
                body: formDataImagen
            });
            const dataImagen = await respuestaImagen.json();
            if (dataImagen.ok !== 0) {
                throw new Error("Error al subir la imagen: " + dataImagen.mensaje);
            }
        }

        // 3. Actualizar la disponibilidad por almacén
        for (let i = 0; i < stock.length; i++) {
            const idAlmacen = stock[i].id.split('_')[1];  // Extraer el ID del almacén
            const cantidad = stock[i].value;

            const formDataStock = new FormData();
            formDataStock.append('id_articulo', idArticulo);
            formDataStock.append('id_almacen', idAlmacen);
            formDataStock.append('cantidad', cantidad);
            formDataStock.append('funcion', 'actualizarDisponibilidadAlmacen');

            const respuestaStock = await fetch('../controller/ArticuloController.php', {
                method: 'POST',
                body: formDataStock
            });
            const dataStock = await respuestaStock.json();
            if (dataStock.ok !== 0) {
                throw new Error("Error al actualizar stock en almacén " + idAlmacen + ": " + dataStock.mensaje);
            }
        }

        alert("Artículo y disponibilidad actualizados correctamente.");
        window.location.href = "Articulos.php";  // Redirigir a la lista de artículos

    } catch (error) {
        console.error("Error:", error);
        alert("Hubo un error: " + error.message);
    }
});
</script>

</body>

</html>