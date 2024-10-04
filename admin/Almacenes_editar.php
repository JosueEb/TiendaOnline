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
$id_almacen = 0;

if (isset($_GET['id_almacen'])) {
    $id_almacen = $_GET['id_almacen'];
    // echo $id_almacen;
}
else{
    echo "<Script>history.back()</Script>";
}

include "../dbo/conexion.php";

$sql = "SELECT * FROM almacenes WHERE id_almacen = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_almacen);
$stmt->execute();
$resultado = $stmt->get_result();
$almacen = $resultado->fetch_assoc();

?>


<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Almacenes</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                    <h1 class="h3 mb-2 text-gray-800">Editar Almacén</h1>
                    <p class="mb-4">Modifica la información del almacén</p>

                    <form id="formEditarAlmacen">
                        <input type="hidden" name="id_almacen" value="<?php echo $almacen['id_almacen']; ?>">
                        <div class="form-group">
                            <label for="nombre_almacen">Nombre del Almacén:</label>
                            <input type="text" class="form-control" id="nombre_almacen" name="nombre_almacen" value="<?php echo $almacen['nombre_almacen']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="ubicacion">Ubicación:</label>
                            <input type="text" class="form-control" id="ubicacion" name="ubicacion" value="<?php echo $almacen['ubicacion']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="estatus">Estatus:</label>
                            <select class="form-control" id="estatus" name="estatus">
                                <option value="alta" <?php echo $almacen['estatus'] == 'alta' ? 'selected' : ''; ?>>Alta</option>
                                <option value="baja" <?php echo $almacen['estatus'] == 'baja' ? 'selected' : ''; ?>>Baja</option>
                            </select>
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

</body>
<script>
document.getElementById('formEditarAlmacen').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('funcion', 'actualizarAlmacen');

    fetch('../controller/ArticuloController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok === 0) {

            Swal.fire({
                icon: 'success',
                title: 'Almacén actualizado',
                text: data.mensaje
            }).then(() => {
                window.location.href = 'Almacenes.php';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.mensaje
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: 'Hubo un problema al actualizar el almacén.'
        });
    });
});
</script>


</html>