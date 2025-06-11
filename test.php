<?php
require_once __DIR__ . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML('<h1>Hello, mPDF works!</h1>');
$mpdf->Output('test.pdf', \Mpdf\Output\Destination::FILE);

echo "PDF generated!";