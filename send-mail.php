<html>
<form method="POST" action="">
    <p>
	Send to:
	<input type="text" name="receiver">
    </p>
    <p>
	Subject:
	<input type="text" name="subject">
    </p>
    <p>
	Message:
	<textarea name="message"></textarea>
    </p>
    <input type="submit">
</form>

<?php
//print_invoice.php
$pas = $_REQUEST["mail"];
$ps= $_REQUEST["id"];
echo $pas;
echo $ps;

if($pas && $ps)
{
 require_once 'pdf.php';
 include('database_connection.php');
 $output = '';
 $statement = $connect->prepare("
  SELECT * FROM tbl_order 
  WHERE order_id = :order_id
  LIMIT 1
 ");
 $statement->execute(
  array(
   ':order_id'       =>  $ps
  )
 );
 $result = $statement->fetchAll();
 foreach($result as $row)
 {
  $output .= '
   <table width="100%" border="1" cellpadding="5" cellspacing="0">
    <tr>
     <td colspan="2" align="center" style="font-size:18px"><b>Invoice</b></td>
    </tr>
    <tr>
     <td colspan="2">
      <table width="100%" cellpadding="5">
       <tr>
        <td width="65%">
         To,<br />
         <b>RECEIVER (BILL TO)</b><br />
         Name : '.$row["order_receiver_name"].'<br /> 
         Billing Address : '.$row["order_receiver_address"].'<br />
        </td>
        <td width="35%">
         Reverse Charge<br />
         Invoice No. : '.$row["order_no"].'<br />
         Invoice Date : '.$row["order_date"].'<br />
        </td>
       </tr>
      </table>
      <br />
      <table width="100%" border="1" cellpadding="5" cellspacing="0">
       <tr>
        <th>Sr No.</th>
        <th>Item Name</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Actual Amt.</th>
        <th colspan="2">Tax1 (%)</th>
        <th colspan="2">Tax2 (%)</th>
        <th colspan="2">Tax3 (%)</th>
        <th rowspan="2">Total</th>
       </tr>
       <tr>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th>Rate</th>
        <th>Amt.</th>
        <th>Rate</th>
        <th>Amt.</th>
        <th>Rate</th>
        <th>Amt.</th>
       </tr>';
  $statement = $connect->prepare(
   "SELECT * FROM tbl_order_item 
   WHERE order_id = :order_id"
  );
  $statement->execute(
   array(
    ':order_id'       =>  $ps
   )
  );
  $item_result = $statement->fetchAll();
  $count = 0;
  foreach($item_result as $sub_row)
  {
   $count++;
   $output .= '
   <tr>
    <td>'.$count.'</td>
    <td>'.$sub_row["item_name"].'</td>
    <td>'.$sub_row["order_item_quantity"].'</td>
    <td>'.$sub_row["order_item_price"].'</td>
    <td>'.$sub_row["order_item_actual_amount"].'</td>
    <td>'.$sub_row["order_item_tax1_rate"].'</td>
    <td>'.$sub_row["order_item_tax1_amount"].'</td>
    <td>'.$sub_row["order_item_tax2_rate"].'</td>
    <td>'.$sub_row["order_item_tax2_amount"].'</td>
    <td>'.$sub_row["order_item_tax3_rate"].'</td>
    <td>'.$sub_row["order_item_tax3_amount"].'</td>
    <td>'.$sub_row["order_item_final_amount"].'</td>
   </tr>
   ';
  }
  $output .= '
  <tr>
   <td align="right" colspan="11"><b>Total</b></td>
   <td align="right"><b>'.$row["order_total_after_tax"].'</b></td>
  </tr>
  <tr>
   <td colspan="11"><b>Total Amt. Before Tax :</b></td>
   <td align="right">'.$row["order_total_before_tax"].'</td>
  </tr>
  <tr>
   <td colspan="11">Add : Tax1 :</td>
   <td align="right">'.$row["order_total_tax1"].'</td>
  </tr>
  <tr>
   <td colspan="11">Add : Tax2 :</td>
   <td align="right">'.$row["order_total_tax2"].'</td>
  </tr>
  <tr>
   <td colspan="11">Add : Tax3 :</td>
   <td align="right">'.$row["order_total_tax3"].'</td>
  </tr>
  <tr>
   <td colspan="11"><b>Total Tax Amt.  :</b></td>
   <td align="right">'.$row["order_total_tax"].'</td>
  </tr>
  <tr>
   <td colspan="11"><b>Total Amt. After Tax :</b></td>
   <td align="right">'.$row["order_total_after_tax"].'</td>
  </tr>
  
  ';
  $output .= '
      </table>
     </td>
    </tr>
   </table>
  ';
 }
 $pdf = new Pdf();
 $mypdf = $pdf->Output();
}
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = 0;                                       // Enable verbose debug output
    $mail->isSMTP();                                            // Set mailer to use SMTP
    $mail->Host       = 'smtp.gmail.com;';  // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = 'smm.vshungry@gmail.com';                     // Your gmail address
    $mail->Password   = 'Asdf726882343#';                               // Your gmail password
    $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
    $mail->Port       = 587;                                    // TCP port to connect to

    //Recipients
    $mail->setFrom('smm.vshungry@gmail.com', 'Rafshan');
    $mail->addAddress($_POST["receiver"]);

    $mail->addStringAttachment($mypdf, 'invoice.pdf');

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $_POST["subject"];
    $mail->Body    = $_POST["message"];

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
</html>