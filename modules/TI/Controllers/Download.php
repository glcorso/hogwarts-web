<?php

namespace Lidere\Modules\TI\Controllers;

use Lidere\Controllers\Controller;

class TI extends Controller {

    public function download() {

        $get = $this->app->request->get();

        $fileUrl = $get['fileUrl'];

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"" . basename($fileUrl) . "\""); 
        
        readfile($fileUrl);
    }

}