<?php
session_start();
require 'vendor/autoload.php';
use Mailgun\Mailgun;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
date_default_timezone_set("America/Mexico_City");
$tittle_section = "Edición Campaña";
$kt_header_tab_3 = "active";
include_once("header.php");
include_once("functions.php");
include_once("dbconnect.php");
$available_location_fields = '';
if ($_SESSION['usrid'] == '13199' || $_SESSION['usrid'] == '11388' || $_SESSION['usrid'] ==
'4485' || $_SESSION['usrid'] == '8583'){
$available_location_fields = 'text';
}else{
$available_location_fields = 'hidden';
}
/* Aqui validamos si la campaña que se quiere
editar pertenece a un producto asignado al usuario en sesion */
$producto_id = $_GET['prod_id'];
$campaign_id = $_GET['campaign_id'];
$parameters = [];
if($_SESSION['acclvl'] <= 2){
$parameters[] = $campaign_id;
$parameters[] = $producto_id;
$parameters[] = $_SESSION['clientid'];
$accsql = "SELECT
campaigns.id
FROM
campaigns JOIN products ON campaigns.pro_id=products.id
WHERE
campaigns.id = ? AND products.id = ? AND products.cli_id = ?";
}else{
$parameters[] = $campaign_id;
$parameters[] = $producto_id;
$parameters[] = $_SESSION['usrid'];
$parameters[] = 1;
if($_SESSION['clientid'] != $_SESSION['last_clid']){
$accsql = "SELECT
campaigns.id
FROM
products_shared JOIN products ON products_shared.pro_id=products.id
JOIN campaigns ON products.id=campaigns.pro_id
WHERE
campaigns.id = ? AND products.id = ?
AND products_shared.con_id = ? AND products_shared.status = ?";
}else{
$accsql = "SELECT
campaigns.id
FROM
products_assigned JOIN products ON products_assigned.pro_id=products.id
JOIN campaigns ON products.id=campaigns.pro_id
WHERE
campaigns.id = ? AND products.id = ?
AND products_assigned.con_id = ? AND products_assigned.status = ?";
}
}
$stmt = $db->prepare($accsql);
$stmt->execute($parameters);
if($stmt->rowCount() > 0){
$producto_id = $_GET['prod_id'];
$campaign_id = $_GET['campaign_id'];
}else{
echo "<script>window.location.href='products-config.php'</script>";
exit;
}
//change cam
if (isset($_POST['campaign_change'])) {
//cargamos variables de cambio por post para evitar efecto humano en url
$cam_name = $_POST['cam_name'];
$producto_id_change = $_POST['prod_id'];
$crm_id = $_POST['crm_id'];
$crm_id_old = $_POST['crm_id_old'];
$campaign_id_change = $_POST['campaign_id'];
$cam_atndays = $_POST['cam_atndays'];
$cam_notify = $_POST['cam_notify'];
$cam_shift = $_POST['cam_shift'];
$cam_role = $_POST['inlineRadioOptions'];
$cam_shift_old = $_POST['cam_shift_old'];
$cam_duplicates_old = $_POST['cam_duplicates_old'];
$cam_dupdays_old = $_POST['cam_dupdays_old'];
$cam_dupdays = $_POST['cam_dupdays'];
$iscore_old = $_POST['iscore_old'];
$iscore = $_POST['iscore'];
$cam_role_old = $_POST['cam_role_old'];
$cam_status_old = $_POST['cam_status'];
$cam_notify_old = $_POST['cam_notify'];
$cam_quality_old = $_POST['cam_quali'];
$hora_inicio_old = $_POST['cam_atnstart_old'];
$hora_fin_old = $_POST['cam_atnend_old'];
$wab_bot_old = $_POST['wab_bot_old'];
$wab_bot = '';
$wab_msg = '';
if(isset($_POST['wab_bot'])){
$wab_bot = $_POST['wab_bot'];
}else{
$wab_bot = '';
}
if(isset($_POST['wab_msg'])) {
$wab_msg = $_POST['wab_msg'];
}else{
$wab_msg = '';
}
$crm_con = $_POST['crm-con'];
$cam_mail_template = $_POST['mail_template'];
$cam_mail_template_old = $_POST['cam_mail_template_old'];
$channel_crmid_change = $_POST['crmid-channel-edit'];
$channel_crmid_old = $_POST['channel_crmid_old'];
$grade_options = $_POST['grade_bundle'];
$grade_options_old = $_POST['grade_options_old'];
$cam_manual_old = $_POST['cam_manual_old'];
$address_position_campaign_old = $_POST['address_position_campaign_old'];
$latitud_position_campaign_old = $_POST['latitud_position_campaign_old'];
$longitud_position_campaign_old = $_POST['longitud_position_campaign_old'];
$name_position_campaign_old = $_POST['name_position_campaign_old'];
$address_position_campaign_edit = $_POST['address-location-edit'];
$latitud_position_campaign_edit = $_POST['latitud-location-edit'];
$longitud_position_campaign_edit = $_POST['longitud-location-edit'];
$name_position_campaign_edit = $_POST['name-location-edit'];
$autoassign_time_ia_edit = 0;
$autoassign_lead_ia_edit = 0;
if(isset($_POST['autoassign_lead_ia_edit']) && $_POST['autoassign_lead_ia_edit'] == 'on'){
$autoassign_lead_ia_edit = 1;
}else{
$autoassign_lead_ia_edit = 0;
}
if(isset($_POST['autoassign_time_ia_edit']) && $_POST['autoassign_time_ia_edit'] != ''){
$autoassign_time_ia_edit = $_POST['autoassign_time_ia_edit'];
}else{
$autoassign_time_ia_edit = 0;
}
$autoassign_time_ia_old = $_POST['autoassign_time_ia_old'];
$autoassign_lead_ia_old = $_POST['autoassign_lead_ia_old'];
//estatus de campaña activa inactiva
//echo "Tenemos nuevo y viejo - role: $cam_role y $cam_role_old, shift: $cam_shift y
$cam_shift_old";
if (isset($_POST['cam_status_change'])) {
$cam_status_change = $_POST['cam_status_change'];
if ($cam_status_change=='on') {
$cam_status_change = 1;
}
else {
$cam_status_change = 0;
}
}
else { $cam_status_change = 0; }
//Begin: Validacion para dar el valor de Quality
if (isset($_POST['cam_quality_change'])) {
$cam_quality_change = $_POST['cam_quality_change'];
if ($cam_quality_change=='on') {
$cam_quality_change = 1;
}
else {
$cam_quality_change = 0;
}
}
else { $cam_quality_change = 0; }
//End: Validacion para dar el valor de Quality
$hora_inicio = $_POST['hora_inicio'];
$hora_fin = $_POST['hora_fin'];
if (isset($_POST['atencion_lunes'])) {$atencion_lunes = "1,";} else {$atencion_lunes="";}
if (isset($_POST['atencion_martes'])) { $atencion_martes = "2,";} else
{$atencion_martes="";}
if (isset($_POST['atencion_miercoles'])) { $atencion_miercoles = "3,";} else
{$atencion_miercoles="";}
if (isset($_POST['atencion_jueves'])) { $atencion_jueves = "4,";} else
{$atencion_jueves="";}
if (isset($_POST['atencion_viernes'])) { $atencion_viernes = "5,";} else
{$atencion_viernes="";}
if (isset($_POST['atencion_sabado'])) { $atencion_sabado = "6,";} else
{$atencion_sabado="";}
if (isset($_POST['atencion_domingo'])) { $atencion_domingo = "7";} else
{$atencion_domingo="";}
//ahora juntamos las variables para formar el cam_atndays (1,2,3,4,5,6,7)
$atencion_semanal = $atencion_lunes.$atencion_martes.$atencion_miercoles.
$atencion_jueves.$atencion_viernes.$atencion_sabado.$atencion_domingo;
//si no esta completa a 7 quitamos ultimo coma
if (substr($atencion_semanal, -1) == ",") {$atencion_semanal = substr($atencion_semanal,
0, -1);}
if (isset($_POST['mailclient_notify'])) { $mailclient_notify = "1,";} else {$mailclient_notify =
"";}
if (isset($_POST['maillead_notify'])) { $maillead_notify = "2,";} else {$maillead_notify = "";}
if (isset($_POST['whatsclient_notify'])) { $whatsclient_notify = "3,";} else
{$whatsclient_notify = "";}
if (isset($_POST['whatsleads_notify'])) { $whatsleads_notify = "4,";} else
{$whatsleads_notify = "";}
$exampleRadios = $_POST['exampleRadios'];
if ($exampleRadios == 0) { $exampleRadios = "";}
$cam_manual = $_POST['cam_manual'];
$cam_duplicates = $_POST['cam_duplicates'];
//ahora juntamos las variables para formar el cam_notify (1,2,3,4,5) o (1,2,3,4,6)
$cam_notify = $mailclient_notify.$maillead_notify.$whatsclient_notify.$whatsleads_notify.
$exampleRadios;
//si no esta completa a 7 quitamos ultimo coma
if (substr($cam_notify, -1) == ",") {$cam_notify = substr($cam_notify, 0, -1);}
$timestamp = date("Y-m-d H:i:s");
//echo "Tenemos status radio: ".$cam_status_change.", hora_inicio: ".$hora_inicio.",
hora_fin: ".$hora_fin;
//echo "Tenemos dias semana: ".$atencion_semanal.". id $producto_id_change y cam
$campaign_id_change";
//echo "Tenemos notificaciones: ".$exampleRadios.":ultimo valor, total :".$cam_notify;
$stmt_update = $db->prepare("UPDATE campaigns
SET crm_id=?, cam_status=?, cam_atnstart=?, cam_atnend=?, cam_atndays=?,
cam_notify=?, cam_quali=?, cam_role=?, cam_shift=?, cam_manual=?,
cam_duplicates=?,
cam_dupdays=?, mail_template=?, crm=?, wab_bot=?, wab_messages=?,
grade_options=?,
channel_crmid=?, iscore=?, address_position_campaign=?,
latitud_position_campaign=?,
longitud_position_campaign=?, name_position_campaign=?, autoassign_lead_ia=?,
autoassign_time_ia=?
WHERE pro_id = ? and id = ?");
$stmt_update->bindParam(1, $crm_id);
$stmt_update->bindParam(2, $cam_status_change);
$stmt_update->bindParam(3, $hora_inicio);
$stmt_update->bindParam(4, $hora_fin);
$stmt_update->bindParam(5, $atencion_semanal);
$stmt_update->bindParam(6, $cam_notify);
$stmt_update->bindParam(7, $cam_quality_change);
$stmt_update->bindParam(8, $cam_role);
$stmt_update->bindParam(9, $cam_shift);
$stmt_update->bindParam(10, $cam_manual);
$stmt_update->bindParam(11, $cam_duplicates);
$stmt_update->bindParam(12, $cam_dupdays);
$stmt_update->bindParam(13, $cam_mail_template);
$stmt_update->bindParam(14, $crm_con);
$stmt_update->bindParam(15, $wab_bot);
$stmt_update->bindParam(16, $wab_msg);
$stmt_update->bindParam(17, $grade_options);
$stmt_update->bindParam(18, $channel_crmid_change);
$stmt_update->bindParam(19, $iscore);
$stmt_update->bindParam(20, $address_position_campaign_edit);
$stmt_update->bindParam(21, $latitud_position_campaign_edit);
$stmt_update->bindParam(22, $longitud_position_campaign_edit);
$stmt_update->bindParam(23, $name_position_campaign_edit);
$stmt_update->bindParam(24, $autoassign_lead_ia_edit);
$stmt_update->bindParam(25, $autoassign_time_ia_edit);
$stmt_update->bindParam(26, $producto_id_change);
$stmt_update->bindParam(27, $campaign_id_change);
//preparamos variables log
$url = "campaign-edit.php";
$item = "campaign_id";
$abc = "c";
if ($iscore <= 20) {
if ($stmt_update->execute()) {
//echo "<br>Mensaje recibido";
//echo "\nPDO::errorInfo():\n";
//print_r($db->errorInfo());
$msg = '<h6 class="card-title">Campaña actualizada con Exito: '.
$cam_name.'</h6>';
//agregamos log
if($cam_manual != $cam_manual_old){
$old_val = $cam_manual_old;
$new_val = $cam_manual;
$comments = "Actualizacion Campaña -cam manual- $cam_name
($campaign_id_change)";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($grade_options != $grade_options_old) {
$old_val = $grade_options_old;
$new_val = $grade_options;
$comments = "Actualizacion Campaña -grade options- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($cam_atndays != $atencion_semanal) {
$old_val = $cam_atndays;
$new_val = $atencion_semanal;
$comments = "Actualizacion Campaña -dias- $cam_name ($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($crm_id_old != $crm_id){
$old_val = $crm_id_old;
$new_val = $crm_id;
$comments ($campaign_id_change) ";
$url,$old_val,$new_val,$msg);
= "Actualizacion Campaña -CRM ID- $cam_name
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
}
if ($cam_status_old != $cam_status_change) {
$old_val = $cam_status_old;
$new_val = $cam_status_change;
$comments = "Actualizacion Campaña -status- $cam_name ($campaign_id_change)
";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($hora_inicio_old != $hora_inicio){
$old_val = $hora_inicio_old;
$new_val = $hora_inicio;
$comments = "Actualizacion Campaña -hora inicio- $cam_name
($campaign_id_change) ";
$url,$old_val,$new_val,$msg);
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
}
if ($hora_fin_old != $hora_fin){
$old_val = $hora_fin_old;
$new_val = $hora_fin;
$comments = "Actualizacion Campaña -hora fin- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($cam_notify_old != $cam_notify) {
$old_val = $cam_notify_old;
$new_val = $cam_notify;
$comments = "Actualizacion Campaña -notificaciones- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($cam_quality_old != $cam_quality_change) {
$old_val = $cam_quality_old;
$new_val = $cam_quality_change;
$comments = "Actualizacion campaña -quality- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($cam_shift_old != $cam_shift) {
$old_val = $cam_shift_old;
$new_val = $cam_shift;
$comments = "Actualizacion Campaña -turno- $cam_name ($campaign_id_change)
";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($cam_role_old != $cam_role) {
$old_val = $cam_role_old;
$new_val = $cam_role;
$comments = "Actualizacion Campaña -asignacion- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
//Barrido
//Primero traemos a los vendedores
$parametersSA[] = $producto_id;
$getSellersAsigned="SELECT con_id as SellerAsigned FROM products_assigned
WHERE pro_id = ? AND status = 1"; //Seleccionamos vendedores asignados al producto
$stmtSA = $db->prepare($getSellersAsigned);
$stmtSA->execute($parametersSA);
$data_SA = $stmtSA->fetchALL(PDO::FETCH_ASSOC);
//echo(var_dump($stmt));
foreach($data_SA as $returndata){
//Contacts Assigned
$parameters_SA = [];
$parameters_SA[] = $campaign_id;
$parameters_SA[] = $returndata['SellerAsigned'];
$checkcassigned = "SELECT contacts_assigned.id FROM contacts_assigned
WHERE contacts_assigned.cam_id=? and contacts_assigned.con_id=?"; // Selecciona
cuantas veces se ha asignado un vendedor a una campaña
$stmt = $db->prepare($checkcassigned);
$stmt->execute($parameters_SA);
if ($stmt->rowCount() > 0) {//Si se ha asignado un vendedor a una campaña
Hace 2 updates
//Ya existen los registros solo hay que actualizar
$parameters4 = [];
$updatecassigned = "UPDATE contacts_assigned SET
contacts_assigned.status=1, contacts_assigned.wheel=0, contacts_assigned.date_add=?,
contacts_assigned.visible=1 WHERE contacts_assigned.cam_id=? and
contacts_assigned.con_id=? and contacts_assigned.role_shift=0";
$updatecassigned2 = "UPDATE contacts_assigned SET
contacts_assigned.wheel=0, contacts_assigned.date_add=?, contacts_assigned.visible=1
WHERE contacts_assigned.cam_id=? and contacts_assigned.con_id=? and
contacts_assigned.role_shift!=0";
$parameters4[] = $timestamp;
$parameters4[] = $campaign_id;
$parameters4[] = $returndata['SellerAsigned'];
$stmt = $db->prepare($updatecassigned);
$stmt->execute($parameters4);
//Fix para poner visibilidad en 1 para los otros shifts
$stmt2 = $db->prepare($updatecassigned2);
$stmt2->execute($parameters4);
}
else{
//No existen los registros hay que insertar
$parameters8 = [];
$parameters8[] = $returndata['SellerAsigned'];
$parameters8[] = $campaign_id;
//echo(var_dump($parameters8));
$insertcassigned0="INSERT INTO contacts_assigned (con_id, cam_id,
role_shift, status) VALUES (?,?,0,0)";
$stmt = $db->prepare($insertcassigned0);
$stmt->execute($parameters8);
$insertcassigned1="INSERT INTO contacts_assigned (con_id, cam_id,
role_shift, status) VALUES (?,?,1,0)";
$stmt2 = $db->prepare($insertcassigned1);
$stmt2->execute($parameters8);
$insertcassigned2="INSERT INTO contacts_assigned (con_id, cam_id,
role_shift, status) VALUES (?,?,2,0)";
$stmt3 = $db->prepare($insertcassigned2);
$stmt3->execute($parameters8);
}
}
}
if ($cam_dupdays_old != $cam_dupdays) {
$old_val = $cam_dupdays_old;
$new_val = $cam_dupdays;
$comments = "Actualizacion Campaña -dias duplicados- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($wab_bot_old != $wab_bot){
$old_val = $wab_bot_old;
$new_val = $wab_bot;
$comments = "Actualizacion campaña -Wab Bot- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($cam_mail_template_old != $cam_mail_template){
$old_val = $cam_mail_template_old;
$new_val = $cam_mail_template;
$comments = "Actualizacion campaña -Mail template- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if($channel_crmid_change != $channel_crmid_old){
$old_val = $channel_crmid_old;
$new_val = $channel_crmid_change;
$comments = "Actualziacion campaña -channel_crmid- $cam_name
($campaign_id_change)";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($iscore_old != $iscore) {
$old_val = $iscore_old;
$new_val = $iscore;
$comments = "Actualizacion campaña -iscore- $cam_name ($campaign_id_change)
";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($address_position_campaign_old != $address_position_campaign_edit) {
$old_val = $address_position_campaign_old;
$new_val = $address_position_campaign_edit;
$comments = "Actualizacion campaña -Dirección- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($latitud_position_campaign_old != $latitud_position_campaign_edit) {
$old_val = $latitud_position_campaign_old;
$new_val = $latitud_position_campaign_edit;
$comments = "Actualizacion campaña -Latitud- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($longitud_position_campaign_old != $longitud_position_campaign_edit) {
$old_val = $longitud_position_campaign_old;
$new_val = $longitud_position_campaign_edit;
$comments = "Actualizacion campaña -Longitud- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($name_position_campaign_old != $name_position_campaign_edit) {
$old_val = $name_position_campaign_old;
$new_val = $name_position_campaign_edit;
$comments = "Actualizacion campaña -Nombre de ubicación- $cam_name
($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($autoassign_lead_ia_old != $autoassign_lead_ia_edit) {
$old_val = $autoassign_lead_ia_old;
$new_val = $autoassign_lead_ia_edit;
$comments = "Actualizacion campaña -Asignación automática de vendedor-
$cam_name ($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
if ($autoassign_time_ia_old != $autoassign_time_ia_edit) {
$old_val = $autoassign_time_ia_old;
$new_val = $autoassign_time_ia_edit;
$comments = "Actualizacion campaña -tiempo de asignación automática de
vendedor- $cam_name ($campaign_id_change) ";
writeModLog($campaign_id_change,$_SESSION['usrid'],$item,$abc,$comments,
$url,$old_val,$new_val,$msg);
}
}
else{
//$msg = '<h6 class="card-title">No se actualizó</h6>';
//Si no se realiza la actualización
}
} else {
$error = "La puntuación del iScore debe ser en un rango de 0 a 20";
$error1 = "Puntuación invalida";
echo '<label class="alert-danger alert" role="alert">' . $error . '</label>';
}
}
$query_camp = "SELECT id, crm_id, pro_id, iscore, cam_name, cam_manual, cam_channel,
cam_type,
cam_status, cam_bu, cam_notify, cam_quali, cam_atnstart, cam_atnend,
cam_atndays,
cam_duplicates, cam_dupdays, mail_template, crm, cam_role, cam_shift,
grade_options,
cam_menu, accessToken, date_add, date_down, date_updated, wab_messages,
wab_bot, channel_crmid,
longitud_position_campaign,
bot_id_sendpulse
FROM campaigns
WHERE pro_id = ? and id = ?";
$parameters = [];
$parameters[] = $producto_id;
$parameters[] = $campaign_id;
$stmt3 = $db->prepare($query_camp);
$stmt3->execute($parameters);
address_position_campaign, latitud_position_campaign,
name_position_campaign, autoassign_lead_ia, autoassign_time_ia,
//conciliacion de guardado
if (isset($_POST['change_assigned'])) {
$msg = "";
$usr_id2 = $_POST['usr_id2'];
$timestamp = date("Y-m-d H:i:s");
$prod_assig_id_s0 = "";
$prod_assig_id_s1 = "";
$prod_assig_id_s2 = "";
//echo "<pre>";
//print_r($usr_id2);
//echo "</pre>";
$button_a = $_POST['change_assigned'];
$cam_name = $_POST['cam_name'];
$producto_id_change = $_POST['campaign_id'];
if (isset($_POST['estatus_usr_encendido1'])) {$estatus_usr_encendido =
$_POST['estatus_usr_encendido1'];} else {$estatus_usr_encendido ="";}
//echo "Tenemos boton: $button_a, campaña: $cam_name, producto:
$producto_id_change, estatus";
//echo "<pre>";
//print_r($estatus_usr_encendido);
//echo "</pre>";
if ($estatus_usr_encendido!="") {
$comma_separated = implode("','", $estatus_usr_encendido);
$vendedores_modificados1 = implode(",", $estatus_usr_encendido);
}
else {
}
$comma_separated = "";
$vendedores_modificados1 = "";
//echo "Empleados seleccionados $comma_separated";
//echo "Query: UPDATE contacts_assigned SET status = 1 WHERE con_id in
('$comma_separated') and cam_id = $producto_id_change";
if ($estatus_usr_encendido != "") {
foreach($estatus_usr_encendido as $vendedor) {
//---------------------------------------------ASIGNACION TODO EL
DIA------------------------------------------
if (isset($_POST['shift0_atencionlunes_'.$vendedor]))
{$shift0_atencionlunes = "1,"; $shift0_atencionlunes_txt = "Lunes,";} else
{$shift0_atencionlunes=""; $shift0_atencionlunes_txt = "";}
if (isset($_POST['shift0_atencionmartes_'.$vendedor]))
{$shift0_atencionmartes = "2,"; $shift0_atencionmartes_txt = "Martes,";} else
{$shift0_atencionmartes=""; $shift0_atencionmartes_txt = "";}
if (isset($_POST['shift0_atencionmiercoles_'.$vendedor]))
{$shift0_atencionmiercoles = "3,"; $shift0_atencionmiercoles_txt = "Miercoles,";} else
{$shift0_atencionmiercoles=""; $shift0_atencionmiercoles_txt = "";}
if (isset($_POST['shift0_atencionjueves_'.$vendedor]))
{$shift0_atencionjueves = "4,"; $shift0_atencionjueves_txt = "Jueves,";} else
{$shift0_atencionjueves=""; $shift0_atencionjueves_txt = "";}
if (isset($_POST['shift0_atencionviernes_'.$vendedor]))
{$shift0_atencionviernes = "5,"; $shift0_atencionviernes_txt = "Viernes,";} else
{$shift0_atencionviernes=""; $shift0_atencionviernes_txt = "";}
if (isset($_POST['shift0_atencionsabado_'.$vendedor]))
{$shift0_atencionsabado = "6,"; $shift0_atencionsabado_txt = "Sabado,";} else
{$shift0_atencionsabado=""; $shift0_atencionsabado_txt = "";}
if (isset($_POST['shift0_atenciondomingo_'.$vendedor]))
{$shift0_atenciondomingo = "7"; $shift0_atenciondomingo_txt = "Domingo,";} else
{$shift0_atenciondomingo=""; $shift0_atenciondomingo_txt = "";}
//ahora juntamos las variables para formar el atndays
(1,2,3,4,5,6,7)
$shift0_atencionsemanal = $shift0_atencionlunes.
$shift0_atencionmartes.$shift0_atencionmiercoles.$shift0_atencionjueves.
$shift0_atencionviernes.$shift0_atencionsabado.$shift0_atenciondomingo;
$shift0_atencionsemanal_texto = $shift0_atencionlunes_txt.
$shift0_atencionmartes_txt.$shift0_atencionmiercoles_txt.$shift0_atencionjueves_txt.
$shift0_atencionviernes_txt.$shift0_atencionsabado_txt.$shift0_atenciondomingo_txt;
//si no esta completa a 7 quitamos ultimo coma
if (substr($shift0_atencionsemanal, -1) == ",")
{$shift0_atencionsemanal = substr($shift0_atencionsemanal, 0, -1);}
if (substr($shift0_atencionsemanal_texto, -1) == ",")
{$shift0_atencionsemanal_texto = substr($shift0_atencionsemanal_texto, 0, -1);}
//Actualizamos los dia de la semana por usuario asignado
//Si esta asignacion no existe debe ser creada, por lo que
haremos la revision primero
$query_prodrole = "SELECT count(id) as conteo FROM
contacts_assigned WHERE con_id = $vendedor AND cam_id = $producto_id_change AND
role_shift = 0";
$stmt_p = $db->prepare($query_prodrole);
$stmt_p->execute();
while($data_p = $stmt_p->fetch(PDO::FETCH_ASSOC)) {
$prod_assig_id_s0 = $data_p['conteo'];
}
//si ya esta asignado hacer update, sino hacemos insert
if ($prod_assig_id_s0 == 0){
$stmt_update = $db->prepare("INSERT INTO
contacts_assigned (cam_id, con_id, region_id, role_shift, status, wheel, active_days,
date_assigned) VALUES
(?,?,'0','0','1','0',?,?) ");
$stmt_update->bindParam(1, $producto_id_change);
$stmt_update->bindParam(2, $vendedor);
$stmt_update->bindParam(3, $shift0_atencionsemanal);
$stmt_update->bindParam(4, $timestamp);
//preparamos variables log
$url = "campaign-edit.php";
$item = "cam_id";
$abc = "a";
if ($stmt_update->execute()) {
$msg.= '<h6 class="card-title">Vendedor '.
$vendedor.' asignado a los dias: '.$shift0_atencionsemanal_texto.' turno todo el dia.</h6>';
//agregamos log
$old_val = "";
$new_val = $shift0_atencionsemanal;
$comments = "Creacion asignacion Turno todo
el dia Usuario ".$vendedor." dias: $shift0_atencionsemanal_texto.";
writeModLog($producto_id_change,
$_SESSION['usrid'],$item,$abc,$comments,$url,$old_val,$new_val,$msg);
}
}
else {
$stmt_update = $db->prepare("UPDATE
contacts_assigned SET active_days = '$shift0_atencionsemanal'
WHERE
con_id = $vendedor
AND cam_id =
$producto_id_change AND role_shift = 0");
//preparamos variables log
$url = "campaign-edit.php";
$item = "cam_id";
$abc = "c";
if ($stmt_update->execute()) {
$msg.= '<h6 class="card-title">Vendedor '.
$vendedor.' asignado a los dias: '.$shift0_atencionsemanal_texto.' turno todo el dia.</h6>';
//agregamos log
$old_val = "";
$new_val = $shift0_atencionsemanal;
$comments = "Actualizacion Turno todo el dia,
Usuario ".$vendedor." asignado a los dias: $shift0_atencionsemanal_texto.";
writeModLog($producto_id_change,
$_SESSION['usrid'],$item,$abc,$comments,$url,$old_val,$new_val,$msg);
}
}
//---------------------------------------------ASIGNACION
MATUTINO------------------------------------------
if (isset($_POST['shift1_atencionlunes_'.$vendedor]))
{$shift1_atencionlunes = "1,"; $shift1_atencionlunes_txt = "Lunes,";} else
{$shift1_atencionlunes=""; $shift1_atencionlunes_txt = "";}
if (isset($_POST['shift1_atencionmartes_'.$vendedor]))
{$shift1_atencionmartes = "2,"; $shift1_atencionmartes_txt = "Martes,";} else
{$shift1_atencionmartes=""; $shift1_atencionmartes_txt = "";}
if (isset($_POST['shift1_atencionmiercoles_'.$vendedor]))
{$shift1_atencionmiercoles = "3,"; $shift1_atencionmiercoles_txt = "Miercoles,";} else
{$shift1_atencionmiercoles=""; $shift1_atencionmiercoles_txt = "";}
if (isset($_POST['shift1_atencionjueves_'.$vendedor]))
{$shift1_atencionjueves = "4,"; $shift1_atencionjueves_txt = "Jueves,";} else
{$shift1_atencionjueves=""; $shift1_atencionjueves_txt = "";}
if (isset($_POST['shift1_atencionviernes_'.$vendedor]))
{$shift1_atencionviernes = "5,"; $shift1_atencionviernes_txt = "Viernes,";} else
{$shift1_atencionviernes=""; $shift1_atencionviernes_txt = "";}
if (isset($_POST['shift1_atencionsabado_'.$vendedor]))
{$shift1_atencionsabado = "6,"; $shift1_atencionsabado_txt = "Sabado,";} else
{$shift1_atencionsabado=""; $shift1_atencionsabado_txt = "";}
if (isset($_POST['shift1_atenciondomingo_'.$vendedor]))
{$shift1_atenciondomingo = "7"; $shift1_atenciondomingo_txt = "Domingo,";} else
{$shift1_atenciondomingo=""; $shift1_atenciondomingo_txt = "";}
//ahora juntamos las variables para formar el atndays
(1,2,3,4,5,6,7)
$shift1_atencionsemanal = $shift1_atencionlunes.
$shift1_atencionmartes.$shift1_atencionmiercoles.$shift1_atencionjueves.
$shift1_atencionviernes.$shift1_atencionsabado.$shift1_atenciondomingo;
$shift1_atencionsemanal_texto = $shift1_atencionlunes_txt.
$shift1_atencionmartes_txt.$shift1_atencionmiercoles_txt.$shift1_atencionjueves_txt.
$shift1_atencionviernes_txt.$shift1_atencionsabado_txt.$shift1_atenciondomingo_txt;
//si no esta completa a 7 quitamos ultimo coma
if (substr($shift1_atencionsemanal, -1) == ",")
{$shift1_atencionsemanal = substr($shift1_atencionsemanal, 0, -1);}
if (substr($shift1_atencionsemanal_texto, -1) == ",")
{$shift1_atencionsemanal_texto = substr($shift1_atencionsemanal_texto, 0, -1);}
//Actualizamos los dia de la semana por usuario asignado
//Si esta asignacion no existe debe ser creada, por lo que
haremos la revision primero
$query_prodrole = "SELECT count(id) as conteo FROM
contacts_assigned WHERE con_id = $vendedor AND cam_id = $producto_id_change AND
role_shift = 1";
$stmt_p = $db->prepare($query_prodrole);
$stmt_p->execute();
while($data_p = $stmt_p->fetch(PDO::FETCH_ASSOC)) {
$prod_assig_id_s1 = $data_p['conteo'];
}
if ($prod_assig_id_s1 == 0){
$stmt_update = $db->prepare("INSERT INTO
contacts_assigned (cam_id, con_id, region_id, role_shift, status, wheel, active_days,
date_assigned) VALUES
(?,?,'0','1','1','0',?,?) ");
$stmt_update->bindParam(1, $producto_id_change);
$stmt_update->bindParam(2, $vendedor);
$stmt_update->bindParam(3, $shift1_atencionsemanal);
$stmt_update->bindParam(4, $timestamp);
//preparamos variables log
$url = "campaign-edit.php";
$item = "cam_id";
$abc = "a";
if ($stmt_update->execute()) {
$msg.= '<h6 class="card-title">Vendedor '.
$vendedor.' asignado dias: '.$shift1_atencionsemanal_texto.' turno matutino.</h6>';
//agregamos log
$old_val = "";
$new_val = $shift1_atencionsemanal;
$comments = "Creacion asignacion Turno
matutino, Usuario ".$vendedor." asignado dias: $shift1_atencionsemanal_texto.";
writeModLog($producto_id_change,
$_SESSION['usrid'],$item,$abc,$comments,$url,$old_val,$new_val,$msg);
}
}
else {
$stmt_update = $db->prepare("UPDATE
contacts_assigned SET active_days = '$shift1_atencionsemanal'
WHERE
con_id = $vendedor
AND cam_id =
$producto_id_change AND role_shift = 1");
//preparamos variables log
$url = "campaign-edit.php";
$item = "cam_id";
$abc = "c";
if ($stmt_update->execute()) {
$msg.= '<h6 class="card-title">Vendedor '.
$vendedor.' asignado dias: '.$shift1_atencionsemanal_texto.' turno matutino.</h6>';
//agregamos log
$old_val = "";
$new_val = $shift0_atencionsemanal;
$comments = "Actualizacion Turno matutino,
Usuario ".$vendedor." asignado dias: $shift1_atencionsemanal_texto.";
writeModLog($producto_id_change,
$_SESSION['usrid'],$item,$abc,$comments,$url,$old_val,$new_val,$msg);
}
}
//---------------------------------------------ASIGNACION
VESPERTINO------------------------------------------
if (isset($_POST['shift2_atencionlunes_'.$vendedor]))
{$shift2_atencionlunes = "1,"; $shift2_atencionlunes_txt = "Lunes,";} else
{$shift2_atencionlunes=""; $shift2_atencionlunes_txt = "";}
if (isset($_POST['shift2_atencionmartes_'.$vendedor]))
{$shift2_atencionmartes = "2,"; $shift2_atencionmartes_txt = "Martes,";} else
{$shift2_atencionmartes=""; $shift2_atencionmartes_txt = "";}
if (isset($_POST['shift2_atencionmiercoles_'.$vendedor]))
{$shift2_atencionmiercoles = "3,"; $shift2_atencionmiercoles_txt = "Miercoles,";} else
{$shift2_atencionmiercoles=""; $shift2_atencionmiercoles_txt = "";}
if (isset($_POST['shift2_atencionjueves_'.$vendedor]))
{$shift2_atencionjueves = "4,"; $shift2_atencionjueves_txt = "Jueves,";} else
{$shift2_atencionjueves=""; $shift2_atencionjueves_txt = "";}
if (isset($_POST['shift2_atencionviernes_'.$vendedor]))
{$shift2_atencionviernes = "5,"; $shift2_atencionviernes_txt = "Viernes,";} else
{$shift2_atencionviernes=""; $shift2_atencionviernes_txt = "";}
if (isset($_POST['shift2_atencionsabado_'.$vendedor]))
{$shift2_atencionsabado = "6,"; $shift2_atencionsabado_txt = "Sabado,";} else
{$shift2_atencionsabado=""; $shift2_atencionsabado_txt = "";}
if (isset($_POST['shift2_atenciondomingo_'.$vendedor]))
{$shift2_atenciondomingo = "7"; $shift2_atenciondomingo_txt = "Domingo,";} else
{$shift2_atenciondomingo=""; $shift2_atenciondomingo_txt = "";}
//ahora juntamos las variables para formar el atndays
(1,2,3,4,5,6,7)
$shift2_atencionsemanal = $shift2_atencionlunes.
$shift2_atencionmartes.$shift2_atencionmiercoles.$shift2_atencionjueves.
$shift2_atencionviernes.$shift2_atencionsabado.$shift2_atenciondomingo;
$shift2_atencionsemanal_texto = $shift2_atencionlunes_txt.
$shift2_atencionmartes_txt.$shift2_atencionmiercoles_txt.$shift2_atencionjueves_txt.
$shift2_atencionviernes_txt.$shift2_atencionsabado_txt.$shift2_atenciondomingo_txt;
//si no esta completa a 7 quitamos ultimo coma
if (substr($shift2_atencionsemanal, -1) == ",")
{$shift2_atencionsemanal = substr($shift2_atencionsemanal, 0, -1);}
if (substr($shift2_atencionsemanal_texto, -1) == ",")
{$shift2_atencionsemanal_texto = substr($shift2_atencionsemanal_texto, 0, -1);}
//Actualizamos los dia de la semana por usuario asignado
//Si esta asignacion no existe debe ser creada, por lo que
haremos la revision primero
$query_prodrole = "SELECT count(id) as conteo FROM
contacts_assigned WHERE con_id = $vendedor AND cam_id = $producto_id_change AND
role_shift = 2";
$stmt_p = $db->prepare($query_prodrole);
$stmt_p->execute();
while($data_p = $stmt_p->fetch(PDO::FETCH_ASSOC)) {
$prod_assig_id_s2 = $data_p['conteo'];
}
if ($prod_assig_id_s2 == 0){
$stmt_update = $db->prepare("INSERT INTO
contacts_assigned (cam_id, con_id, region_id, role_shift, status, wheel, active_days,
date_assigned) VALUES
(?,?,'0','2','1','0',?,?) ");
$stmt_update->bindParam(1, $producto_id_change);
$stmt_update->bindParam(2, $vendedor);
$stmt_update->bindParam(3, $shift2_atencionsemanal);
$stmt_update->bindParam(4, $timestamp);
//preparamos variables log
$url = "campaign-edit.php";
$item = "cam_id";
$abc = "a";
if ($stmt_update->execute()) {
$msg.= '<h6 class="card-title">Vendedor '.
$vendedor.' asignado dias: '.$shift2_atencionsemanal_texto.' turno matutino.</h6>';
//agregamos log
$old_val = "";
$new_val = $shift2_atencionsemanal;
$comments = "Creacion asignacion Turno
matutino, Usuario ".$vendedor." asignado dias: $shift2_atencionsemanal_texto.";
writeModLog($producto_id_change,
$_SESSION['usrid'],$item,$abc,$comments,$url,$old_val,$new_val,$msg);
}
}
else {
$stmt_update = $db->prepare("UPDATE
contacts_assigned SET active_days = '$shift2_atencionsemanal'
WHERE
con_id = $vendedor
AND cam_id =
$producto_id_change AND role_shift = 2 ");
//preparamos variables log
$url = "campaign-edit.php";
$item = "cam_id";
$abc = "c";
if ($stmt_update->execute()) {
$msg.= '<h6 class="card-title">Vendedor '.
$vendedor.' asignado dias: '.$shift2_atencionsemanal_texto.' turno matutino.</h6>';
//agregamos log
$old_val = "";
$new_val = $shift2_atencionsemanal;
$comments = "Actualizacion Turno matutino,
Usuario ".$vendedor." asignado dias: $shift2_atencionsemanal_texto.";
writeModLog($producto_id_change,
$_SESSION['usrid'],$item,$abc,$comments,$url,$old_val,$new_val,$msg);
}
}
}
}
$stmt_update = $db->prepare("UPDATE contacts_assigned SET status = 1,
date_add = '$timestamp' WHERE con_id in ('$comma_separated') and cam_id =
$producto_id_change");
if ($stmt_update->execute()) {
$msg.= '<h6 class="card-title">Vendedor(es) asignados(s): '.
$vendedores_modificados1.'</h6>';
}
$stmt_update2 = $db->prepare("UPDATE contacts_assigned SET status = 0,
date_down = '$timestamp' WHERE con_id not in ('$comma_separated') and cam_id =
$producto_id_change");
if ($stmt_update2->execute()) {
$msg.= '<h6 class="card-title">Vendedor(es) desasignados(s).</h6>';
}
//Guardar el rol en el log de roles
$logroll = $db->prepare("INSERT INTO roll_logs (roll_type, type_id, usr_id, new_roll,
date_add) VALUES ('campaign',?,?,?,?)");
$logroll->bindParam(1, $producto_id_change);
$logroll->bindParam(2, $_SESSION['usrid']);
$logroll->bindParam(3, $_SESSION['newRoll']);
$logroll->bindParam(4, $timestamp);
if ($logroll->execute()) {
$msg.= 'Rol guardado correctamente en Logs';
}
//Enviar correo de confirmación a cambio de rol y copias
$mgClient = Mailgun::create(getenv('MAILGUN_API_KEY', true) ?:
getenv('MAILGUN_API_KEY'));
$domain = getenv('MAILGUN_DOMAIN', true) ?: getenv('MAILGUN_DOMAIN');
$clientdata = array(
'from' => 'Beefast <info@app.beefast.pro>',
'to' => $_SESSION['username'],
'subject' => 'Cambio de Rol de Guardias Beefast',
'template' => 'new_roll',
'v:username' => $_SESSION['name'],
'v:product' => $cam_name,
'v:newroll' => $_SESSION['newRoll'],
'v:mailtype' => "App",
'o:tag' => array('ClientMails', 'NewRoll'));
// send copies to IT and Creator
$clientdata['cc']= 'evas@daad.pro';
// Make the call to the client.
$result = $mgClient->messages()->send($domain, $clientdata);
$msg.= 'Correo de confirmación enviado';
}
?>
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
<!--begin::Subheader-->
<div class="subheader py-2 py-lg-4 subheader-transparent" id="kt_subheader">
<div class="container d-flex align-items-center justify-content-between flex-wrap flex-
sm-nowrap">
<!--begin::Details-->
<div class="d-flex align-items-center flex-wrap mr-2">
<!--begin::Title-->
<h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Campa&ntilde;as</h5>
<!--end::Title-->
<!--begin::Separator-->
<div class="subheader-separator subheader-separator-ver mt-2 mb-2 mr-5 bg-
gray-200"></div>
<!--end::Separator-->
<!--begin::Search Form-->
<!--<div class="d-flex align-items-center" id="kt_subheader_search">
<form class="ml-5">
<div class="input-group input-group-sm input-group-solid" style="max-
width: 175px">
<input type="text" class="form-control" id="kt_subheader_search_form"
placeholder="Search..." />
<div class="input-group-append">
<span class="input-group-text">
<span class="svg-icon">
<!- begin::Svg Icon | path:https://storage.googleapis.com/
rt2yno3b84zfdu26tgwh/app/assets/media/svg/icons/General/Search.svg-->
<!-- <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://
www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
<g stroke="none" stroke-width="1" fill="none" fill-
rule="evenodd">
<rect x="0" y="0" width="24" height="24" />
<path d="M14.2928932,16.7071068 C13.9023689,16.3165825
13.9023689,15.6834175 14.2928932,15.2928932 C14.6834175,14.9023689
15.3165825,14.9023689 15.7071068,15.2928932 L19.7071068,19.2928932
C20.0976311,19.6834175 20.0976311,20.3165825 19.7071068,20.7071068
C19.3165825,21.0976311 18.6834175,21.0976311 18.2928932,20.7071068
L14.2928932,16.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" />
<path d="M11,16 C13.7614237,16 16,13.7614237 16,11
C16,8.23857625 13.7614237,6 11,6 C8.23857625,6 6,8.23857625 6,11 C6,13.7614237
8.23857625,16 11,16 Z M11,18 C7.13400675,18 4,14.8659932 4,11 C4,7.13400675
7.13400675,4 11,4 C14.8659932,4 18,7.13400675 18,11 C18,14.8659932 14.8659932,18
11,18 Z" fill="#000000" fill-rule="nonzero" />
</g>
</svg> -->
<!--end::Svg Icon-->
<!-- </span>
<i class="flaticon2-search-1 icon-sm"></i> // coment
</span>
</div>
</div>
</form>
</div> -->
<!--end::Search Form-->
<!--begin::Group Actions-->
<div class="d-flex- align-items-center flex-wrap mr-2 d-none"
id="kt_subheader_group_actions">
<div class="text-dark-50 font-weight-bold">
<span id="kt_subheader_group_selected_rows">23</span>Selected:</div>
<div class="d-flex ml-6">
<div class="dropdown mr-2"
id="kt_subheader_group_actions_status_change">
<button type="button" class="btn btn-light-primary font-weight-bolder
btn-sm dropdown-toggle" data-toggle="dropdown">Update Status</button>
<div class="dropdown-menu p-0 m-0 dropdown-menu-sm">
<ul class="navi navi-hover pt-3 pb-4">
<li class="navi-header font-weight-bolder text-uppercase text-primary
font-size-lg pb-0">Change status to:</li>
<li class="navi-item">
<a href="#" class="navi-link" data-toggle="status-change" data-
status="1">
<span class="navi-text">
<span class="label label-light-success label-inline font-weight-
bold">Approved</span>
</span>
</a>
</li>
<li class="navi-item">
<a href="#" class="navi-link" data-toggle="status-change" data-
status="2">
<span class="navi-text">
<span class="label label-light-danger label-inline font-weight-
bold">Rejected</span>
</span>
</a>
</li>
<li class="navi-item">
<a href="#" class="navi-link" data-toggle="status-change" data-
status="3">
<span class="navi-text">
<span class="label label-light-warning label-inline font-weight-
bold">Pending</span>
</span>
</a>
</li>
<li class="navi-item">
<a href="#" class="navi-link" data-toggle="status-change" data-
status="4">
<span class="navi-text">
<span class="label label-light-info label-inline font-weight-
bold">On Hold</span>
</span>
</a>
</li>
</ul>
</div>
</div>
<button class="btn btn-light-success font-weight-bolder btn-sm mr-2"
id="kt_subheader_group_actions_fetch" data-toggle="modal" data-
target="#kt_datatable_records_fetch_modal">Fetch Selected</button>
<button class="btn btn-light-danger font-weight-bolder btn-sm mr-2"
id="kt_subheader_group_actions_delete_all">Delete All</button>
</div>
</div>
<!--end::Group Actions-->
</div>
<!--end::Details-->
<!--begin::Toolbar-->
<div class="d-flex align-items-center">
<!--begin::Button-->
<a href="#" class=""></a>
<!--end::Button-->
<!--begin::Button-->
<!-- <a href="custom/apps/projects/add-project.html" class="btn btn-light-primary
font-weight-bold ml-2">Crear Campaña</a> -->
<!--end::Button-->
</div>
<!--end::Toolbar-->
</div>
</div>
<!--end::Subheader-->
<!--begin::Notice-->
<div class="alert alert-custom alert-white alert-shadow gutter-b" role="alert">
<div class="alert-icon">
<span class="svg-icon svg-icon-primary svg-icon-xl">
<!--begin::Svg Icon | path:https://storage.googleapis.com/rt2yno3b84zfdu26tgwh/
app/assets/media/svg/icons/Tools/Compass.svg-->
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/
xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
<rect x="0" y="0" width="24" height="24" />
<path d="M7.07744993,12.3040451 C7.72444571,13.0716094
8.54044565,13.6920474 9.46808594,14.1079953 L5,23 L4.5,18 L7.07744993,12.3040451 Z
M14.5865511,14.2597864 C15.5319561,13.9019016 16.375416,13.3366121
17.0614026,12.6194459 L19.5,18 L19,23 L14.5865511,14.2597864 Z M12,3.55271368e-14
C12.8284271,3.53749572e-14 13.5,0.671572875 13.5,1.5 L13.5,4 L10.5,4 L10.5,1.5
C10.5,0.671572875 11.1715729,3.56793164e-14 12,3.55271368e-14 Z" fill="#000000"
opacity="0.3" />
<path d="M12,10 C13.1045695,10 14,9.1045695 14,8 C14,6.8954305
13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 C10,9.1045695 10.8954305,10 12,10 Z
M12,13 C9.23857625,13 7,10.7614237 7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3
17,5.23857625 17,8 C17,10.7614237 14.7614237,13 12,13 Z" fill="#000000" fill-rule="nonzero"
/>
</g>
</svg>
<!--end::Svg Icon-->
</span>
</div>
<div class="alert-text">
<?php
echo $msg;
if (isset($_POST['campaign_change']) || isset($_POST['change_assigned'])) {
}
?>
</div>
</div>
<!--end::Notice-->
<!--begin::Entry-->
<div class="d-flex flex-column-fluid">
<!--begin::Container-->
<div class="container-fluid">
<?php
while($data3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
$id = $data3['id'];
$crm_id = $data3['crm_id'];
$pro_id = $data3['pro_id'];
$iscore = $data3['iscore'];
$cam_name = $data3['cam_name'];
$cam_channel = $data3['cam_channel'];
$cam_type = $data3['cam_type'];
$cam_status = $data3['cam_status'];
$cam_bu = $data3['cam_bu'];
$cam_role = $data3['cam_role'];
$cam_shift = $data3['cam_shift'];
$cam_notify = array();
$cam_notify = explode(",", $data3['cam_notify']);
$cam_notify2 = $data3['cam_notify'];
$cam_quali = $data3['cam_quali'];
$cam_atnstart = $data3['cam_atnstart'];
$cam_atnend = $data3['cam_atnend'];
$cam_atndays = array();
$cam_atndays = explode(",", $data3['cam_atndays']);
$cam_atndays2 = $data3['cam_atndays'];
$cam_duplicates = $data3['cam_duplicates'];
$pro_id";
$cam_dupdays = $data3['cam_dupdays'];
$cam_mail_template = $data3['mail_template'];
$crm = $data3['crm'];
$cam_accessToken= $data3['accessToken'];
$date_add = $data3['date_add'];
$date_down = $data3['date_down'];
$date_updated = $data3['date_updated'];
$wab_bot = $data3['wab_bot'];
$wab_messages = $data3['wab_messages'];
$startcam_atnstart = date("h:i A", strtotime($cam_atnstart));
$startcam_atnend = date("h:i A", strtotime($cam_atnend));
$cam_manual = $data3['cam_manual'];
$cam_menu = $data3['cam_menu'];
$grade_options = $data3['grade_options'];
$channel_crmid = $data3['channel_crmid'];
$address_position_campaign = $data3['address_position_campaign'];
$latitud_position_campaign = $data3['latitud_position_campaign'];
$longitud_position_campaign = $data3['longitud_position_campaign'];
$name_position_campaign = $data3['name_position_campaign'];
$autoassign_lead_ia = $data3['autoassign_lead_ia'];
$autoassign_time_ia = $data3['autoassign_time_ia'];
$bot_id_sendpulse = $data3['bot_id_sendpulse'];
//Obtenemos el id del cliente para poder obtener el bundle WAB messages
$query_qgci = "SELECT id, cli_id, pro_name FROM products WHERE id =
$stmt_qgci = $db->prepare($query_qgci);
$stmt_qgci->execute();
while($data_qgci = $stmt_qgci->fetch(PDO::FETCH_ASSOC)){
$cli_id = $data_qgci['cli_id'];
}
?>
<!--begin::Card-->
<div class="card card-custom gutter-b">
<div class="card-body">
<div class="d-flex">
<!--begin: Pic-->
<div class="flex-shrink-0 mr-7 mt-lg-0 mt-3">
<div class="symbol symbol-50 symbol-lg-120">
<img alt="<?php echo $cam_channel ?>" src="<?php echo
$_SESSION['image_service'].'img/mails/'.$cam_channel ?>.png" />
</div>
<div class="symbol symbol-50 symbol-lg-120 symbol-primary d-none">
<span class="font-size-h3 symbol-label font-weight-boldest">JM</
span>
</div>
</div>
<!--end: Pic-->
<!--begin: Info-->
<div class="flex-grow-1">
<!--begin: Title-->
<div class="d-flex align-items-center justify-content-between flex-wrap">
<div class="mr-3">
<!--begin::Name-->
<a href="#" class="d-flex align-items-center text-dark text-hover-
primary font-size-h5 font-weight-bold mr-3"><?php echo $cam_name.' - '.$cam_channel; ?>
<?php if ($cam_status==1) { ?>
<i class="flaticon2-correct text-success icon-md ml-2">Activo</
i>
<?php } else { ?>
<i class="flaticon2-correct text-danger icon-md ml-2">Inactivo</
i>
<?php } ?>
</a>
<!--end::Name-->
<!--begin::Contacts-->
<div class="d-flex flex-wrap my-2">
<a href="#" class="text-muted text-hover-primary font-weight-bold
mr-lg-8 mr-5 mb-lg-0 mb-2">
<span class="svg-icon svg-icon-md svg-icon-gray-500 mr-1">
<!--begin::Svg Icon | path:https://storage.googleapis.com/
rt2yno3b84zfdu26tgwh/app/assets/media/svg/icons/Communication/Mail-notification.svg-->
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://
www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
<g stroke="none" stroke-width="1" fill="none" fill-
rule="evenodd">
<rect x="0" y="0" width="24" height="24" />
<path d="M21,12.0829584 C20.6747915,12.0283988
20.3407122,12 20,12 C16.6862915,12 14,14.6862915 14,18 C14,18.3407122
14.0283988,18.6747915 14.0829584,19 L5,19 C3.8954305,19 3,18.1045695 3,17 L3,8
C3,6.8954305 3.8954305,6 5,6 L19,6 C20.1045695,6 21,6.8954305 21,8 L21,12.0829584 Z
M18.1444251,7.83964668 L12,11.1481833 L5.85557487,7.83964668 C5.4908718,7.6432681
5.03602525,7.77972206 4.83964668,8.14442513 C4.6432681,8.5091282
4.77972206,8.96397475 5.14442513,9.16035332 L11.6444251,12.6603533
C11.8664074,12.7798822 12.1335926,12.7798822 12.3555749,12.6603533
L18.8555749,9.16035332 C19.2202779,8.96397475 19.3567319,8.5091282
19.1603533,8.14442513 C18.9639747,7.77972206 18.5091282,7.6432681
18.1444251,7.83964668 Z" fill="#000000" />
<circle fill="#000000" opacity="0.3" cx="19.5" cy="17.5"
r="2.5" />
</g>
</svg>
<!--end::Svg Icon-->
</span></a>
<a href="#" class="text-muted text-hover-primary font-weight-bold
mr-lg-8 mr-5 mb-lg-0 mb-2">
<span class="svg-icon svg-icon-md svg-icon-gray-500 mr-1">
<!--begin::Svg Icon | path:https://storage.googleapis.com/
rt2yno3b84zfdu26tgwh/app/assets/media/svg/icons/General/Lock.svg-->
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://
www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
<g stroke="none" stroke-width="1" fill="none" fill-
rule="evenodd">
<mask fill="white">
<use xlink:href="#path-1" />
</mask>
<g />
<path d="M7,10 L7,8 C7,5.23857625 9.23857625,3 12,3
C14.7614237,3 17,5.23857625 17,8 L17,10 L18,10 C19.1045695,10 20,10.8954305 20,12
L20,18 C20,19.1045695 19.1045695,20 18,20 L6,20 C4.8954305,20 4,19.1045695 4,18 L4,12
C4,10.8954305 4.8954305,10 6,10 L7,10 Z M12,5 C10.3431458,5 9,6.34314575 9,8 L9,10
L15,10 L15,8 C15,6.34314575 13.6568542,5 12,5 Z" fill="#000000" />
</g>
</svg>
<!--end::Svg Icon-->
</span><?php echo $cam_bu; ?></a>
<a href="#" class="text-muted text-hover-primary font-weight-
bold">
<span class="svg-icon svg-icon-md svg-icon-gray-500 mr-1">
<!--begin::Svg Icon | path:https://storage.googleapis.com/
rt2yno3b84zfdu26tgwh/app/assets/media/svg/icons/Map/Marker2.svg-->
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://
www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
<g stroke="none" stroke-width="1" fill="none" fill-
rule="evenodd">
<rect x="0" y="0" width="24" height="24" />
<path d="M9.82829464,16.6565893
C7.02541569,15.7427556 5,13.1079084 5,10 C5,6.13400675 8.13400675,3 12,3
C15.8659932,3 19,6.13400675 19,10 C19,13.1079084 16.9745843,15.7427556
14.1717054,16.6565893 L12,21 L9.82829464,16.6565893 Z M12,12 C13.1045695,12
14,11.1045695 14,10 C14,8.8954305 13.1045695,8 12,8 C10.8954305,8 10,8.8954305 10,10
C10,11.1045695 10.8954305,12 12,12 Z" fill="#000000" />
</g>
</svg>
<!--end::Svg Icon-->
</span><?php echo $cam_atndays2; ?> Dias Enlace</a>
<a href="#" class="text-muted text-hover-primary font-weight-
bold">
<span class="svg-icon svg-icon-md svg-icon-gray-500 mr-1">
<!--begin::Svg Icon | path:https://storage.googleapis.com/
rt2yno3b84zfdu26tgwh/app/assets/media/svg/icons/Map/Marker2.svg-->
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://
www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
<g stroke="none" stroke-width="1" fill="none" fill-
rule="evenodd">
<rect x="0" y="0" width="24" height="24" />
<path d="M9.82829464,16.6565893
C7.02541569,15.7427556 5,13.1079084 5,10 C5,6.13400675 8.13400675,3 12,3
C15.8659932,3 19,6.13400675 19,10 C19,13.1079084 16.9745843,15.7427556
14.1717054,16.6565893 L12,21 L9.82829464,16.6565893 Z M12,12 C13.1045695,12
14,11.1045695 14,10 C14,8.8954305 13.1045695,8 12,8 C10.8954305,8 10,8.8954305 10,10
C10,11.1045695 10.8954305,12 12,12 Z" fill="#000000" />
</g>
</svg>
<!--end::Svg Icon-->
</span>
<?php echo $cam_notify2; ?> Notificaciones</a>
</div>
<!--end::Contacts-->
</div>
<div class="my-lg-0 my-1">
text-uppercase mr-3">Online</a>
<?php if ($cam_type == 'Online') {?>
<a href="#" class="btn btn-sm btn-success font-weight-bolder
<?php } else { ?>
<a href="#" class="btn btn-sm btn-danger font-weight-bolder text-
uppercase">Oﬄine</a>
<?php } ?>
<a href="campaign-menu.php?campaign_id=<?php echo
$campaign_id; ?>&prod_id=<?php echo $producto_id; ?>&campaign_menu=<?php echo
$cam_menu; ?>" class="btn btn-sm btn-danger font-weight-bolder text-uppercase">Editar
Menu Whatsapp</a>
</div>
</div>
<!--end: Title-->
<!--begin: Content-->
<div class="d-flex align-items-center flex-wrap justify-content-between">
<div class="d-flex flex-wrap align-items-center py-2">
<div class="d-flex align-items-center mr-10">
<div class="mr-6">
<div class="font-weight-bold mb-2">Horario Inicio</div>
<span class="btn btn-sm btn-text btn-light-primary text-
uppercase font-weight-bold"><?php echo $startcam_atnstart; ?></span>
</div>
<div class="">
<div class="font-weight-bold mb-2">Horario Fin</div>
<span class="btn btn-sm btn-text btn-light-danger text-
uppercase font-weight-bold"><?php echo $startcam_atnend; ?></span>
</div>
</div>
</div>
</div>
<!--end: Content-->
</div>
<!--end: Info-->
</div>
<div class="separator separator-solid my-7"></div>
<!--begin: Items-->
<div class="d-flex align-items-center flex-wrap">
<!--begin: Item-->
<div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
<span class="mr-4">
<i class="flaticon-calendar-3 icon-2x text-muted font-weight-bold"></i>
</span>
<div class="d-flex flex-column text-dark-75">
<span class="font-weight-bolder font-size-sm">Fecha Alta</span>
<span class="font-weight-bolder font-size-h5">
<span class="text-dark-50 font-weight-bold"><?php $date_add2 =
date("F, jS Y", strtotime($date_add)); echo $date_add2; ?></span>
</div>
</div>
<!--end: Item-->
<!--begin: Item-->
<?php if ($date_down !== NULL) { ?>
<div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
<span class="mr-4">
<i class="flaticon2-calendar icon-2x text-muted font-weight-bold"></i>
</span>
<div class="d-flex flex-column text-dark-75">
<span class="font-weight-bolder font-size-sm">Campaña Apagada</
span>
<span class="font-weight-bolder font-size-h5">
<span class="text-dark-50 font-weight-bold"><?php $date_down2 =
date("F, jS Y", strtotime($date_down)); echo $date_down; ?></span>
</div>
</div>
<?php }?>
<!--end: Item-->
<!--begin: Item-->
<div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
<span class="mr-4">
<i class="flaticon-pie-chart icon-2x text-muted font-weight-bold"></i>
</span>
<div class="d-flex flex-column text-dark-75">
<span class="font-weight-bolder font-size-sm">Id Campaña</span>
<span class="font-weight-bolder font-size-h5">
<span class="text-dark-50 font-weight-bold"><?php echo $id; ?></
span>
</div>
</div>
<!--end: Item-->
<!--begin: Item-->
<div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
<span class="mr-4">
<i class="flaticon-file-2 icon-2x text-muted font-weight-bold"></i>
</span>
<div class="d-flex flex-column flex-lg-fill">
<span class="text-dark-75 font-weight-bolder font-size-sm">Token</
span>
<a href="#" class="text-primary font-weight-bolder"><?php echo
$cam_accessToken; ?></a>
</div>
</div>
<!--end: Item-->
<!--begin: Item-->
<div class="d-flex align-items-center flex-lg-fill my-1">
<span class="mr-4">
<i class="flaticon-network icon-2x text-muted font-weight-bold"></i>
</span>
<div class="symbol-group symbol-hover">
<span class="text-dark-75 font-weight-bolder font-size-
sm">Vendedores <br>Asignados</span>
</div>
<div class="symbol-group symbol-hover">
<?php
//20210322 - si esta campaña es asgnacion por producto mostrar
products_assigned, sino contact_assigned
$query_asignacion = "SELECT a.id as id, a.usr_mail as usr_mail,
a.usr_name as usr_name, a.usr_phone as usr_phone,
b.status as statuscam FROM users a left join
contacts_assigned b on
b.con_id = a.id WHERE b.cam_id = ? AND b.status=1";
$parameters_asig = [];
$parameters_asig[] = $id;
$stmt_asig = $db->prepare($query_asignacion);
$stmt_asig_2= $db->prepare($query_asignacion);
$stmt_asig->execute($parameters_asig);
$cuantos_vendedores = 0;
while($data_asig = $stmt_asig->fetch(PDO::FETCH_ASSOC)) {
$usr_id = $data_asig['id'];
$usr_mail = $data_asig['usr_mail'];
$usr_name = $data_asig['usr_name'];
$usr_phone = $data_asig['usr_phone'];
$cuantos_vendedores++;
echo '
<div class="symbol symbol-30 symbol-circle" data-
toggle="tooltip" title="'.$usr_name.' - '.$usr_mail.'">
<img alt="Pic" src="https://storage.googleapis.com/
rt2yno3b84zfdu26tgwh/app/assets/media/users/default.jpg" />
</div>';
}
?>
<div class="symbol-group symbol-hover">
<span class="text-dark-75 font-weight-bolder font-size-sm"><?php
echo $cuantos_vendedores; ?></span>
</div>
</div>
</div>
<!--end: Item-->
</div>
<!--begin: Items-->
</div>
</div>
<!--end::Card-->
<?php
}
?>
</div>
<!--end::Container-->
</div>
<!--end::Entry-->
<!--begin::Entry-->
<div class="d-flex flex-column-fluid">
<!--begin::Container-->
<div class="container-fluid">
<!--begin::Card-->
<div class="card card-custom gutter-b">
<div class="card-body">
<form class="form" id="kt_form_camedit" method="post" action="campaign-
edit.php?campaign_id=<?php echo $id?>&prod_id=<?php echo $producto_id?>">
<div class="tab-content">
<!--begin::Tab-->
<div class="tab-pane show active px-7" id="kt_user_edit_tab_1"
role="tabpanel">
<!--begin::Row-->
<div class="row">
<div class="col-xl-12 my-2">
<!--begin::Row-->
<div class="row">
<label class="col-3"></label>
<div class="col-9">
<h6 class="text-dark font-weight-bold mb-10">Modificacion de la
Campaña:</h6>
</div>
</div>
<!--end::Row-->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-left">Estatus</
label>
<div class="col-3">
<label class="switch">(Apagado/Encendido)
<?php
if ($cam_status == 1) {
echo '<input type="checkbox" name="cam_status_change"
checked="checked">';
}
else {
echo '<input type="checkbox"
name="cam_status_change">';
}
?>
</label>
<span class="slider round"></span>
<div class="text-dark font-weight-bold mb-7">Inactivo campaña
desactivado.</div>
</div>
<div class="col-6">
<div class="form-check form-check-inline">
<input class="form-check-input" type="radio" onclick="hide()"
name="inlineRadioOptions" id="inlineRadio1" value="campaign" <?php if ($cam_role ==
'campaign') echo "checked"; ?>>
<label class="form-check-label" for="inlineRadio1">Asignacion
por Campaña</label>
</div>
<div class="form-check form-check-inline">
<input class="form-check-input" type="radio" onclick="hide()"
name="inlineRadioOptions" id="inlineRadio2" value="product" <?php if ($cam_role ==
'product') echo "checked"; ?>>
<label class="form-check-label" for="inlineRadio2">Asignacion
por Producto</label>
</div>
<?php
//Se agrega query para revisar el id de la marca(brand id)
$query_toCheck_brandId = "SELECT brand_id, IF(brand_id!=0, '!
=0', '0') FROM products where id = ?;";
$parameters_brandId = [];
$parameters_brandId[] = $producto_id;
$stmt_brandId = $db->prepare($query_toCheck_brandId);
$stmt_brandId->execute($parameters_brandId);
while($data_brid =$stmt_brandId->fetch(PDO::FETCH_ASSOC)){
$response_brandId = $data_brid['brand_id'];
}
?>
<div class="form-check form-check-inline <?php if
($response_brandId == 0) echo "d-none"; ?>">
<input class="form-check-input" type="radio" onclick="hide()"
name="inlineRadioOptions" id="inlineRadio3" value="brand" <?php if ($cam_role == 'brand')
echo "checked"; ?>>
<label class="form-check-label" for="inlineRadio3">Asignacion
por Marca</label>
</div>
<div class="form-check form-check-inline <?php if ($cam_role !=
'crm') echo "d-none"; ?>">
<input class="form-check-input" type="radio" onclick="hide()"
name="inlineRadioOptions" id="inlineRadio3" value="crm" <?php if ($cam_role == 'crm') echo
"checked"; ?>>
<label class="form-check-label" for="inlineRadio3">Asignacion
por CRM</label>
</div>
</div>
</div>
<script>
function hide() {
var x = document.getElementById("myDIV");
x.style.display = "none";
}
function show() {
var x = document.getElementById("myDIV");
x.style.display = "block";
}
</script>
<!--begin::Group-->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-left">Hora Inicio</
label>
<div class="col-3">
<select class="form-control form-control-lg form-control-solid"
name="hora_inicio">
<?php
echo "<option value='".$cam_atnstart."'>".$cam_atnstart."</
option>";
for ($i=0;$i<24;$i++){
if ($i<10){ $show = "0".$i.":00"; $show1 = "0".$i.":00"; }
if ($i>=10 && $i<12){ $show = $i.":00"; $show1 = $i.":00"; }
if ($i>=12){ $show = $i.":00"; $show1 = $i.":00"; }
echo "<option value='".$show1."'>".$show."</option>";
label>
name="hora_fin">
option>";
}
?>
</select>
</div>
<label class="col-form-label col-3 text-lg-right text-left">Hora Fin</
<div class="col-3">
<select class="form-control form-control-lg form-control-solid"
<?php
echo "<option value='".$cam_atnend."'>".$cam_atnend."</
for ($i=0;$i<24;$i++){
if ($i<10){ $show3 = "0".$i.":00"; $show2 = "0".$i.":00";}
if ($i>=10 && $i<12){ $show3 = $i.":00"; $show2 = $i.":00";}
if ($i>=12){ $show3 = $i.":00"; $show2 = $i.":00"; }
echo "<option value='".$show2."'>".$show3."</option>";
}
?>
</select>
</div>
</div>
<!--end::Group-->
<!--begin::Group-->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-left">Dias de
Atencion</label>
<div class="col-8">
<div class="form-check">
<?php
$lunes = array_search('1', $cam_atndays);
if ($lunes !== false) {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_lunes" checked="checked">
<?php
} else {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_lunes">
<?php
}?>
<label class="form-check-label mt-1 mr-7"
for="atencion_lunes">Lun</label>
<?php
$martes = array_search('2', $cam_atndays);
if ($martes !== false) {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_martes" checked="checked">
<?php
} else {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_martes">
<?php
}?>
<label class="form-check-label mr-7"
for="atencion_martes">Mar</label>
<?php
$miercoles = array_search('3', $cam_atndays);
if ($miercoles !== false) {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_miercoles" checked="checked">
<?php
} else {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_miercoles">
<?php
}?>
<label class="form-check-label mr-7"
for="atencion_miercoles">Mie</label>
<?php
$jueves = array_search('4', $cam_atndays);
if ($jueves !== false) {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_jueves" checked="checked">
<?php
} else {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_jueves">
<?php
}?>
<label class="form-check-label mr-7"
for="atencion_jueves">Jue</label>
<?php
$viernes = array_search('5', $cam_atndays);
if ($viernes !== false) {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_viernes" checked="checked">
<?php
} else {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_viernes">
<?php
for="atencion_viernes">Vie</label>
}?>
<?php
<label class="form-check-label mr-7"
$sabado = array_search('6', $cam_atndays);
if ($sabado !== false) {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_sabado" checked="checked">
<?php
} else {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_sabado">
<?php
}?>
<label class="form-check-label mr-7"
for="atencion_sabado">Sab</label>
<?php
$domingo = array_search('7', $cam_atndays);
if ($domingo !== false) {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_domingo" checked="checked">
<?php
} else {
?>
<input type="checkbox" class="form-check-input mt-2"
name="atencion_domingo">
<?php
}?>
for="atencion_domingo">Dom</label>
</div>
</div>
<label class="form-check-label"
<label class="col-form-label col-3 text-lg-right text-left">Cambio de
turno</label>
<div class="col-3">
<select class="form-control form-control-lg form-control-solid"
name="cam_shift">
<?php
echo "<option value='".$cam_shift."'>".$cam_shift."</
option>";
for ($i=0;$i<24;$i++){
if ($i<10){ $show4 = "0".$i.":00"; $show3 = "0".$i.":00";}
if ($i>=10 && $i<12){ $show4 = $i.":00"; $show3 = $i.":00";}
if ($i>=12){ $show4 = $i.":00"; $show3 = $i.":00"; }
echo "<option value='".$show3."'>".$show4."</option>";
}
?>
</select>
</div>
</div>
<input type="hidden" name="cam_type" id="camType" value="<?php
echo $cam_type; ?>">
<?php if($cam_type == 'IA' && $cam_bu == 'IA' &&
$_SESSION['ai_exist'] == 1){ ?>
<hr>
<div class="row mb-5">
<label class="col-3"></label>
<div class="col-9">
<h6>AI Setup<i class="fa fa-question-circle mt-1 ml-2 text-info"
data-toggle="tooltip" data-placement="top" title="Aqui puedes configurar si necesitas que el
bot IA asignado a esta campaña realice asignación automática de vendedores y elegir el
tiempo para hacerlo"></i></h6>
<p class="text-muted">Selecciona si necesitas asignación
automática de vendedor en tu Bot IA</p>
</div>
</div>
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-
left">Asignación automática de vendedor</label>
<div class="col-3">
<label class="switch">
<?php
if($autoassign_lead_ia == 1){
echo '<input type="checkbox" id="proAiSellerAssign"
name="autoassign_lead_ia_edit" checked="checked">';
}else{
echo '<input type="checkbox" id="proAiSellerAssign"
name="autoassign_lead_ia_edit">';
}
?>
</label>
</div>
</div>
<span class="slider round"></span>
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-
left">Asignación automática</label>
<div class="col-3">
<select class="form-control form-control-lg form-control-solid"
id="proAiTimeAssign" name="autoassign_time_ia_edit" <?= ($autoassign_lead_ia == 0 ?
'disabled' : '') ?>>
<option value="">Tiempo para la asignación automática</
option>
<option value="2" <?= ($autoassign_time_ia == 2 ?
'selected' : '') ?>>2 horas</option>
<option value="4" <?= ($autoassign_time_ia == 4 ?
'selected' : '') ?>>4 horas</option>
<option value="6" <?= ($autoassign_time_ia == 6 ?
'selected' : '') ?>>6 horas</option>
<option value="8" <?= ($autoassign_time_ia == 8 ? 'selected'
: '') ?>>8 horas</option>
<option value="10" <?= ($autoassign_time_ia == 10 ?
'selected' : '') ?>>10 horas</option>
<option value="12" <?= ($autoassign_time_ia == 12 ?
'selected' : '') ?>>12 horas</option>
<option value="14" <?= ($autoassign_time_ia == 14 ?
'selected' : '') ?>>14 horas</option>
<option value="16" <?= ($autoassign_time_ia == 16 ?
'selected' : '') ?>>16 horas</option>
<option value="18" <?= ($autoassign_time_ia == 18 ?
'selected' : '') ?>>18 horas</option>
<option value="20" <?= ($autoassign_time_ia == 20 ?
'selected' : '') ?>>20 horas</option>
<option value="22" <?= ($autoassign_time_ia == 22 ?
'selected' : '') ?>>22 horas</option>
<option value="24" <?= ($autoassign_time_ia == 24 ?
'selected' : '') ?>>24 horas</option>
</select>
</div>
</div>
<div class="form-group row" style="pointer-events: none;">
<label class="col-form-label col-3 text-lg-right text-left">Bot IA</
label>
<div class="col-3">
<select class="form-control form-control-lg form-control-solid"
name="bot_ia_sendpulse">
<?php
$sql_ai_agent = "SELECT bot_id, bot_phone, bot_name
FROM bots_sendpulse
WHERE cli_id = :cliId AND active_bot
= :activeBot";
$params_ai_agent = [
'cliId' => $_SESSION['clientid'],
'activeBot' => 1
'selected' : '';
];
$stmt_ai_agent = $db->prepare($sql_ai_agent);
$stmt_ai_agent->execute($params_ai_agent);
$bots = $stmt_ai_agent->fetchAll(PDO::FETCH_ASSOC);
foreach($bots as $bot){
$selected = ($bot_id_sendpulse == $bot['bot_id']) ?
echo '<option value="'.$bot['bot_id'].'" '.$selected.'>'.
$bot['bot_name'].' - '.$bot['bot_phone'].
'</option>';
}
?>
</select>
</div>
</div>
<hr>
<?php } ?>
<!-- Begin: Campo para seleccionar la configuracion de CRM por cada
cliente -->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-
left">Configuración CRM</label>
<div class="col-3">
<select class="form-control form-control-solid form-control-lg
producto" id="crm-con" name="crm-con">
<option value="">- Selecciona la configuración CRM -</option>
<?php
//Query para obtener todos los CRM config disponibles para el
cliente actual, menos el que ya tiene guardado
$query_crm = "SELECT id, cli_id, api_description FROM
api_setups WHERE cli_id = '$cli_id' AND id != '$crm'";
$stmt_crm = $db->prepare($query_crm);
$stmt_crm->execute();
while($data_crm = $stmt_crm->fetch(PDO::FETCH_ASSOC)){
$id_crm = $data_crm['id'];
$cli_idcrm = $data_crm['cli_id'];
$api_description = $data_crm['api_description'];
echo "<option value='".$id_crm."'>".$api_description."</
option>";
}
//Query para obtener la descripcion del CRM config usando el id
que tiene guardado
$query_crmname = "SELECT id, api_description FROM
api_setups WHERE id = '$crm'";
$stmt_crmname = $db->prepare($query_crmname);
$stmt_crmname->execute();
while($data_crmname = $stmt_crmname-
>fetch(PDO::FETCH_ASSOC)){
$id2 = $data_crmname['id'];
$api_description2 = $data_crmname['api_description'];
echo "<option value='".$crm."' selected>".
$api_description2."</option>";
}
?>
</select>
</div>
</div>
<!-- End: Campo apra seleccionar la configuracion de CRM por cada
cliente -->
CRM ID</label>
<!-- Begin: Campo para editar el channel CRM ID -->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-alert">Channel
<div class="col-3">
<?php if($channel_crmid==''||$channel_crmid==NULL){ ?>
<input type="text" class="form-control form-control-solid form-
control-lg" id="crmid-channel-change" name="crmid-channel-edit" value=""
placeholder="Ingresa el channel CRM ID" />
<?php }else{ ?>
<input type="text" class="form-control form-control-solid form-
control-lg" id="crmid-channel-change" name="crmid-channel-edit" value="<?php echo
$channel_crmid; ?>" />
<?php } ?>
</div>
</div>
<!-- End: Campo para editar el channel CRM ID -->
<!--
----------------------------------------------------------------------------------------- -->
<!-- Begin:: Campo para editar el nombre de la locacion de la
campaña -->
<div class="form-group row" <?php echo $available_location_fields
== 'hidden' ? 'style="display:none"' : ''; ?>>
<label class="col-form-label col-3 text-lg-right text-alert">Nombre
de ubicación</label>
<div class="col-3">
<input type="<?php echo $available_location_fields ; ?>"
class="form-control form-control-solid form-control-lg" id="name-location-change"
name="name-location-edit" value="<?php echo $name_position_campaign; ?>" />
</div>
</div>
<!-- End:: Campo para editar el nombre de la locacion de la campaña
-->
<!-- Begin: Campo para editar la direccion de la locacion de la
campaña -->
<div class="form-group row" <?php echo $available_location_fields
== 'hidden' ? 'style="display:none"' : ''; ?>>
<label class="col-form-label col-3 text-lg-right text-alert">Dirección
de ubicación</label>
<div class="col-3">
<input type="<?php echo $available_location_fields ; ?>"
class="form-control form-control-solid form-control-lg" id="address-location-change"
name="address-location-edit" value="<?php echo $address_position_campaign; ?>" />
</div>
</div>
<!-- End: Campo para editar la direccion de la locacion de la campaña
-->
<!-- Begin: Campo para editar la latitud de la locacion de la campaña
-->
<div class="form-group row" <?php echo $available_location_fields
== 'hidden' ? 'style="display:none"' : ''; ?>>
<label class="col-form-label col-3 text-lg-right text-alert">Latitud
de ubicación</label>
<div class="col-3">
<input type="<?php echo $available_location_fields ; ?>"
class="form-control form-control-solid form-control-lg" id="latitud-location-change"
name="latitud-location-edit" value="<?php echo $latitud_position_campaign; ?>" />
</div>
</div>
<!-- End: Campo para editar la latitud de la locacion de la campaña --
>
<!-- Begin: Campo para editar la longitud de la locacion de la
campaña -->
<div class="form-group row" <?php echo $available_location_fields
== 'hidden' ? 'style="display:none"' : ''; ?>>
<label class="col-form-label col-3 text-lg-right text-alert">Longitud
de ubicación</label>
<div class="col-3">
<input type="<?php echo $available_location_fields ; ?>"
class="form-control form-control-solid form-control-lg" id="longitud-location-change"
name="longitud-location-edit" value="<?php echo $longitud_position_campaign; ?>" />
</div>
</div>
<!-- End: Campo para editar la longitud de la locacion de la campaña
-->
<!--
----------------------------------------------------------------------------------------- -->
<!-- Begin: Campo para mostrar los mail template disponibles para el
cliente en sesion -->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-alert">Mail
Template</label>
<div class="col-3">
<select class="form-control form-control-solid form-control-lg
producto" id="mail_template" name="mail_template">
<option value="<?php echo $cam_mail_template; ?>">-
Selecciona mail template -</option>
<?php
if($cam_mail_template!='user_lead_mail'){
echo"<option value='".$cam_mail_template."' selected>".
$cam_mail_template."</option>";
}
$query_mailTemp = "SELECT c.mail_template as mail_template,
c.pro_id, p.id, p.cli_id FROM campaigns AS c, products AS p WHERE c.pro_id = p.id AND
c.mail_template != 'user_lead_mail' AND c.mail_template != '$cam_mail_template' AND p.cli_id
= '$cli_id' GROUP BY c.mail_template";
$stmt_mailTemp = $db->prepare($query_mailTemp);
$stmt_mailTemp->execute();
while($data_mailTemp = $stmt_mailTemp-
>fetch(PDO::FETCH_ASSOC)){
$mail_temp = $data_mailTemp['mail_template'];
echo"<option value='".$mail_temp."'>".$mail_temp."</
option>";
}
?>
</select>
</div>
</div>
<!-- Begin: Campo para mostrar los mail template disponibles para el
cliente en sesion -->
<!-- Begin: Campo para seleccionar el Wab Bot disponible para cada
cliente -->
<div class="form-group row" id="divWabBot" <?php if ($cam_type ==
'IA') echo 'style="display:none;"'; ?>>
<label class="col-form-label col-3 text-lg-right text-alert">Bot</label>
<div class="col-3">
<select class="form-control form-control-solid form-control-lg
producto" id="wab_bot" name="wab_bot">
<option value="<?php echo $wab_bot; ?>">- Selecciona Bot -</
option>
datos -->
$wab_bot ?></option>
<!-- Opcion con el bot guardado para esta campaña en la base de
<option value="<?php echo $wab_bot; ?>" selected><?php echo
<?php
//Validacion para saber si mostramos opcion del Bot general de
dragon o no (depende del bot guardado en la base)
if ($wab_bot == 'cli_id01-General' || $wab_bot ==
'botGralDragonCEM'){
}else{
echo "<option
value='botGralDragonCEM'>botGralDragonCEM</option>";
}
$array_wab_bot1 = array();
//Obtenemos los bots para el cliente en sesion a exepcion del que
ya esta guardado, jalamos data de las relaciones existentes en campañas y clientes
// $query_wabbot = "SELECT DISTINCT (campaigns.wab_bot) as
wab_bot FROM campaigns JOIN products ON campaigns.pro_id=products.id WHERE
products.cli_id='$cli_id' AND campaigns.cam_status='1' AND campaigns.wab_bot !=
'$wab_bot'";
$query_wabbot_1 = "SELECT DISTINCT (campaigns.wab_bot) as
wab_bot FROM campaigns JOIN products ON campaigns.pro_id=products.id WHERE
products.cli_id='$cli_id' AND campaigns.cam_status='1' AND campaigns.wab_bot !=
'$wab_bot' AND campaigns.wab_bot != 'cli_id01-General' AND campaigns.wab_bot !=
'botGralDragonCEM'";
$stmt_wabbot_1 = $db->prepare($query_wabbot_1);
$stmt_wabbot_1->execute();
while($data_wabbot_1 = $stmt_wabbot_1-
>fetch(PDO::FETCH_ASSOC)){
$wab_bot_1 = $data_wabbot_1['wab_bot'];
echo "<option value='".$wab_bot_1."'>".$wab_bot_1."</
option>";
array_push($array_wab_bot1, $wab_bot_1);
}
//Obtenemos
los bots relacionados al producto en curso, jalamos data de la tabla wab_bots
$query_wabbot = "SELECT DISTINCT (wab_bots.bot_name) as
nombre_bot FROM wab_bots WHERE pro_id = ? AND wab_bots.bot_name != ? AND
bot_status = 1 AND wab_bots.bot_name != 'cli_id01-General'";
$param_bot = [];
$param_bot[] = $producto_id;
$param_bot[] = $wab_bot;
$stmt_wabbot = $db->prepare($query_wabbot);
$stmt_wabbot->execute($param_bot);
while($data_wabbot = $stmt_wabbot-
>fetch(PDO::FETCH_ASSOC)){
$wab_bot_b = $data_wabbot['nombre_bot'];
$index = array_search($wab_bot_b, $array_wab_bot1);
if($index == ''){
echo "<option value='".$wab_bot_b."'>".$wab_bot_b."</
option>";
}
}
?>
</select>
</div>
</div>
<!-- End: Campo para seleccionar el Wab Bot disponible para cada
cliente -->
<!-- Begin: Campo para mostrar dropdown con el bundle de
calificaciones -->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-
left">Configuración de calificaciones</label>&nbsp;&nbsp;&nbsp;&nbsp;
<select class="form-control col-4 form-control-lg form-control-solid"
name="grade_bundle" id="grade_bundle">
<!-- <option value="">- Selecciona la configuración de
calificaciones -</option> -->
<?php
$grade_default = '{"1": "No Localizable", "2": "No Interesado",
"3": "Interés bajo", "4": "Interés Medio", "5": "Alto Interés"}';
echo "<option value=''>- Selecciona la configuración de
calificaciones -</option>";
echo "<option value='".$grade_default."'>Default 5
ponderadores</option>";
$query_grade_options = "SELECT id, grade_options FROM
campaigns WHERE pro_id='$producto_id' GROUP BY grade_options";
$stmt_gopt = $db->prepare($query_grade_options);
$stmt_gopt->execute();
while($data_gopt = $stmt_gopt->fetch(PDO::FETCH_ASSOC)){
$camp_id_gop = $data_gopt['id'];
json_decode($data_gopt['grade_options'], true);
if($data_gopt['grade_options'] != $grade_options){
$gradeopt_grop =
$count = count($gradeopt_grop);
$nombre_gradeopt = "Configuración de ".$count."
ponderadores";
echo "<option value='".$data_gopt['grade_options']."'>".
$nombre_gradeopt."</option>";
}else{
}
}
$current_grop = json_decode($grade_options, true);
$count_current = count($current_grop);
$nombre_current_grop = "Configuración de ".$count_current."
ponderadores";
//Mostramos seleccionada la opcion que esta guardada
actualmente
echo "<option value='".$grade_options."' selected>".
$nombre_current_grop."</option>";
?>
</select>
</div>
<!-- End: Campo para mostrar dropdown con el bundle de calificaciones
-->
<!-- Begin: Campo para mostrar dropdown con el bundle de
calificaciones -->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-
left">Ponderadores</label>&nbsp;&nbsp;&nbsp;&nbsp;
<textarea class="form-control col-4 form-control-lg form-control-solid
text-muted" rows="4" readonly name="gradeOptionDetail" id="gradeOptionDetail"></
textarea>
</div>
<!-- End: Campo para mostrar dropdown con el bundle de calificaciones
-->
label>
<!-- Begin: Validacion para mostrar campo con el CRM ID-->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-left">CRM ID</
<div class="col-3">
<?php if($crm_id==""||$crm_id==NULL){ ?>
<input style="width:350px;" class="form-control form-control-lg"
type="text" name="crm_id" placeholder="Ingresa el CRM ID (Opcional)">
<?php }else{ ?>
<input style="width:350px;" class="form-control form-control-lg"
type="text" name="crm_id" value="<?php echo $crm_id; ?>">
<?php } ?>
</div>
</div>
<!--End: Validacion para mostrar campo con el CRM ID -->
<!--Begin: Validacion para mostrar switch Quality-->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-left">Quality</
label>&nbsp;&nbsp;&nbsp;&nbsp;
<label class="switch">(Apagada/Encendida)
<?php if(($cam_quali == 1)){ ?>
<input type="checkbox" name="cam_quality_change"
checked="checked">
<?php }else{?>
<input type="checkbox" name="cam_quality_change">
<?php } ?>
<span class="slider round"></span>
</label>
</div>
<!--End: Validacion para mostrar switch Quality-->
<!-- Begin: Campo para mostrar dropdown con el bundle msg -->
<div class="form-group row" id="divBot" <?php if ($cam_type == 'IA')
echo 'style="display:none;"'; ?>>
<label class="col-form-label col-3 text-lg-right text-
left">Configuración de mensajes WhatsApp</label>&nbsp;&nbsp;&nbsp;&nbsp;
<select class="form-control col-4 form-control-lg form-control-solid"
name="wab_msg" id="wab_msg" onchange="msg()">
<option value="">- Selecciona la configuración de mensajes de
WhatsApp -</option>
<?php
// Definir y configurar el logger
// $logger = new Logger('twilio_logger');
// $logger->pushHandler(new StreamHandler(__DIR__ . '/twilio.log',
Logger::ERROR));
// Obtenemos el nombre del bundle de WAB message guardado y
lo seleccionamos de manera predeterminada
$query_qwm = "SELECT id, wab_bundle, wab_welcome,
wab_quality FROM wab_messages WHERE id= :wab_messages";
$stmt_qwm = $db->prepare($query_qwm);
$stmt_qwm->execute([':wab_messages' => $wab_messages]);
$data_qwm = $stmt_qwm->fetch(PDO::FETCH_ASSOC);
$query_products = "SELECT twl_accsid, twl_accat FROM products
WHERE id = :product_id";
// Preparar la consulta
$stmt_products = $db->prepare($query_products);
// Ejecutar la consulta con el parámetro adecuado
$stmt_products->execute([':product_id' => $producto_id]);
// Obtener los resultados
$data_products = $stmt_products->fetch(PDO::FETCH_ASSOC);
if ($data_products) {
// Asignar los valores obtenidos a variables PHP
$twl_sellwab_acid = $data_products['twl_accsid'];
$twl_sellwab_at = $data_products['twl_accat'];
}
function processTwilioContent($client, $templateSid) {
try {
>fetch();
$content = $client->content->v1->contents($templateSid)-
$bodyContent = 'No existe mensaje';
if (isset($content->types) && is_array($content->types)) {
foreach ($content->types as $type => $typeContent) {
if (isset($typeContent['body'])) {
$bodyContent = $typeContent['body'];
break;
}
}
}
return [
'gotit' => true,
'body' => $bodyContent
];
} catch (TwilioException $e) {
return [
'gotit' => false,
'content' => 'inexistent'
];
}
}
if ($data_qwm) {
$wab_bundle = $data_qwm['wab_bundle'];
$id_bundle = $data_qwm['id'];
$wab_welcome = isset($data_qwm['wab_welcome']) ?
$data_qwm['wab_welcome'] : null;
$wab_quality = isset($data_qwm['wab_quality']) ?
$data_qwm['wab_quality'] : null;
// Muestra las opciones
echo "<option value='" . $id_bundle . "' selected>" .
$wab_bundle . "</option>";
// Configura los datos del cliente
$clientSk = $twl_sellwab_at;
$clientId = $twl_sellwab_acid;
// echo "<script>console.log('clientSk: " . $clientSk . "');</
script>";
// echo "<script>console.log('clientId: " . $clientId . "');</script>";
$client = new Client($clientId, $clientSk);
if ($wab_welcome) {
$welcomeResponse = processTwilioContent($client,
$wab_welcome);
$welcomeBodyContent = $welcomeResponse['gotit'] ?
$welcomeResponse['body'] : 'No existe mensaje';
// echo "<script>console.log('wab_welcome body: " .
addslashes($welcomeBodyContent) . "');</script>";
}
if ($wab_quality) {
$qualityResponse = processTwilioContent($client,
$wab_quality);
$qualityBodyContent = $qualityResponse['gotit'] ?
$qualityResponse['body'] : 'No existe mensaje';
// echo "<script>console.log('wab_quality body: " .
addslashes($qualityBodyContent) . "');</script>";
}
}
$query_wab_status = "SELECT wab_status, twl_wab FROM
products WHERE id = :producto_id";
$stmt_wab_status = $db->prepare($query_wab_status);
$stmt_wab_status->execute([':producto_id' => $producto_id]);
while ($data_wab_status = $stmt_wab_status-
>fetch(PDO::FETCH_ASSOC)) {
$status_wab_prod = $data_wab_status['wab_status'];
$status_twl_wab = $data_wab_status['twl_wab'];
if ($status_wab_prod == '1') {
// Obtenemos bundle de wab messages del cliente en sesion
$query_wab_msg = "SELECT id, wab_bundle FROM
wab_messages WHERE wab_number = :twl_wab AND id != :wab_messages AND
bundle_status = 1";
$stmt_wab_msg = $db->prepare($query_wab_msg);
$stmt_wab_msg->execute([
':twl_wab' => $status_twl_wab,
':wab_messages' => $wab_messages
]);
>fetch(PDO::FETCH_ASSOC)) {
while ($data_qwm = $stmt_wab_msg-
$wab_bundle = $data_qwm['wab_bundle'];
$id_bundle = $data_qwm['id'];
echo "<option value='" . $id_bundle . "'>" . $wab_bundle .
"</option>";
}
} elseif ($status_wab_prod == '0' && $_SESSION['clientid'] == '1')
{
// Obtenemos bundle de wab messages solo del cliente
Dragon
$query_wab_msg = "SELECT id, wab_bundle FROM
wab_messages WHERE cli_id = 1 AND id != :wab_messages AND bundle_status = 1";
$stmt_wab_msg = $db->prepare($query_wab_msg);
$stmt_wab_msg->execute([':wab_messages' =>
$wab_messages]);
>fetch(PDO::FETCH_ASSOC)) {
while ($data_qwm = $stmt_wab_msg-
$wab_bundle = $data_qwm['wab_bundle'];
$id_bundle = $data_qwm['id'];
echo "<option value='" . $id_bundle . "'>" . $wab_bundle .
"</option>";
}
} elseif ($status_wab_prod == '0' && $_SESSION['clientid'] != '1')
{
$query_wab_msg = "SELECT id, wab_bundle FROM
wab_messages WHERE wab_number = :twl_wab AND bundle_status = 1 AND cli_id = :clientid
AND id != :wab_messages AND id != 43";
$stmt_wab_msg = $db->prepare($query_wab_msg);
$stmt_wab_msg->execute([
':twl_wab' => $_SESSION['twl_wab'],
':clientid' => $_SESSION['clientid'],
':wab_messages' => $wab_messages
]);
>fetch(PDO::FETCH_ASSOC)) {
while ($data_qwm = $stmt_wab_msg-
$wab_bundle = $data_qwm['wab_bundle'];
$id_bundle = $data_qwm['id'];
echo "<option value='" . $id_bundle . "'>" . $wab_bundle .
"</option>";
actualmente -->
}
}
}
?>
<!-- Mostramos seleccionada la opcion que esta guardada
</select>
</div>
<!-- End: Campo para mostrar dropdown con el bundle msg -->
<!-- Begin: Campo para mostrar dropdown con el bundle msg -->
<div class="form-group row" id="wabWelcome">
<label class="col-form-label col-3 text-lg-right text-left">Mensaje
principal</label>&nbsp;&nbsp;&nbsp;&nbsp;
<textarea class="form-control col-4 form-control-lg form-control-solid
text-muted" rows="4" readonly><?php echo $welcomeBodyContent; ?></textarea>
</div>
<!-- End: Campo para mostrar dropdown con el bundle msg -->
<!-- Begin: Campo para mostrar dropdown con el bundle msg -->
<div class="form-group row" id="wabQuality">
<label class="col-form-label col-3 text-lg-right text-left">Mensaje
para quality</label>&nbsp;&nbsp;&nbsp;&nbsp;
<textarea class="form-control col-4 form-control-lg form-control-solid
text-muted" rows="4" readonly><?php echo $qualityBodyContent; ?></textarea>
</div>
<!-- End: Campo para mostrar dropdown con el bundle msg -->
left">Notificaciones</label>
<!--end::Group-->
<!--begin::Group-->
<div class="form-group row">
<label class="col-form-label col-3 col-sm-3 text-lg-right text-
<div class="col-2 col-sm-1">
<label>Mail Cliente</label>
</div>
<div class="col-2 col-sm-1">
<label class="switch">
<?php
$key1 = array_search('1', $cam_notify);
if ($key1 !== false) {
echo '<input type="checkbox" name="mailclient_notify"
checked="checked">';
}
else {
echo '<input type="checkbox" name="mailclient_notify">';
}
?>
<span class="slider round"></span>
</label>
</div>
<div class="col-2 col-sm-1">
<label>Mail Lead</label>
</div>
<div class="col-3 col-sm-1">
<label class="switch">
<?php
$key2 = array_search('2', $cam_notify);
if ($key2 !== false) {
echo '<input type="checkbox" name="maillead_notify"
checked="checked">';
}
else {
echo '<input type="checkbox" name="maillead_notify">';
}
?>
</label>
</div>
<span class="slider round"></span>
<span class="col-sm-0 col-xs-3"></span>
<div class="col-2 col-sm-1">
<label>Whats Cliente</label>
</div>
<div class="col-2 col-sm-1">
<label class="switch">
<?php
$key3 = array_search('3', $cam_notify);
if ($key3 !== false) {
echo '<input type="checkbox" name="whatsclient_notify"
checked="checked">';
}
else {
echo '<input type="checkbox" name="whatsclient_notify">';
}
?>
<span class="slider round"></span>
</label>
</div>
<div class="col-2 col-sm-1">
<label>Whats Lead</label>
</div>
<div class="col-3 col-sm-1">
<label class="switch">
<?php
$key4 = array_search('4', $cam_notify);
if ($key4 !== false) {
echo '<input type="checkbox" name="whatsleads_notify"
checked="checked">';
label>
}
else {
echo '<input type="checkbox" name="whatsleads_notify">';
}
?>
<span class="slider round"></span>
</label>
</div>
</div>
<!--end::Group-->
<!--begin::Group-->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-left">Llamada</
<div class="form-check">
<?php
$key5 = array_search('5', $cam_notify);
$key6 = array_search('6', $cam_notify);
if ($key5 !== false) {
?>
<input class="form-check-input" type="radio"
name="exampleRadios" id="exampleRadios1" value="5" checked>
<label class="form-check-label mr-5"
for="exampleRadios1">Mayor Calidad ("Presione 1 para llamada")</label>
<br>
<input class="form-check-input" type="radio"
name="exampleRadios" id="exampleRadios2" value="6">
<label class="form-check-label" for="exampleRadios2">Mayor
Volumen (Llamada enlazada automaticamente)</label>
<br>
<input class="form-check-input" type="radio"
name="exampleRadios" id="exampleRadios3" value="0">
<label class="form-check-label" for="exampleRadios3">Sin
Llamada</label>
<?php
}
else if ($key6 !== false) {
?>
<input class="form-check-input" type="radio"
name="exampleRadios" id="exampleRadios1" value="5">
<label class="form-check-label mr-5"
for="exampleRadios1">Mayor Calidad ("Presione 1 para llamada")</label>
<br>
<input class="form-check-input" type="radio"
name="exampleRadios" id="exampleRadios2" value="6" checked>
<label class="form-check-label" for="exampleRadios2">Mayor
Volumen (Llamada enlazada automaticamente)</label>
<br>
<input class="form-check-input" type="radio"
name="exampleRadios" id="exampleRadios3" value="0">
<label class="form-check-label" for="exampleRadios3">Sin
Llamada</label>
<?php
}
else if ($key5 !== true && $key6 !== true) {
?>
<input class="form-check-input" type="radio"
name="exampleRadios" id="exampleRadios1" value="5">
<label class="form-check-label mr-5"
for="exampleRadios1">Mayor Calidad ("Presione 1 para llamada")</label>
<br>
<input class="form-check-input" type="radio"
name="exampleRadios" id="exampleRadios2" value="6">
<label class="form-check-label" for="exampleRadios2">Mayor
Volumen (Llamada enlazada automaticamente)</label>
<br>
<input class="form-check-input" type="radio"
name="exampleRadios" id="exampleRadios3" value="0" checked>
<label class="form-check-label" for="exampleRadios3">Sin
Llamada</label>
<?php
}
?>
</div>
</div>
<!--end::Group-->
<?php
if($cam_bu=='Online'||$cam_bu=='WhatsApp'){
?>
<!--begin::Group-->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-left">Creacion de
Leads Manuales</label>
<div class="form-check">
<?php
if ($cam_manual == 1) {
?>
<input class="form-check-input" type="radio"
name="cam_manual" id="cam_manual1" value="1" checked>
<label class="form-check-label mr-5"
for="cam_manual1">Permitir</label>
<br>
<input class="form-check-input" type="radio"
name="cam_manual" id="cam_manual2" value="0">
<label class="form-check-label" for="cam_manual2">No
Permitir</label>
<?php
}
else {
?>
<input class="form-check-input" type="radio"
name="cam_manual" id="cam_manual1" value="1">
<label class="form-check-label mr-5"
for="cam_manual1">Permitir</label>
<br>
<input class="form-check-input" type="radio"
name="cam_manual" id="cam_manual2" value="0" checked>
<label class="form-check-label" for="cam_manual2">No
Permitir</label>
<?php
}
?>
</div>
</div>
<!--end::Group-->
<?php
}
?>
<!--begin::Group-->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-left">Manejo de
duplicados a nivel</label>
<div class="form-check">
<?php
if ($cam_duplicates == 1) {
?>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates1" value="1" checked>
<label class="form-check-label mr-5"
for="cam_duplicates1">Campaña</label>
<br>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates2" value="2">
<label class="form-check-label"
for="cam_duplicates2">Producto</label>
<br class= "<?php if ($response_brandId == 0) echo "d-none"; ?
>">
<input class="form-check-input <?php if ($response_brandId ==
0) echo "d-none"; ?>" type="radio" name="cam_duplicates" id="cam_duplicates3" value="3">
<label class="form-check-label <?php if ($response_brandId ==
0) echo "d-none"; ?>" for="cam_duplicates3">Marca</label>
<br>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates4" value="4">
<label class="form-check-label"
for="cam_duplicates4">Cliente</label>
<?php
}
else if ($cam_duplicates == 2) {
?>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates1" value="1">
<label class="form-check-label mr-5"
for="cam_duplicates1">Campaña</label>
<br>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates2" value="2" checked>
<label class="form-check-label"
for="cam_duplicates2">Producto</label>
<br class= "<?php if ($response_brandId == 0) echo "d-none"; ?
>">
<input class="form-check-input <?php if ($response_brandId ==
0) echo "d-none"; ?>" type="radio" name="cam_duplicates" id="cam_duplicates3" value="3">
<label class="form-check-label <?php if ($response_brandId ==
0) echo "d-none"; ?>" for="cam_duplicates3">Marca</label>
<br>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates4" value="4">
<label class="form-check-label"
for="cam_duplicates4">Cliente</label>
<?php
}
else if ($cam_duplicates == 3) {
?>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates1" value="1">
<label class="form-check-label mr-5"
for="cam_duplicates1">Campaña</label>
<br>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates2" value="2">
<label class="form-check-label"
for="cam_duplicates2">Producto</label>
<br class= "<?php if ($response_brandId == 0) echo "d-none"; ?
>">
<input class="form-check-input <?php if ($response_brandId ==
0) echo "d-none"; ?>" type="radio" name="cam_duplicates" id="cam_duplicates3" value="3"
checked>
<label class="form-check-label <?php if ($response_brandId ==
0) echo "d-none"; ?>" for="cam_duplicates3">Marca</label>
<br>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates4" value="4">
<label class="form-check-label"
for="cam_duplicates4">Cliente</label>
<?php
}
else {
?>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates1" value="1">
<label class="form-check-label mr-5"
for="cam_duplicates1">Campaña</label>
<br class= "<?php if ($response_brandId == 0) echo "d-none"; ?
>">
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates2" value="2">
<label class="form-check-label"
for="cam_duplicates2">Producto</label>
<br class= "<?php if ($response_brandId == 0) echo "d-none"; ?
>">
<input class="form-check-input <?php if ($response_brandId ==
0) echo "d-none"; ?>" type="radio" name="cam_duplicates" id="cam_duplicates3" value="3">
<label class="form-check-label <?php if ($response_brandId ==
0) echo "d-none"; ?>" for="cam_duplicates3">Marca</label>
<br>
<input class="form-check-input" type="radio"
name="cam_duplicates" id="cam_duplicates4" value="4" checked>
<label class="form-check-label"
for="cam_duplicates4">Cliente</label>
<?php
}
?>
</div>
</div>
<!--end::Group-->
<!--begin::Group-->
<div class="form-group row">
<label class="col-form-label col-3 text-lg-right text-left">Días para
detectar duplicados</label>
<div class="col-auto">
<input class="form-control form-control-lg" type="text"
id="input_dupdays" name="cam_dupdays" value="<?php echo $cam_dupdays; ?>">
</div>
<div class="mt-3 form-check">
<?php if($cam_dupdays == 0) {
echo '<input type="checkbox" class="form-check-input mt-2"
onchange="comprobar();" value='.$cam_dupdays.' id="repeatCheq"
name="deteccion_repeat_leads" checked>';
} else {
echo '<input type="checkbox" class="form-check-input mt-2"
onchange="comprobar();" value='.$cam_dupdays.' id="repeatCheq"
name="deteccion_repeat_leads">';
}
?>
</div>
<label class="col-form-label text-lg-right text-left">Detectar Leads
duplicados sin importar la fecha.<i class="fa fa-question-circle mt-1 ml-1" aria-hidden="true"
data-placement="top" title="Al marcar el checkbox la detección de duplicados se aplicará
desde la fecha que ingresó el primer lead."></i></label>
<script type="text/javascript">
if (document.getElementById("repeatCheq").checked){
console.log('sí cheq');
document.getElementById('input_dupdays').readOnly = true;
}
function comprobar(){
var dupdays = document.getElementById('input_dupdays');
var dupdaysValue =
document.getElementById('input_dupdays').value;
var oldVal = <?php echo $cam_dupdays; ?>;
console.log("Valor: "+oldVal);
//varTest.setAttribute('value',0);
console.log('dupdaysValue: ',dupdaysValue);
if (document.getElementById('repeatCheq').checked){
dupdays.setAttribute('value',0);
document.getElementById('input_dupdays').readOnly =
true;
false;
}else{
document.getElementById('input_dupdays').readOnly =
dupdays.setAttribute('value', oldVal);
por Campaña:</h6>
label>
}
}
</script>
</div>
<div class="row mb-5">
<label class="col-3"></label>
<div class="col-9">
<h6 class="text-dark font-weight-bold mb-10">Modificacion iScore
</div>
<label class="col-form-label col-3 text-lg-right text-left">iScore</
<div class="col-auto">
<input class="form-control form-control-lg" type="text" id="iscore"
name="iscore" value="<?php echo $iscore; ?>">
</div>
<label class="col-form-label text-lg-right text-left">Puntuación
máxima 20<i class="fa fa-question-circle mt-1 ml-1" aria-hidden="true" data-placement="top"
title="Calificación inicial del lead"></i></label>
<?php
echo '<label class="alert-warning" role="alert">' . $error1 . '</label>';
?>
</div>
<!--end::Group-->
<!--begin::Group-->
<div class="form-group row">
<input type="hidden" id="cam_manual_old"
name="cam_manual_old" value="<?php echo $cam_manual; ?>">
<input type="hidden" id="cam_name" name="cam_name" value="<?
php echo $cam_name; ?>">
<input type="hidden" id="crm_id_old" name="crm_id_old" value="<?
php echo $crm_id; ?>">
<input type="hidden" id="cam_mail_template_old"
name="cam_mail_template_old" value="<?php echo $cam_mail_template; ?>">
<input type="hidden" id="prod_id" name="prod_id" value="<?php
echo $producto_id; ?>">
<input type="hidden" id="campaign_id" name="campaign_id"
value="<?php echo $id; ?>">
<input type="hidden" id="cam_atndays" name="cam_atndays"
value="<?php echo $cam_atndays2; ?>">
<input type="hidden" id="cam_notify" name="cam_notify" value="<?
php echo $cam_notify2; ?>">
<input type="hidden" id="cam_quali" name="cam_quali" value="<?
php echo $cam_quali; ?>">
<input type="hidden" id="cam_status" name="cam_status"
value="<?php echo $cam_status; ?>">
<input type="hidden" id="cam_atnstart_old"
name="cam_atnstart_old" value="<?php echo $cam_atnstart; ?>">
<input type="hidden" id="cam_atnend_old" name="cam_atnend_old"
value="<?php echo $cam_atnend; ?>">
<input type="hidden" id="cam_role_old" name="cam_role_old"
value="<?php echo $cam_role; ?>">
<input type="hidden" id="cam_shift_old" name="cam_shift_old"
value="<?php echo $cam_shift; ?>">
<input type="hidden" id="cam_duplicates_old"
name="cam_duplicates_old" value="<?php echo $cam_duplicates; ?>">
<input type="hidden" id="wab_bot_old" name="wab_bot_old"
value="<?php echo $wab_bot; ?> ">
<input type="hidden" id="cam_dupdays_old"
name="cam_dupdays_old" value="<?php echo $cam_dupdays; ?>">
<input type="hidden" id="iscore_old" name="iscore_old" value="<?
php echo $iscore; ?>">
<input type="hidden" id="channel_crmid_old"
name="channel_crmid_old" value="<?php echo $channel_crmid; ?>">
<input type="hidden" id="address_position_campaign_old"
name="address_position_campaign_old" value="<?php echo $address_position_campaign; ?
>">
<input type="hidden" id="latitud_position_campaign_old"
name="latitud_position_campaign_old" value="<?php echo $latitud_position_campaign; ?>">
<input type="hidden" id="longitud_position_campaign_old"
name="longitud_position_campaign_old" value="<?php echo $longitud_position_campaign; ?
>">
<input type="hidden" id="name_position_campaign_old"
name="name_position_campaign_old" value="<?php echo $name_position_campaign; ?>">
<input type="hidden" id="autoassign_time_ia_old"
name="autoassign_time_ia_old" value="<?php echo $autoassign_time_ia_old; ?>">
<input type="hidden" id="autoassign_lead_ia_old"
name="autoassign_lead_ia_old" value="<?php echo $autoassign_lead_ia_old; ?>">
<?php echo "<input type='hidden' id='grade_options_old'
name='grade_options_old' value='".$grade_options."'>" ?>
<button type="submit" name="campaign_change" class="btn btn-
primary">Guardar Cambios Campaña</button>
</div>
<!--end::Group-->
</div>
</div>
<!--end::Row-->
</div>
<!--end::Tab-->
</div>
</form>
</div>
</div>
<!--end::Card-->
</div>
<!--end::Container-->
</div>
<!--end::Entry-->
</div>
<!--end::Content-->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-
KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
$('#grade_bundle').ready(function(){
let option_selected = $('#grade_bundle').val();
let textArea = $('#gradeOptionDetail');
let grade_bundle = JSON.parse($('#grade_bundle').val());
var msg = '';
var keys = Object.keys(grade_bundle);
for(var i = 0; i < keys.length; i++){
var key = keys[i];
var value = grade_bundle[key];
msg += key + " - " + value + '\n';
}
$(textArea).val(msg);
});
$('#grade_bundle').change(function(){
let option_selected = $('#grade_bundle').val();
let textArea = $('#gradeOptionDetail');
if(option_selected != ''){
let grade_bundle = JSON.parse($('#grade_bundle').val());
let textArea = $('#gradeOptionDetail');
var msg = '';
var keys = Object.keys(grade_bundle);
for(var i = 0; i < keys.length; i++){
var key = keys[i];
var value = grade_bundle[key];
msg += key + " - " + value + '\n';
}
$(textArea).val(msg);
}else{
$(textArea).val('');
}
});
</script>
<script type="text/javascript">
$('#proAiSellerAssign').on('change', function(){
if($(this).is(':checked')){
$('#proAiTimeAssign').prop('disabled', false);
$('#proAiTimeAssign').prop('required', true);
} else {
$('#proAiTimeAssign').prop('value', '');
$('#proAiTimeAssign').prop('disabled', true);
$('#proAiTimeAssign').prop('required', false);
}
});
$('#proAiTimeAssign').on('change', function () {
if ($(this).val() !== '') {
$('#proAiSellerAssign').prop('checked', true).trigger('change');
}else{
$('#proAiSellerAssign').prop('checked', false).trigger('change');
}
});
$(document).ready(function() {
const cam_type = $('#camType').val();
if(cam_type == 'IA'){
$('#wab_msg').prop('required', false);
console.log('es ia');
// $('#divBot').css({
// 'display': 'none'
// })
// $('#divWabBot').css({
// 'display': 'none'
// })
}else{
$('#wab_msg').prop('required', true);
// $('#divBot').css({
// 'display': 'block'
// })
// $('#divWabBot').css({
// 'display': 'block'
// })
}
});
</script>
<script type="text/javascript">
async function processTwilioContent(templateSid) {
var accountSid = "<?php echo $twl_sellwab_acid; ?>";
var authToken = "<?php echo $twl_sellwab_at; ?>";
try {
const response = await fetch(`https://content.twilio.com/v1/Content/${templateSid}`, {
method: 'GET',
headers: {
'Authorization': `Basic ${btoa(`${accountSid}:${authToken}`)}`,
'Content-Type': 'application/json'
}
});
if (!response.ok) {
throw new Error('Network response was not ok');
}
const content = await response.json();
const bodyContent = content.types && content.types["twilio/quick-reply"]
? content.types["twilio/quick-reply"].body
: 'No existe mensaje';
return {
gotit: true,
body: bodyContent
};
} catch (error) {
console.error('Error fetching content:', error);
return {
gotit: false,
body: 'inexistent'
};
}
}
async function msg() {
$('#wabWelcome').remove();
$('#wabQuality').remove();
let idBot = $('#wab_msg').val();
let divFather = document.getElementById('divBot');
try {
const response = await $.ajax({
type: 'GET',
url: 'dist/js/ajax-campaigns/campaign-edit-bot.php',
cache: false,
data: { idBot: idBot },
dataType: 'JSON'
});
let len = response.length;
for (let i = 0; i < len; i++) {
let wabWelcome = response[i].wab_welcome;
let wabQuality = response[i].wab_quality;
let wabWelcomeBody = 'No existe mensaje';
let wabQualityBody = 'No existe mensaje';
if (wabWelcome) {
const welcomeResponse = await processTwilioContent(wabWelcome);
wabWelcomeBody = welcomeResponse.gotit ? welcomeResponse.body : 'No
existe mensaje';
}
if (wabQuality) {
const qualityResponse = await processTwilioContent(wabQuality);
wabQualityBody = qualityResponse.gotit ? qualityResponse.body : 'No existe
mensaje';
}
let echo_msg = "<div class='form-group row' id='wabWelcome'>" +
"<label class='col-form-label col-3 text-lg-right text-left'>Mensaje
principal</label>&nbsp;&nbsp;&nbsp;&nbsp;" +
"<textarea class='form-control col-4 form-control-lg form-control-solid
text-muted' rows='5' readonly>" + wabWelcomeBody + "</textarea>" +
"</div>" +
"<div class='form-group row' id='wabQuality'>" +
"<label class='col-form-label col-3 text-lg-right text-left'>Mensaje para
quality</label>&nbsp;&nbsp;&nbsp;&nbsp;" +
"<textarea class='form-control col-4 form-control-lg form-control-solid
text-muted' rows='5' readonly>" + wabQualityBody + "</textarea>" +
"</div>";
$(echo_msg).insertAfter(divFather);
}
} catch (error) {
console.error('Error in msg function:', error);
}
}
</script>
<?php
include_once("footer.php");
include_once("scripts_main.php");
include_once("end.php");
?>