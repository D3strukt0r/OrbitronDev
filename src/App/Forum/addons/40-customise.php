<?php

use App\Forum\ForumAcp;

ForumAcp::addGroup(array(
    'parent' => 'root',
    'id'     => 'customise',
    'title'  => 'Customise',
));

ForumAcp::addMenu(array(
    'parent' => 'customise',
    'id'     => 'theme',
    'title'  => 'Themes',
    'href'   => 'customise-theme',
    'screen' => 'acp_html_customise_theme',
));

function acp_html_customise_theme()
{
    return;
}
