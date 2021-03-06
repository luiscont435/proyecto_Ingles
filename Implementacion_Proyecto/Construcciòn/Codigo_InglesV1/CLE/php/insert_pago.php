<?php
session_start();
include('../php/conexion.php');
date_default_timezone_set('America/Mexico_City');

$Promo = $conn->real_escape_string($_POST['valorPromo']);
$Tipo_Campio = $conn->real_escape_string($_POST['valorTipo_Campio']);
$Tipo = $conn->real_escape_string($_POST['valorTipo']);
$Cantidad = $conn->real_escape_string($_POST['valorCantidad']);
$Descripcion = $conn->real_escape_string($_POST['valorDescripcion']);
$IdCliente = $conn->real_escape_string($_POST['valorIdCliente']);
if ($Tipo == 'Mensualidad'){
  $TelTipo = '';
  $Cotejamiento = 0;
}elseif($Tipo == 'Otros Pagos'){
  $TelTipo = '';
  $Cotejamiento = 0;
}else {
  $TelTipo = $conn->real_escape_string($_POST['valorTipoTel']);
  $Tipo = $TelTipo;
  $Cotejamiento = 1;
}
$fecha_corte = mysqli_fetch_array(mysqli_query($conn, 'SELECT * FROM clientes WHERE id_cliente='.$IdCliente));
$Fecha_db = $fecha_corte['fecha_corte'];
$Fecha_hoy = date('Y-m-d');

if($Fecha_hoy<=$Fecha_db){
  $Fecha = $fecha_corte['fecha_corte'];
}else{
  $Fecha = date('Y-m-d');  
}

if($Promo == 'si'){
  $nuevafecha = strtotime('+12 month', strtotime($Fecha));
  $FechaCorte = date('Y-m-05', $nuevafecha);
}else{
  $nuevafecha = strtotime('+1 month', strtotime($Fecha));
  $FechaCorte = date('Y-m-05', $nuevafecha);
}

$cambia_fecha = $FechaCorte;
$id_user = $_SESSION['user_id'];

//Variable vacía (para evitar los E_NOTICE)
$mensaje = "";

if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM pagos WHERE id_cliente = $IdCliente AND descripcion = '$Descripcion' AND cantidad='$Cantidad' AND fecha='$Fecha_hoy'"))>0){
	$mensaje = '<script>M.toast({html:"Ya se encuentra un pago registrado con los mismos valores el día de hoy.", classes: "rounded"})</script>';
}else{
if ($Tipo_Campio == "Credito") {
  mysqli_query($conn,"INSERT INTO deudas(id_cliente, cantidad, fecha_deuda, descripcion, usuario) VALUES ($IdCliente, '$Cantidad', '$Fecha_hoy', '$Descripcion', $id_user)");
}
//o $consultaBusqueda sea igual a nombre + (espacio) + apellido
$sql = "INSERT INTO pagos (id_cliente, descripcion, cantidad, fecha, tipo, id_user, corte, tipo_cambio, Cotejado) VALUES ($IdCliente, '$Descripcion', '$Cantidad', '$Fecha_hoy', '$Tipo', $id_user, 0, '$Tipo_Campio', '$Cotejamiento')";

if(mysqli_query($conn, $sql)){
	$mensaje = '<script>M.toast({html:"El pago se dió de alta satisfcatoriamente.", classes: "rounded"})</script>';
   $cliente = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM clientes WHERE id_cliente = $IdCliente"));
  if($cliente['fecha_corte']<$FechaCorte){
      mysqli_query($conn, "UPDATE clientes SET fecha_corte='$FechaCorte' WHERE id_cliente='$IdCliente'");
    }

  ?>
  <script>
    id_cliente = <?php echo $IdCliente; ?>;
    var a = document.createElement("a");
      a.target = "_blank";
      a.href = "../php/activar_pago.php?id="+id_cliente;
      a.click();
  </script>
  <?php   
}else{
	$mensaje = '<script>M.toast({html:"Ha ocurrido un error.", classes: "rounded"})</script>';	
  }
}
echo $mensaje;
?>
<div id="mostrar_pagos">
    <table class="bordered highlight responsive-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Cantidad</th>
        <th>Tipo</th>
        <th>Descripción</th>
        <th>Usuario</th>
        <th>Fecha</th>
        <th>Imprimir</th>
        <th>Borrar</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $sql_pagos = "SELECT * FROM pagos WHERE id_cliente = ".$IdCliente." ORDER BY id_pago DESC";
    $resultado_pagos = mysqli_query($conn, $sql_pagos);
    $aux = mysqli_num_rows($resultado_pagos);
    if($aux>0){
    while($pagos = mysqli_fetch_array($resultado_pagos)){
      $id_user = $pagos['id_user'];
      $user = mysqli_fetch_array(mysqli_query($conn, "SELECT user_name FROM users WHERE user_id = '$id_user'"));
    ?>
      <tr>
        <td><b><?php echo $aux;?></b></td>
        <td>$<?php echo $pagos['cantidad'];?></td>
        <td><?php echo $pagos['tipo'];?></td>
        <td><?php echo $pagos['descripcion'];?></td>
        <td><?php echo $user['user_name'];?></td>
        <td><?php echo $pagos['fecha'];?></td>
        <td><a onclick="imprimir(<?php echo $pagos['id_pago'];?>);" class="btn btn-floating pink waves-effect waves-light"><i class="material-icons">print</i></a>
        </td>
        <td><a onclick="borrar(<?php echo $pagos['id_pago'];?>);" class="btn btn-floating red darken-4 waves-effect waves-light"><i class="material-icons">delete</i></a>
        </td>
      </tr>
    <?php
    $aux--;
    }//fin while
    }else{
      echo "<center><b><h3>Este cliente aún no ha registrado pagos</h3></b></center>";
    }
    ?>        
    </tbody>
  </table>
</div>
<?php 
mysqli_close($conn);
?>
