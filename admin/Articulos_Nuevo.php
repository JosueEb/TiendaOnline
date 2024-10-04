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
                    <h1 class="h3 mb-2 text-gray-800">Registrando Artículo</h1>
                    <p class="mb-4">Llena toda la información del artículo</p>

                    <form id="formArticulo" id="guardar_Art" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nombre">Nombre del Artículo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del artículo" required>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Descripción del artículo" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="precio">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="precio" name="precio" placeholder="Precio" required>
                        </div>

                        <!-- Input para subir la imagen -->
                        <div class="form-group">
                            <label for="imagen">Subir Imagen del Artículo</label>
                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" >
                        </div>
                        <hr>

                        <!-- Inputs de stock por almacén -->
                        <div class="form-group">
                            <H4>Disponibilidad por Almacén</H4>
                            <div id="almacenesContainer">
                                <?php
                                    include '../dbo/conexion.php';
                                    $sql = "SELECT id_almacen, nombre_almacen FROM almacenes WHERE estatus = 'alta'";
                                    $resultado = $conexion->query($sql);

                                    if ($resultado->num_rows > 0) {
                                        while ($fila = $resultado->fetch_assoc()) {
                                            echo '<div class="form-group">';
                                            echo '<label for="stock_' . $fila['id_almacen'] . '">Stock en ' . $fila['nombre_almacen'] . '</label>';
                                            echo '<input type="number" class="form-control" id="stock_' . $fila['id_almacen'] . '" name="stock[' . $fila['id_almacen'] . ']" placeholder="Cantidad en ' . $fila['nombre_almacen'] . '" required>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<p>No hay almacenes disponibles.</p>';
                                    }
                                    $conexion->close();
                                ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar Artículo</button>
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
document.getElementById('formArticulo').addEventListener('submit', async function(e) {
    e.preventDefault();

    const nombre = document.getElementById('nombre').value;
    const descripcion = document.getElementById('descripcion').value;
    const precio = document.getElementById('precio').value;
    const imagen = document.getElementById('imagen').files[0];
    const stock = document.querySelectorAll('#almacenesContainer input');

    if (nombre === "" || descripcion === "" || precio === "") {
        alert("Por favor, completa todos los campos requeridos.");
        return;
    }

    for (let i = 0; i < stock.length; i++) {
        const cantidad = stock[i].value;
        if (cantidad === "") {
            alert("Por favor ingresa una cantidad válida para cada almacén.");
            return;
        }
    }

    let id_articulo_creado = 0;

    try {
        // Crear el artículo (primera parte)
        const formData = new FormData();
        formData.append('nombre', nombre);
        formData.append('descripcion', descripcion);
        formData.append('precio', precio);
        formData.append('funcion', 'crearArticulo');

        let response = await fetch('../controller/ArticuloController.php', {
            method: 'POST',
            body: formData
        });
        let data = await response.json();

        if (data.ok === 0) {
            id_articulo_creado = data.id;

            // Subir imagen (segunda parte)
            if (imagen) {
                const formDataImagen = new FormData();
                formDataImagen.append('id_articulo', id_articulo_creado);
                formDataImagen.append('imagen', imagen);
                formDataImagen.append('funcion', 'subirImagen');

                response = await fetch('../controller/ArticuloController.php', {
                    method: 'POST',
                    body: formDataImagen
                });
                data = await response.json();

                if (data.ok !== 0) {
                    throw new Error("Error al subir la imagen: " + data.mensaje);
                }
            }

            // Insertar cantidades en almacenes (tercera parte)
            for (let i = 0; i < stock.length; i++) {
                const id_almacen = stock[i].id.split('_')[1];
                const cantidad = stock[i].value;

                const formDataStock = new FormData();
                formDataStock.append('id_articulo', id_articulo_creado);
                formDataStock.append('id_almacen', id_almacen);
                formDataStock.append('cantidad', cantidad);
                formDataStock.append('funcion', 'agregarDisponibilidadAlmacen');

                response = await fetch('../controller/ArticuloController.php', {
                    method: 'POST',
                    body: formDataStock
                });
                data = await response.json();

                if (data.ok !== 0) {
                    throw new Error("Error al guardar stock en almacén: " + data.mensaje);
                }
            }

            alert("Artículo registrado con éxito.");
            window.location.href="Articulos.php";
        } else {
            throw new Error(data.mensaje);
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Hubo un error: " + error.message);
    }
});



    </script>

</body>

</html>