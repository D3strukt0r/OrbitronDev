<?php

use App\Forum\ForumAcp;

ForumAcp::addGroup(array(
    'parent' => 'root',
    'id'     => 'posting',
    'title'  => 'Posting',
));

ForumAcp::addMenu(array(
    'parent' => 'posting',
    'id'     => 'bbcode',
    'title'  => 'BBCode',
    'href'   => 'posting-bbcode',
    'screen' => 'acp_html_posting_bbcode',
));

function acp_html_posting_bbcode()
{
    return '';
}
