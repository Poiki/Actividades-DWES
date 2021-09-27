<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
</head>

<body>
    <?php

    $db = new mysqli("localhost", "root", "", "videoclub");

    // Miramos el valor de la variable "action", si existe. Si no, le asignamos una acción por defecto
    if (isset($_REQUEST["action"])) {
        $action = $_REQUEST["action"];
    } else {
        $action = "mostrarListaPeliculas";  // Acción por defecto
    }

    // CONTROL DE FLUJO PRINCIPAL
    // El programa saltará a la sección del switch indicada por la variable "action"
    switch ($action) {

            // --------------------------------- MOSTRAR LISTA DE PELICULAS ----------------------------------------

        case "mostrarListaPeliculas":
            echo "<h1>Videoclub</h1>";

            // Buscamos todos las peliculas del videoclub
            if ($result = $db->query("SELECT * FROM peliculas
                                        LEFT JOIN actuan ON peliculas.idPelicula = actuan.idPelicula
                                        LEFT JOIN personas ON actuan.idPersona = personas.idPersona
                                        ORDER BY peliculas.titulo")) {

                // La consulta se ha ejecutado con éxito. Vamos a ver si contiene registros
                if ($result->num_rows != 0) {
                    // La consulta ha devuelto registros: vamos a mostrarlos

                    // Primero, el formulario de búsqueda
                    echo "<form action='index.php'>
                                <input type='hidden' name='action' value='buscarPeliculas'>
                                <input type='text' name='textoBusqueda'>
                                <input type='submit' value='Buscar'>
                                </form><br>";

                    // Ahora, la tabla con los datos de las peliculas
                    echo "<table border ='1'>";
                    while ($pelicula = $result->fetch_object()) {
                        echo "<tr>";
                        echo "<td>" . $pelicula->titulo . "</td>";
                        echo "<td>" . $pelicula->genero . "</td>";
                        echo "<td>" . $pelicula->pais . "</td>";
                        echo "<td>" . $pelicula->anyo . "</td>";
                        echo "<td><img src='" . $pelicula->cartel . "' width='100'></td>";
                        echo "<td><a href='index.php?action=formularioModificarPelicula&idPelicula=" . $pelicula->idPelicula . "'>Modificar</a></td>";
                        echo "<td><a href='index.php?action=borrarPelicula&idPelicula=" . $pelicula->idPelicula . "'>Borrar</a></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    // La consulta no contiene registros
                    echo "No se encontraron datos";
                }
            } else {
                // La consulta ha fallado
                echo "Error al tratar de recuperar los datos de la base de datos. Por favor, inténtelo más tarde";
            }
            echo "<p><a href='index.php?action=formularioInsertarPeliculas'>Nuevo</a></p>";
            break;

            // --------------------------------- FORMULARIO ALTA DE PELICULAS ----------------------------------------

        case "formularioInsertarPeliculas":
            echo "<h1>Modificación de Peliculas</h1>";

            // Creamos el formulario con los campos de la pelicula
            echo "<form action = 'index.php' method = 'get'>
                    Título:<input type='text' name='titulo'><br>
                    Género:<input type='text' name='genero'><br>
                    País:<input type='text' name='pais'><br>
                    Año:<input type='text' name='anyo'><br>
                    Cartel:<input type='text' name='cartel'><br>
                    Actores:<input type= 'text' name='actores'";

            // Añadimos un selector para el id de los actores
            $result = $db->query("SELECT * FROM personas");
            echo "Actores: <select name='personas[]' multiple='true'>";
            while ($pelicula = $result->fetch_object()) {
                echo "<option value='" . $pelicula->idPersona . "'>" . $pelicula->nombre . " " . $pelicula->apellidos . "</option>";
            }
            echo "</select>";
            echo "<a href='index.php?action=formularioInsertarActores'>Añadir nuevo</a><br>";

            // Finalizamos el formulario
            echo "  <input type='hidden' name='action' value='insertarPelicula'>
					<input type='submit'>
				</form>";
            echo "<p><a href='index.php'>Volver</a></p>";

            break;

            // --------------------------------- INSERTAR PELICULAS ----------------------------------------

        case "insertarPelicula":
            echo "<h1>Alta de peliculas</h1>";

            // Vamos a procesar el formulario de alta de peliculas
            // Primero, recuperamos todos los datos del formulario
            $titulo = $_REQUEST["titulo"];
            $genero = $_REQUEST["genero"];
            $pais = $_REQUEST["pais"];
            $anyo = $_REQUEST["anyo"];
            $cartel = $_REQUEST["cartel"];
            $actores = $_REQUEST["actores"];

            // Lanzamos el INSERT contra la BD.
            echo "INSERT INTO peliculas (titulo,genero,pais,anyo,cartel) VALUES ('$titulo','$genero', '$pais', '$anyo', '$cartel')";
            $db->query("INSERT INTO peliculas (titulo,genero,pais,anyo,cartel) VALUES ('$titulo','$genero', '$pais', '$anyo', '$cartel')");
            if ($db->affected_rows == 1) {
                // Si la inserción del peliculas ha funcionado, continuamos insertando en la tabla "actuan"
                // Tenemos que averiguar qué idPelicula se ha asignado a la pelicula que acabamos de insertar
                $result = $db->query("SELECT MAX(idPelicula) AS ultimoIdPelicula FROM peliculas");
                $idPelicula = $result->fetch_object()->ultimoIdpelicula;
                // Ya podemos insertar todos los actores junto con la pelicula en "actuan"
                foreach ($actores as $idPersona) {
                    $db->query("INSERT INTO actuan(idPelicula, idPersona) VALUES('$idPelicula', '$idPersona')");
                }
                echo "Pelicula insertado con éxito";
            } else {
                // Si la inserción de la pelicula ha fallado, mostramos mensaje de error
                echo "Ha ocurrido un error al insertar la pelicula. Por favor, inténtelo más tarde.";
            }
            echo "<p><a href='index.php'>Volver</a></p>";

            break;

            // --------------------------------- BORRAR PELICULAS ----------------------------------------

        case "borrarPelicula":
            echo "<h1>Borrar pelicula</h1>";

            // Recuperamos el id de la pelicula y lanzamos el DELETE contra la BD
            $idPelicula = $_REQUEST["idPelicula"];
            $db->query("DELETE FROM peliculas WHERE idPelicula = '$idPelicula'");

            // Mostramos mensaje con el resultado de la operación
            if ($db->affected_rows == 0) {
                echo "Ha ocurrido un error al borrar la pelicula. Por favor, inténtelo de nuevo";
            } else {
                echo "Pelicula borrado con éxito";
            }
            echo "<p><a href='index.php'>Volver</a></p>";

            break;

            // --------------------------------- FORMULARIO MODIFICAR PELICULAS ----------------------------------------

        case "formularioModificarPelicula":
            echo "<h1>Modificación de peliculas</h1>";

            // Recuperamos el id de la pelicula que vamos a modificar y sacamos el resto de sus datos de la BD
            $idPelicula = $_REQUEST["idPelicula"];
            $result = $db->query("SELECT * FROM peliculas WHERE peliculas.idPelicula = '$idPelicula'");
            $pelicula = $result->fetch_object();

            // Creamos el formulario con los campos de la pelicula
            // y lo rellenamos con los datos que hemos recuperado de la BD
            echo "<form action = 'index.php' method = 'get'>
				    <input type='hidden' name='idPelicula' value='$idPelicula'>
                    Título:<input type='text' name='titulo' value='$pelicula->titulo'><br>
                    Género:<input type='text' name='genero' value='$pelicula->genero'><br>
                    País:<input type='text' name='pais' value='$pelicula->pais'><br>
                    Año:<input type='text' name='anyo' value='$pelicula->anyo'><br>
                    Cartel:<input type='image' name='cartel' src='$pelicula->cartel' width=100px align='center'><br>";

            // Vamos a añadir un selector para el id de los actores.
            // Para que salgan preseleccionados los actores de la pelicula que estamos modificando, vamos a buscar
            // también a esos actores.
            $todosLosActores = $db->query("SELECT * FROM personas");  // Obtener todos los actores
            $actoresPelicula = $db->query("SELECT idPersona FROM actuan WHERE idPelicula = '$idPelicula'");             // Obtener solo los actores de la pelicula que estamos buscando
            // Vamos a convertir esa lista de actores de la pelicula en un array de ids de personas
            $listaActoresPelicula = array();
            while ($actor = $actoresPelicula->fetch_object()) {
                $listaActoresPelicula[] = $actor->idPersona;
            }

            // Ya tenemos todos los datos para añadir el selector de actores al formulario
            echo "Actores: <select name='actor[]' multiple size='3'>";
            while ($pelicula = $todosLosActores->fetch_object()) {
                if (in_array($pelicula->idPersona, $listaActoresPelicula))
                    echo "<option value='$pelicula->idPersona' selected>$pelicula->nombre $pelicula->apellidos</option>";
                else
                    echo "<option value='$pelicula->idPersona'>$pelicula->nombre $pelicula->apellidos</option>";
            }
            echo "</select>";

            // Por último, un enlace para crear un nuevo actor
            echo "<a href='index.php?action=formularioInsertarActores'>Añadir nuevo</a><br>";

            // Finalizamos el formulario
            echo "  <input type='hidden' name='action' value='modificarPelicula'>
                    <input type='submit'>
                  </form>";
            echo "<p><a href='index.php'>Volver</a></p>";

            break;

            // --------------------------------- MODIFICAR PELICULAS ----------------------------------------

        case "modificarPeliculas":
            echo "<h1>Modificación de peliculas</h1>";

            // Vamos a procesar el formulario de modificación de peliculas
            // Primero, recuperamos todos los datos del formulario
            $idPelicula = $_REQUEST["idPelicula"];
            $titulo = $_REQUEST["titulo"];
            $genero = $_REQUEST["genero"];
            $pais = $_REQUEST["pais"];
            $anyo = $_REQUEST["anyo"];
            $cartel = $_REQUEST["cartel"];
            $actores = $_REQUEST["actor"];

            // Lanzamos el UPDATE contra la base de datos.
            $db->query("UPDATE peliculas SET
							titulo = '$titulo',
							genero = '$genero',
							pais = '$pais',
							anyo = '$anyo',
							cartel = '$cartel'
							WHERE idPelicula = '$idPelicula'");

            if ($db->affected_rows == 1) {
                // Si la modificación de la pelicula ha funcionado, continuamos actualizando la tabla "actuan".
                // Primero borraremos todos los registros de la pelicula actual y luego los insertaremos de nuevo
                $db->query("DELETE FROM actuan WHERE idPelicula = '$idPelicula'");
                // Ya podemos insertar todos los actores junto con la pelicula en "actuan"
                foreach ($actores as $idPersona) {
                    $db->query("INSERT INTO actuan(idPelicula, idPersona) VALUES('$idPelicula', '$idPersona')");
                }
                echo "Pelicula actualizada con éxito";
            } else {
                // Si la modificación del pelicula ha fallado, mostramos mensaje de error
                echo "Ha ocurrido un error al modificar el pelicula. Por favor, inténtelo más tarde.";
            }
            echo "<p><a href='index.php'>Volver</a></p>";
            break;

            // --------------------------------- BUSCAR PELICULAS ----------------------------------------

        case "buscarPelicula":
            // Recuperamos el texto de búsqueda de la variable de formulario
            $textoBusqueda = $_REQUEST["textoBusqueda"];
            echo "<h1>Resultados de la búsqueda: \"$textoBusqueda\"</h1>";

            // Buscamos los peliculas de la biblioteca que coincidan con el texto de búsqueda
            if ($result = $db->query("SELECT * FROM peliculas
					INNER JOIN actuan ON peliculas.idpelicula = actuan.idPelicula
					INNER JOIN personas ON actuan.idPersona = personas.idPersona
					WHERE peliculas.titulo LIKE '%$textoBusqueda%'
					OR peliculas.genero LIKE '%$textoBusqueda%'
					OR personas.nombre LIKE '%$textoBusqueda%'
					OR personas.apellidos LIKE '%$textoBusqueda%'
					ORDER BY peliculas.titulo")) {

                // La consulta se ha ejecutado con éxito. Vamos a ver si contiene registros
                if ($result->num_rows != 0) {
                    // La consulta ha devuelto registros: vamos a mostrarlos
                    // Primero, el formulario de búsqueda
                    echo "<form action='index.php'>
								<input type='hidden' name='action' value='buscarPelicula'>
                            	<input type='text' name='textoBusqueda'>
								<input type='submit' value='Buscar'>
                          </form><br>";
                    // Después, la tabla con los datos
                    echo "<table border ='1'>";
                    while ($pelicula = $result->fetch_object()) {
                        echo "<tr>";
                        echo "<td>" . $pelicula->titulo . "</td>";
                        echo "<td>" . $pelicula->genero . "</td>";
                        echo "<td>" . $pelicula->nombre . "</td>";
                        echo "<td>" . $pelicula->apellidos . "</td>";
                        echo "<td>" . $pelicula->cartel . "</td>";
                        echo "<td><a href='index.php?action=formularioModificarPeliculas&idpelicula=" . $pelicula->idPelicula . "'>Modificar</a></td>";
                        echo "<td><a href='index.php?action=borrarPeliculas&idPelicula=" . $pelicula->idPelicula . "'>Borrar</a></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    // La consulta no contiene registros
                    echo "No se encontraron datos";
                }
            } else {
                // La consulta ha fallado
                echo "Error al tratar de recuperar los datos de la base de datos. Por favor, inténtelo más tarde";
            }
            echo "<p><a href='index.php?action=formularioInsertarPeliculas'>Nuevo</a></p>";
            echo "<p><a href='index.php'>Volver</a></p>";
            break;

            // --------------------------------- ACTION NO ENCONTRADA ----------------------------------------

        default:
            echo "<h1>Error 404: página no encontrada</h1>";
            echo "<a href='index.php'>Volver</a>";
            break;
    } // switch

    ?>

</body>

</html>