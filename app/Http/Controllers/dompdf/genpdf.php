<?php
namespace App\Http\Controllers\dompdf;
use Illuminate\Http\Request;
use PdoGsb;
use MyDate;
use Dompdf\Dompdf;
use Dompdf\Options;


require_once 'includes/dompdf/autoload.inc.php';

        $option = new Options();
        $option ->set('defaultFont', 'Courier');

        

        ob_start();
        require_once __DIR__.'test.html';
        $html = ob_get_contents();/*permet d'avoir le fichier infovisiteurArchive.blade.php dans $html*/
        ob_end_clean();

        $dompdf = new Dompdf($option);

        $dompdf->loadHtml($html);/* mettre de html*/

        
        $dompdf->setPaper('A4', 'portrait');/* information sur la forme du fichier */

        $fichier = "user--document";

        $dompdf->stream($fichier);

        $dompdf -> render (); 




?>