<?php

use App\Forum\ForumAcp;

ForumAcp::addGroup(array(
    'parent' => 'root',
    'id'     => 'users',
    'title'  => 'Users',
));

ForumAcp::addMenu(array(
    'parent' => 'users',
    'id'     => 'bans',
    'title'  => 'Ban user',
    'href'   => 'users-ban',
    'screen' => 'acp_html_users_ban',
));

ForumAcp::addMenu(array(
    'parent' => 'users',
    'id'     => 'ranks',
    'title'  => 'User ranks',
    'href'   => 'users-rank',
    'screen' => 'acp_html_users_rank',
));

ForumAcp::addMenu(array(
    'parent' => 'users',
    'id'     => 'groups',
    'title'  => 'Manage groups',
    'href'   => 'groups',
    'screen' => 'acp_html_groups',
));

function acp_html_users_ban()
{
    return;
}

function acp_html_users_rank()
{
    return;
}

function acp_html_groups()
{
    return;
}
