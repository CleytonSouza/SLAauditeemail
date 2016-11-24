<?php
$this->breadcrumbs=array(
  'Audit',
  ); 
Yii::import('ext.yii-mail.*');
?>



<script>
 $(document).ready(function(){
   setInterval(function(){cache_clear()},60000);
 });
 function cache_clear()
 {
   window.location.reload(true);
 }
</script>

<!--
1min = 60000
2min = 120000
5min = 300000
8min = 480000
10min = 600000
15min = 900000
20min = 1,2e+6
-->

<?php

/*---------------------------------------------------*/

#Pega hora atual do servidor 
#ano
$ano_atual = date('Y');
#mes
$mes_atual = date('m');
#Dia
$dia_atual = date('d');

/*---------------------------------------------------*/


/*---------------------------------------------------*/

#Tempo total para realizar o processo de Moto-Mensageria
#Tempo total
#3 Horas
#                                               3 h 
$tempototalprocesso = date('H:i:s', strtotime('030000'));




#Após efetuar o check-in o SLA de coleta será de 1 hora / sms ao cliente  
#SLA DE COLETA 
 #check-in                            01:00:00 
$slacoleta = date('H:i:s', strtotime('010000'));


#SLA DE ENTREGA
 #2 HORAS
# sms - iNdicando que o portador está no local
#                                    02:00:00 
$slaentrega =date('H:i:s', strtotime('020000')); 




#Tempo final de envio do SMS para status igual a impresso
# 10 min
$statusentregamaior =date('H:i:s', strtotime('001000'));
#                            30min                       
#date('H:i:s', strtotime('003000'));




#Tempo final de envio do SMS para status igual a impresso
# 10 min
$ocorrenciacoleta =date('H:i:s', strtotime('001000'));
#                            30min                       
#date('H:i:s', strtotime('003000'));


#Tempo final de envio do SMS para status igual a impresso
# 10 min
$ocorrenciaentrega =date('H:i:s', strtotime('001000'));
#                            30min                       
#date('H:i:s', strtotime('003000'));


/*---------------------------------------------------*/



/*---------------------------------------------------*/

//conexao
include 'conexao\conexao.php';

#$ano_atual-$mes_atual-$dia_atual%
#$sql = "SELECT * FROM easytracking.tb_espo 
#WHERE ts_impressao LIKE '$ano_atual-$mes_atual-$dia_atual%' ORDER BY track_expo DESC";


$sql = "SELECT b.telefone, a.* FROM easytracking.tb_espo as a
left join tb_espo_entrega as b
on a.track_expo = b.track_expo
where ts_impressao like '$ano_atual-$mes_atual-$dia_atual%' ORDER BY track_expo DESC";

 #inner join  easytracking.tb_espo_entrega as b
 #$sql = "SELECT * FROM easytracking.tb_espo as a
 #inner join easytracking.tb_ocorrencias_coleta_doc_externos as b
 #inner join easytracking.tb_ocorrencias_entrega_doc_externos as c
 #WHERE a.ts_impressao LIKE '$ano_atual-$mes_atual-$dia_atual%' ORDER BY track_expo DESC";

$resultado = mysql_query($sql, $conecta);
$numRegistros = mysql_num_rows($resultado);
if($numRegistros !=0) {
 $tabela = "<table class='table table-bordered'>

 <thead> 
   <tr>
     <th style='color: #fff; background-color: #000;'>Tracking</th> 
     <th style='color: #fff; background-color: #000;'>Remetente</th>
     <th style='color: #fff; background-color: #000;'>Destinatário</th>
     <th style='color: #fff; background-color: #000;'>Status</th> 
     <th style='color: #fff; background-color: #000;'>Data da Geração</th>
   </tr> 
 </thead> 

 "; 

          //Retorna os Resultados da tabela
 $return = "$tabela"; 



 /*---------------------------------------------------*/

// Captura os dados da consulta e inseri na tabela HTML // Por linha
 while ($linha = mysql_fetch_array($resultado)) { 



  /*---------------------------------------------------*/ 
  
  /*Tracking*/
  $tracking = $linha["track_expo"];
  

  /*Flag Email*/
  
  $flag = $linha["flag_email"];
  

  /*  Stap 1 */
  $status = $linha["status"];

 #data
 #Armazena o valor do ts_impressao na variavel $data
  $data = $linha["ts_impressao"];
  $hora = date('H:i:s', strtotime($data));

 #Busca a hora atual do servidor
  $hora_atual = date('H:i:s');
  $tempo = gmdate('H:i:s', strtotime($hora_atual) - strtotime($hora));
  /*  Stap 1 */
  /*---------------------------------------------------*/

  $solicitacao = "";
  # if
  # Status for identico a o status
  # || ou
  if($status === 'Impresso' || $status === 'Transportadora' || $status === 'Em transporte' || $status === 'Entregue' || $status === 'Ocorrencia coleta' || $status === 'Ocorrencia entrega' || $status === 'Devolvido'){

   $solicitacao = "documento";
 } 
 else{
   $solicitacao = "n";
 }
          #se 
          #identico 
          #&& E verdadeiro, verdadeiro
        # if($solicitacao === "documento" && $tempo > $tempototalprocesso){
        # 1
 if( ($status === 'Impresso') && ($tempo > $tempototalprocesso) ){
            #Vermelho
  $return.= "<td><b><font color='#FF0000'>" . utf8_encode($linha["track_expo"]) . "</td></font></b>"; 
  $return.= "<td><b><font color='#FF0000'>" . utf8_encode($linha["d_nome"]) . "</td></font></b>";
  $return.= "<td><b><font color='#FF0000'>" . utf8_encode($linha["p_email"]) . "</td></font></b>";
  $return.= "<td><b><font color='#FF0000'>" . utf8_encode($linha["status"]) . "</td></font></b>";
  $return.= "<td><b><font color='#FF0000'>" . utf8_encode($linha["ts_impressao"]) . "</td></font></b>";  
  $return.= "</tr>"; 

           #Envia e-mail para expedicao(Tempo para entrega excedido).

           # SLA do Total do processo (3h) foi ultrapassado.    
           #Ultrapassou 3h apos criação da solicitação[ts_impressão].

               /* Enviar Email -> SMS   
             
                 $message = new YiiMailMessage('Tempo da solicitacao foi excedido'.$linha['track_expo']);
                 $message->view = 'email2';
                 $message->setBody(array('model' => $model), 'text/html');
                    #sms
                 $message->addTo('cleyton.souza@pb.com');
                  #Expedicao
                 #$message->addTo('');#Email destino
                 $message->from = Yii::app()->params['eviaremail'];
                 Yii::app()->mail->send($message);  */

               }
               else

                 if( ($status === "Transportadora") && ($tempo > $tempototalprocesso) ){
            #Amarelo (ok)
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["track_expo"]) . "</td></font></b>"; 
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["d_nome"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["p_email"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["status"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["ts_impressao"]) . "</td></font></b>";  
                  $return.= "</tr>"; 

                    #Envia e-mail operação.
                    #SMS CLIENTE FINAL
                }

                else
                 #&& ($tempo > $tempototalprocesso)
                 if( ($status === "Em transporte")  ){
                  #Amarelo (ok)
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["track_expo"]) . "</td></font></b>"; 
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["d_nome"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["p_email"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["status"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["ts_impressao"]) . "</td></font></b>";  
                  $return.= "</tr>"; 

                 #Envia e-mail operação.
                 #SMS CLIENTE FINAL
                  if( ($status === 'Em transporte') &&  ($flag  === '1') ){

                   /* Enviar Email -> SMS  */
                   #$numerotelefone = '5511973037778'; 
                   $message = new YiiMailMessage($linha['0']); #numero do remetente
                   #$message = new YiiMailMessage($numerotelefone); #numero do remetente
                   #$message->view = 'email5';
                   #$message->setBody(array('model' => $model), 'text/html');
                   $message->setBody(
                     '<html>' .
                     ' <head></head>'.
                     ' <body>'.
                     '<p>Objeto em Transporte</p><br />'.
                     '<br />'.
                     '<p>|Numero do Pedido:'.$linha['3'].'</p><br />'.
                     '<br />'.
                     '<p>|Nome Destino:'.$linha['59'].'</p><br />'.
                     '<br />'.
                     '<p>|Sistema EasyTracking</p><br />'.
                     '<br />'.
                     ' </body>'.
                     '</html>',
                     'text/html'
                     );
                   $message->addTo('pitneybowers@connect.zenvia360.com');
                   $message->from = Yii::app()->params['eviaremail'];
                   Yii::app()->mail->send($message);  

                   
                   $sql_espo = "UPDATE easytracking.tb_espo SET  flag_email = '2' WHERE track_expo = '$tracking'";
                   $result_espo = mysql_query($sql_espo, $conecta) or die(mysql_error());

                 }
                 


               }
               /*---------------------------------------------------*/
               /*---------------------------------------------------*/
               else
                if( ($status === "Devolvido")){
                    #Amarelo (ok)
                  $return.= "<td><b><font color='#ffa500'>" . utf8_encode($linha["track_expo"]) . "</td></font></b>"; 
                  $return.= "<td><b><font color='#ffa500'>" . utf8_encode($linha["d_nome"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#ffa500'>" . utf8_encode($linha["p_email"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#ffa500'>" . utf8_encode($linha["status"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#ffa500'>" . utf8_encode($linha["ts_impressao"]) . "</td></font></b>";  
                  $return.= "</tr>"; 

                  if( ($status === 'Devolvido') &&  ($flag  === '1') ){

                   /* Enviar Email -> SMS  */
                   #$numerotelefone = '55119973037778'; 
                   $message = new YiiMailMessage($linha['0']); #numero do remetente
                   #$message->view = 'email3';
                   #$message->setBody(array('model' => $model), 'text/html');
                   $message->setBody(
                     '<html>' .
                     ' <head></head>'.
                     ' <body>'.
                     '<p>Objeto Devolvido.</p><br />'.
                     '<br />'.
                     '<p>|Numero do Pedido:'.$linha['3'].'</p><br />'.
                     '<br />'.
                     '<br />'.
                     '<p>|Sistema EasyTracking</p><br />'.
                     '<br />'.
                     ' </body>'.
                     '</html>',
                     'text/html'
                     );
                   $message->addTo('pitneybowers@connect.zenvia360.com');
                   $message->from = Yii::app()->params['eviaremail'];
                   Yii::app()->mail->send($message);  

                   
                   $sql_espo = "UPDATE easytracking.tb_espo SET  flag_email = '2' WHERE track_expo = '$tracking'";
                   $result_espo = mysql_query($sql_espo, $conecta) or die(mysql_error());


                 }

               }

               else
                 #Ocorrencia Coleta / 
                 #SMS CLIENTE FINAL
                 if( ($status === "Ocorrencia coleta") && ($tempo > $ocorrenciacoleta) ){
                    #Amarelo (ok)
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["track_expo"]) . "</td></font></b>"; 
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["d_nome"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["p_email"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["status"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["ts_impressao"]) . "</td></font></b>";  
                  $return.= "</tr>"; 

                  if( ($status === 'Ocorrencia coleta') &&  ($flag  === '1') ){

                   /* Enviar Email -> SMS  */
                   #$numerotelefone = '55119973037778'; 
                   $message = new YiiMailMessage($linha['0']); #numero do remetente
                   #$message->view = 'email3';
                   #$message->setBody(array('model' => $model), 'text/html');
                   $message->setBody(
                     '<html>' .
                     ' <head></head>'.
                     ' <body>'.
                     '<p>Nao foi possivel coletar o objeto</p><br />'.
                     '<br />'.
                     '<p>|Numero do Pedido:'.$linha['3'].'</p><br />'.
                     '<br />'.
                     '<p>|Nome Destino:'.$linha['59'].'</p><br />'.
                     '<br />'.
                     '<p>|Sistema EasyTracking</p><br />'.
                     '<br />'.
                     ' </body>'.
                     '</html>',
                     'text/html'
                     );
                   $message->addTo('pitneybowers@connect.zenvia360.com');
                   $message->from = Yii::app()->params['eviaremail'];
                   Yii::app()->mail->send($message);  

                   
                   $sql_espo = "UPDATE easytracking.tb_espo SET  flag_email = '2' WHERE track_expo = '$tracking'";
                   $result_espo = mysql_query($sql_espo, $conecta) or die(mysql_error());

                 }

               }
               else
                 #Ocorrencia de Entrega / 
                 #SMS CLIENTE FINAL
                if( ($status === "Ocorrencia entrega") && ($tempo > $ocorrenciacoleta)){
                  #Amarelo (ok)
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["track_expo"]) . "</td></font></b>"; 
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["d_nome"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["p_email"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["status"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#FFFF00'>" . utf8_encode($linha["ts_impressao"]) . "</td></font></b>";  
                  $return.= "</tr>"; 

                  if( ($status === 'Ocorrencia entrega') &&  ($flag  === '1') ){

                   /* Enviar Email -> SMS  */
                   #$numerotelefone = '5511973037778'; 
                   #$message = new YiiMailMessage($numerotelefone); #numero do remetente
                   $message = new YiiMailMessage($linha['0']); #numero do remetente
                   #$message->view = 'email2';
                   #$message->setBody(array('model' => $model), 'text/html');
                   $message->setBody(
                     '<html>' .
                     ' <head></head>'.
                     ' <body>'.
                     '<p>Nao foi possivel entregar o objeto</p><br />'.
                     '<br />'.
                     '<p>|Numero do Pedido:'.$linha['3'].'</p><br />'.
                     '<br />'.
                     '<p>|Nome Destino:'.$linha['59'].'</p><br />'.
                     '<br />'.
                     '<p>|Sistema EasyTracking</p><br />'.
                     '<br />'.
                     ' </body>'.
                     '</html>',
                     'text/html'
                     );
                   $message->addTo('pitneybowers@connect.zenvia360.com');
                   $message->from = Yii::app()->params['eviaremail'];
                   Yii::app()->mail->send($message);  

                   
                   $sql_espo = "UPDATE easytracking.tb_espo SET  flag_email = '2' WHERE track_expo = '$tracking'";
                   $result_espo = mysql_query($sql_espo, $conecta) or die(mysql_error());
                  
                 }
                 

               } 
               /*---------------------------------------------------*/
               /*---------------------------------------------------*/


        #Processo de entrega encerrado
        #envia email para o cliente final/ SMS 
               else
                if(($status === 'Entregue') && ($tempo > $statusentregamaior)){
            #Azul (OK)
                  $return.= "<td><b><font color='#0000FF'>" . utf8_encode($linha["track_expo"]) . "</td></font></b>"; 
                  $return.= "<td><b><font color='#0000FF'>" . utf8_encode($linha["d_nome"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#0000FF'>" . utf8_encode($linha["p_email"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#0000FF'>" . utf8_encode($linha["status"]) . "</td></font></b>";
                  $return.= "<td><b><font color='#0000FF'>" . utf8_encode($linha["ts_impressao"]) . "</td></font></b>";  
                  $return.= "</tr>"; 


                  if( ($status === 'Entregue') &&  ($flag  === '1') ){

                   /* Enviar Email -> SMS  */
                   #$numerotelefone = '5511994438453'; 
                   $message = new YiiMailMessage($linha['0']); #numero do remetente
                   #$message = new YiiMailMessage($numerotelefone);
                   #$message->view = 'email4';
                   #$message->setBody(array('model' => $model), 'text/html');
                   $message->setBody(
                     '<html>' .
                     ' <head></head>'.
                     ' <body>'.
                     '<p>Objeto Entregue</p><br />'.
                     '<br />'.
                     '<p>|Numero do Pedido:'.$linha['3'].'</p><br />'.
                     '<br />'.
                     '<p>|Nome Destino:'.$linha['59'].'</p><br />'.
                     '<br />'.
                     '<p>|Sistema EasyTracking</p><br />'.
                     '<br />'.
                     ' </body>'.
                     '</html>',
                     'text/html'
                     );
                   $message->addTo('pitneybowers@connect.zenvia360.com');
                   $message->from = Yii::app()->params['eviaremail'];
                   Yii::app()->mail->send($message);  

                   
                   $sql_espo = "UPDATE easytracking.tb_espo SET  flag_email = '2' WHERE track_expo = '$tracking'";
                   $result_espo = mysql_query($sql_espo, $conecta) or die(mysql_error());
                  
                  }

               }
               else
          #verde
               {

                 $return.= "<td><b><font color='#00FF00'>".utf8_encode($linha["track_expo"])."</td></font></b>"; 
                 $return.= "<td><b><font color='#00FF00'>".utf8_encode($linha["d_nome"])."</td></font></b>";
                 $return.= "<td><b><font color='#00FF00'>".utf8_encode($linha["p_email"])."</td></font></b>";
                 $return.= "<td><b><font color='#00FF00'>".utf8_encode($linha["status"])."</td></font></b>";
                 $return.= "<td><b><font color='#00FF00'>".utf8_encode($linha["ts_impressao"])."</td></font></b>";
                 #$return.= "<td><b><font color='#00FF00'>".utf8_encode($linha["0"])."</td></font></b>";  
                 $return.= "</tr>"; 

               }


   }#while
   echo $return.="</tbody></table>";
}#if($numRegistro !=0)
mysql_close($conecta);