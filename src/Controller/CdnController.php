<?php

namespace Controller;

class CdnController extends \Controller
{
    private $libraries = array(
        'text/javascript' => array(
            'bootstrap.min.js' => 'vendor/bootstrap/dist/js/bootstrap.min.js',
        ),
        'text/css' => array(

        ),
    );

    private $mime = array(
        'text/javascript' => 'js',
        'text/css' => 'css',
    );

    public function indexAction()
    {
        echo '<ul>';

        foreach ($this->libraries as $type => $libraries) {
            echo '<h2>' . $type . '</h2>';
            foreach($libraries as $key => $libray) {
                $fileSize = (float) ceil(filesize(\Kernel::getIntent()->getRootDir() . '/web/' . $this->libraries[$type][$key]) / 1024);
                echo '<li>
<a href="' . $this->generateUrl('app_cdn_library', array('libraries' => $key, 'type' => $this->mime[$type])) . '">' . $key . '</a> ('.$fileSize.'KB)
</li>';
            }
        }

        echo '</ul>';
    }

    public function sourceAction()
    {
        $type = $this->getRequest()->query->get('type');
        if($type) {
            if($type == 'css') {
                $sOutputType = 'text/css';
            } elseif($type == 'js') {
                $sOutputType = 'text/javascript';
            } else {
                $sOutputType = 'text/plain';
            }
        } else {
            $sOutputType = 'text/plain';
        }
        header('Content-Type: ' . $sOutputType);

        if(!isset($this->parameters['libraries'])) {
            header('Content-Type: text/plain');
            echo 'You have to give the files you need';
            exit;
        }
        $aFiles = explode(':', $this->parameters['libraries']);

        foreach($aFiles as $sFile) {
            $sFileLoc = \Kernel::getIntent()->getRootDir() . '/web/' . $this->libraries[$sOutputType][$sFile];
            if(file_exists($sFileLoc)) {
                echo "/* " . $sFileLoc . "*/
/* BEGIN FILE */
" . file_get_contents($sFileLoc) . "
/* END FILE */\n\r\n\r";
            }
        }
    }
}
