<?php
session_start();
include 'dbo/conexion.php';

$email = "";
$num_items_carrito = 0;
$id = 0;

if (isset($_SESSION['usuario'])) {
    $email = $_SESSION['usuario'];
    $id = $_SESSION['id'];

    $sql = "SELECT SUM(cantidad) AS CantidadCarrito FROM carrito WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $num_items_carrito = $row['CantidadCarrito'];
    } else {
        $num_items_carrito = 0;
    }
} else {
    echo ('<script>alert("No has iniciado sesión"); window.location.href="index.php"</script>');
}

?>


<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Tienda</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="admin/css/sb-admin-2.css" rel="stylesheet" />
        <link href="admin/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

        <link href="admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

            <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    </head>
    <body>
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
            <div class="container px-4 px-lg-5">
                <a class="navbar-brand" href="index.php">Tienda</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <?php if ($email == ""): ?>
                            <li class="nav-item"><a class="nav-link" href="login.html">Iniciar sesión</a></li>
                            <li class="nav-item"><a class="nav-link" href="register.html">Registrarse</a></li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo htmlspecialchars($email); ?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="compras.php">Compras</a></li>
                                    <li><a class="dropdown-item" href="pagos_pendientes.php">Pagos Pendientes</a></li>
                                    <li><a class="dropdown-item" href="cambiar_contraseña.php">Cambiar Contraseña</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if ($email != ""): ?>
                        <div class="d-flex">
                            <button class="btn btn-outline-dark" id="btnCarrito">
                                <i class="bi-cart-fill me-1"></i>
                                Cart
                                <span class="badge bg-dark text-white ms-1 rounded-pill"><?php echo $num_items_carrito; ?></span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>


        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder">Articulos en el carrito</h1>
                    <!-- <p class="lead fw-normal text-white-50 mb-0">With this shop hompeage template</p> -->
                </div>
            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <?php
                        if (isset($_SESSION['usuario']) && isset($_SESSION['id'])) {
                            $id_usuario = $_SESSION['id'];
                            // Ejecutamos la consulta una sola vez
                            $sql = "
                                SELECT c.id_carrito, c.cantidad, a.nombre, a.precio, a.imagen_url
                                FROM carrito c
                                INNER JOIN articulos a ON c.id_articulo = a.id_articulo
                                WHERE c.id_usuario = ? AND c.estatus = 'activo'
                            ";
                            $stmt = $conexion->prepare($sql);
                            $stmt->bind_param('i', $id_usuario);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            // Guardamos los resultados en un array
                            $articulos_carrito = [];
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $articulos_carrito[] = $row;
                                }
                            }

                            $stmt->close();

                            // Verificamos si hay artículos en el carrito
                            if (count($articulos_carrito) > 0) {
                                $total_carrito = 0;

                                // Mostrar tabla en pantallas grandes
                                echo '
                                <div class="table-responsive d-none d-lg-block">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Imagen</th>
                                                <th>Nombre</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unitario</th>
                                                <th>Total Parcial</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                                foreach ($articulos_carrito as $articulo) {
                                    $id_carrito = $articulo['id_carrito'];
                                    $nombre = $articulo['nombre'];
                                    $cantidad = $articulo['cantidad'];
                                    $precio = $articulo['precio'];
                                    $imagen_url = str_replace("../", "", $articulo['imagen_url']) ? str_replace("../", "", $articulo['imagen_url']) : 'https://dummyimage.com/100x100/dee2e6/6c757d.jpg';
                                    $total_parcial = $precio * $cantidad;

                                    echo '
                                    <tr>
                                        <td><img src="' . $imagen_url . '" alt="' . $nombre . '" width="50"></td>
                                        <td>' . $nombre . '</td>
                                        <td>
                                            <button class="btn btn-sm btn-danger restarCantidad" data-id="' . $id_carrito . '">-</button>
                                            ' . $cantidad . '
                                            <button class="btn btn-sm btn-success agregarCantidad" data-id="' . $id_carrito . '">+</button>
                                        </td>
                                        <td>$' . number_format($precio, 2) . '</td>
                                        <td>$' . number_format($total_parcial, 2) . '</td>
                                        <td>
                                            <button class="btn btn-danger btn-sm eliminarArticulo" data-id="' . $id_carrito . '">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>';
                                    $total_carrito += $total_parcial;
                                }

                                echo '
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" align="right"><strong>Total:</strong></td>
                                                <td colspan="2">$' . number_format($total_carrito, 2) . '</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>';

                                // Mostrar tarjetas en móviles
                                echo '<div class="d-lg-none">';
                                foreach ($articulos_carrito as $articulo) {
                                    $id_carrito = $articulo['id_carrito'];
                                    $nombre = $articulo['nombre'];
                                    $cantidad = $articulo['cantidad'];
                                    $precio = $articulo['precio'];
                                    $imagen_url = str_replace("../", "", $articulo['imagen_url']) ? str_replace("../", "", $articulo['imagen_url']) : 'https://dummyimage.com/100x100/dee2e6/6c757d.jpg';
                                    $total_parcia = $precio * $cantidad;

                                    echo '
                                    <div class="card mb-3">
                                        <div class="row no-gutters">
                                            <div class="col-md-4">
                                                <img src="' . $imagen_url . '" class="card-img" alt="' . $nombre . '">
                                            </div>
                                            <div class="col-md-8">
                                                <div class="card-body">
                                                    <h5 class="card-title">' . $nombre . '</h5>
                                                    <p class="card-text"><strong>Precio Unitario:</strong> $' . number_format($precio, 2) . '</p>
                                                    <p class="card-text"><strong>Cantidad:</strong> 
                                                        <button class="btn btn-sm btn-danger restarCantidad" data-id="' . $id_carrito . '">-</button>
                                                        ' . $cantidad . '
                                                        <button class="btn btn-sm btn-success agregarCantidad" data-id="' . $id_carrito . '">+</button>
                                                    </p>
                                                    <p class="card-text"><strong>Total Parcial:</strong> $' . number_format($total_parcia, 2) . '</p>
                                                    <button class="btn btn-danger btn-sm eliminarArticulo" data-id="' . $id_carrito . '">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
                                }
                                echo '
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h5><strong>Total del Carrito:</strong> $' . number_format($total_carrito, 2) . '</h5>
                                    </div>
                                </div>
                                </div>';
                            } else {
                                echo '<p>El carrito está vacío.</p>';
                            }
                        } else {
                            echo '<p>Por favor, inicia sesión para ver tu carrito.</p>';
                        }
                        ?>
                    </div>
                </div>
                <br>
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <a href="finalizar_compra.php" class="btn btn-success btn-block">Finalizar Compra</a>
                    </div>
                </div>
            </div>
        </section>




        <!-- Footer-->
        <footer class="py-5 bg-dark">
            <div class="container"><p class="m-0 text-center text-white">Copyright &copy; josueeb2001@gmail.com</p></div>
        </footer>

    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap core JavaScript-->
    <script src="admin/vendor/jquery/jquery.min.js"></script>
    <script src="admin/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="admin/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="admin/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="admin/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="admin/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="admin/js/demo/datatables-demo.js"></script>
    </body>


<Script>
    document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.agregarCantidad').forEach(button => {
        button.addEventListener('click', function() {
            const id_carrito = this.getAttribute('data-id');
            actualizarCantidad(id_carrito, 'sumar');
        });
    });

    document.querySelectorAll('.restarCantidad').forEach(button => {
    button.addEventListener('click', function() {
        const id_carrito = this.getAttribute('data-id');
        const cantidadElement = this.nextSibling; // Elemento con la cantidad actual
        let cantidad = parseInt(cantidadElement.textContent.trim());

        if (cantidad === 1) {
            Swal.fire({
                title: '¿Quieres eliminar este artículo del carrito?',
                text: "La cantidad es 1, al restar se eliminará.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    actualizarCantidad(id_carrito, 'restar');
                }
            });
        } else {
            actualizarCantidad(id_carrito, 'restar');
        }
    });
});


    // Manejar eliminar artículo
    document.querySelectorAll('.eliminarArticulo').forEach(button => {
        button.addEventListener('click', function() {
            const id_carrito = this.getAttribute('data-id');

            Swal.fire({
                title: '¿Quieres eliminar este artículo del carrito?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarArticulo(id_carrito);
                }
            });
            
        });
    });


    function actualizarCantidad(id_carrito, accion) {

        const formData = new FormData();
        formData.append('id_carrito', id_carrito);
        formData.append('accion', accion);
        formData.append('funcion', 'actualizarCantidadCarrito');

        fetch('controller/ArticuloController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                location.reload();
            } else {
                alert('Error al actualizar la cantidad');
            }
        });
    }

    function eliminarArticulo(id_carrito) {

        const formData = new FormData();
        formData.append('id_carrito', id_carrito);
        formData.append('funcion', 'eliminarArticuloCarrito');

        fetch('controller/ArticuloController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                location.reload();
            } else {
                alert('Error al eliminar el artículo');
            }
        });
    }
});

</Script>



</html>
