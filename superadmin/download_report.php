<?php

include("../config/config.php");

require '../vendor/autoload.php';

use Dompdf\Dompdf;

/* =============================
   GET FILTER VALUES
============================= */

$college_filter = $_GET['college_id'] ?? '';
$hostel_filter  = $_GET['hostel_id'] ?? '';

$where = [];

if($college_filter != ''){
    $where[] = "sa.college_id = $college_filter";
}

if($hostel_filter != ''){
    $where[] = "sa.hostel_id = $hostel_filter";
}

$where_sql = "";

if(count($where) > 0){
    $where_sql = "WHERE " . implode(" AND ", $where);
}

/* =============================
   FETCH DATA
============================= */

$query = "
SELECT 
h.name AS hostel_name,
c.name AS college_name,
sa.allocated_seats,
sa.hostel_id,
sa.college_id
FROM seat_allocation sa
JOIN hostels h ON sa.hostel_id = h.id
JOIN colleges c ON sa.college_id = c.id
$where_sql
";

$result = mysqli_query($conn,$query);

/* =============================
   TOTAL VARIABLES
============================= */

$total_allocated = 0;
$total_admitted  = 0;
$total_vacant    = 0;

/* =============================
   BUILD HTML
============================= */

$html = "

<h2 style='text-align:center'>Hostel Allocation Report</h2>

<p><b>Date:</b> ".date("d-m-Y")."</p>

<table border='1' width='100%' cellpadding='8' cellspacing='0'>

<tr style='background:#eee'>

<th>Hostel</th>
<th>College</th>
<th>Allocated Seats</th>
<th>Admitted Students</th>
<th>Vacant Seats</th>

</tr>

";

while($row = mysqli_fetch_assoc($result)){

$count_query = mysqli_query($conn, "

SELECT COUNT(*) as admitted

FROM students

WHERE hostel_id = {$row['hostel_id']}

AND college_id = {$row['college_id']}

");

$count_data = mysqli_fetch_assoc($count_query);

$admitted = $count_data['admitted'];

$vacant = $row['allocated_seats'] - $admitted;

$total_allocated += $row['allocated_seats'];
$total_admitted  += $admitted;
$total_vacant    += $vacant;

$html .= "

<tr>

<td>{$row['hostel_name']}</td>

<td>{$row['college_name']}</td>

<td>{$row['allocated_seats']}</td>

<td>{$admitted}</td>

<td>{$vacant}</td>

</tr>

";

}

/* =============================
   TOTAL ROW
============================= */

$html .= "

<tr style='background:#ddd;font-weight:bold'>

<td colspan='2'>TOTAL</td>

<td>{$total_allocated}</td>

<td>{$total_admitted}</td>

<td>{$total_vacant}</td>

</tr>

</table>

";

/* =============================
   GENERATE PDF
============================= */

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4','portrait');

$dompdf->render();

$dompdf->stream("hostel_report.pdf", ["Attachment" => true]);

?>