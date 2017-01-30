var ControlPanelSettings = {
	'elementLoader' : null,
	'elementPage' : null,
	'debug' : false,
	'ajaxPageTarget': null,
	'elementTask': null,
	'controlPanelPageSystem': 'href'
};

var ControlPanel = {
	'init' : function(oPageTargetElement, oLoaderIconElement) {
		ControlPanelSettings.elementPage = oPageTargetElement;
		ControlPanelSettings.elementLoader = oLoaderIconElement;
		
		if(ControlPanelSettings.debug)
			console.debug('[ControlPanel] Initted');
	},
	'startLoading' : function() {
		$(ControlPanelSettings.elementLoader).show();
	},
	'stopLoading' : function() {
		$(ControlPanelSettings.elementLoader).hide();
	},
	'changePage' : function(sPageName) {
		if(typeof ControlPanelSettings.ajaxPageTarget == 'object')
		{
			if(ControlPanelSettings.debug)
				console.error('[ControlPanel] Can\'t change page because no url has been defined');
			return;
		}
			
		ControlPanel.startLoading();
		$.ajax({
			url : ControlPanelSettings.ajaxPageTarget,
			data : {
				a : 'getTemplate',
				t : 'account/page/' + sPageName
			},
			complete : function() {
				ControlPanel.stopLoading();
			},
			success : function(data) {
				$(ControlPanelSettings.elementPage).empty().append($(data));
				
				if(ControlPanelSettings.debug)
					console.debug('[ControlPanel] Page changed to "' + sPageName + '"');
			},
			error : function() {
				console.error('[ControlPanel] Could not load page "' + sPageName + '"');
			}
		});
	}
};

var ControlPanelTaskData = {
	'lastTask': 0
};
var ControlPanelTask = function(title, type) {
	var id = ControlPanelTaskData.lastTask + 1;
	ControlPanelSettings.elementTask.append('<li class="task-' + id + '">' +
												'<a href>' +
													'<div>' +
														'<p>' +
															'<strong>' + title + '</strong>' +
															'<span class="pull-right text-muted">0% Complete</span>' +
														'</p>' +
														'<div class="progress progress-striped active">' +
															'<div class="progress-bar progress-bar-' + type + '" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">' +
																'<span class="sr-only">0% Complete (' + type + ')</span>' +
															'</div>' +
														'</div>' +
													'</div>' +
												'</a>' +
											'</li>');
};
ControlPanelTask.prototype.set = function(element, terminal, commands) {
};

ControlPanelTask.prototype.get = function(element, terminal, commands) {
};

ControlPanelTask.prototype.remove = function(element, terminal, commands) {
};