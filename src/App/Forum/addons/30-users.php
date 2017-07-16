<?php

use App\Forum\ForumAcp;

ForumAcp::addGroup(array(
	'parent' => 'root',
	'id'     => 'users',
	'title'  => _('Users'),
));

ForumAcp::addMenu(array(
	'parent' => 'users',
	'id'     => 'bans',
	'title'  => _('Ban user'),
	'href'   => 'users-ban',
	'screen' => 'acp_html_users_ban',
));

ForumAcp::addMenu(array(
	'parent' => 'users',
	'id'     => 'ranks',
	'title'  => _('User ranks'),
	'href'   => 'users-rank',
	'screen' => 'acp_html_users_rank',
));

ForumAcp::addMenu(array(
	'parent' => 'users',
	'id'     => 'groups',
	'title'  => _('Manage groups'),
	'href'   => 'groups',
	'screen' => 'acp_html_groups',
));

function acp_html_users_ban() {}
function acp_html_users_rank() {}
function acp_html_groups() {}
