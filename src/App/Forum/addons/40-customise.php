<?php

use App\Forum\ForumAcp;

ForumAcp::addGroup(array(
	'parent' => 'root',
	'id'     => 'customise',
	'title'  => _('Customise'),
));

ForumAcp::addMenu(array(
	'parent' => 'customise',
	'id'     => 'theme',
	'title'  => _('Themes'),
	'href'   => 'customise-theme',
	'screen' => 'acp_html_customise_theme',
));

function acp_html_customise_theme() {}
