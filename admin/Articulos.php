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

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Articulos</h1>
                    <p class="mb-4">Aquí puedes modificar toda la información de los artículos</p>


                    <a href="Articulos_Nuevo.php" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="text">Nuevo artículo</span>
                    </a>
                    <br><br>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>nombre</th>
                                            <th>descripcion</th>
                                            <th>Precio</th>
                                            <th>Stock</th>
                                            <th>estatus</th>
                                            <th>acciones</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>#</th>
                                            <th>nombre</th>
                                            <th>descripcion</th>
                                            <th>Precio</th>
                                            <th>Stock</th>
                                            <th>estatus</th>
                                            <th>acciones</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php
                                            include "../dbo/conexion.php";
                                            $sql = "SELECT  a.id_articulo,a.nombre,a.descripcion,a.precio,SUM(ad.stock) AS total_stock,a.estatus FROM articulos a LEFT JOIN ArtDisponibilidad ad ON a.id_articulo = ad.id_articulo GROUP BY a.id_articulo;";
                                            $result = $conexion->query($sql);
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . $row['id_articulo'] . "</td>";
                                                    echo "<td>" . $row['nombre'] . "</td>";
                                                    echo "<td>" . $row['descripcion'] . "</td>";
                                                    echo "<td>" . $row['precio'] . "</td>";
                                                    echo "<td>" . $row['total_stock'] . "</td>";
                                                    echo "<td>" . ($row['estatus'] ? 'Activo' : 'Inactivo') . "</td>";
                                                    echo "<td>";
                                                    echo "<a href='Articulos_editar.php?id_articulo=" . $row['id_articulo'] . "' class='btn btn-primary'>Editar</a>";
                                                    echo "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='7'>No hay artículos disponibles</td></tr>";
                                            }
                                            $conexion->close();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

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

</html>