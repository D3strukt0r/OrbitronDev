<?php

namespace Controller;

use Controller;

class DeveloperController extends Controller
{

    private $pages = array(
        'oauth_unauthorized' => 'oauth/unauthorized.php',
        'oauth_authorized'   => 'oauth/authorized.php',
        'oauth_token'        => 'oauth/token.php',
        'oauth_resource'     => 'oauth/resource.php',
    );

    public function indexAction()
    {
        echo '<ul>';

        foreach ($this->pages as $index => $page) {
            $fileSize = (float) ceil(filesize(\Kernel::getIntent()->getRootDir() . '/src/App/Developer/pages/' . $page) / 1024);
            echo '<li>
<a href="' . $this->generateUrl('app_developer_page', array('page' => $page)) . '">' . $page . '</a> ('.$fileSize.'KB)
<a href="' . $this->generateUrl('app_developer_download', array('file' => $index)) . '">Download</a>
</li>';
        }

        echo '</ul>';
    }

    public function pageAction()
    {
        include \Kernel::getIntent()->getRootDir() . '/src/App/Developer/pages/' . $this->parameters['page'];
    }

    public function downloadAction()
    {
        $directory = \Kernel::getIntent()->getRootDir() . '/src/App/Developer/pages/';
        $file      = $this->pages[$this->parameters['file']];
        $fileLoc   =          $directory . $file;

        if(!file_exists($fileLoc)) {
            echo 'File not found';
            exit;
        }

        header('Content-Type: application/force-download');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length: ' . filesize($fileLoc));
        header('Content-Disposition: attachment; filename = ' . basename($fileLoc));
        readfile($fileLoc);
    }
}
