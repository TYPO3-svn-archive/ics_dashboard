// jQuery.noConflict();
function supprModule(moduleuid,dashboard,msg) {
	jQuery('#ics'+dashboard+'_'+moduleuid).hide('slow');
	if (window.confirm(msg)) {
		//jQuery("#info").load('index.php?eID=ics_dashboard&tx_icsdashboard_pi1[suppr]=1&tx_icsdashboard_pi1[uid]='+moduleuid);
		//jQuery("#ics_dashboard_dashboard_"+dashboard).load('index.php?eID=ics_dashboard&tx_icsdashboard_pi1[suppr]=1&tx_icsdashboard_pi1[uid]='+moduleuid);
		jQuery.ajax({url: 'index.php?eID=ics_dashboard&tx_icsdashboard_pi1[suppr]=1&tx_icsdashboard_pi1[uid]='+moduleuid});
		jQuery('#ics'+dashboard+'_'+moduleuid).remove();
		jQuery(".modules div.col").removeClass('even');
		jQuery(".modules div.col:even").addClass('even');
		jQuery(".modules div.col").removeClass('even');
		jQuery(".modules div.col").filter(":odd").addClass('even');
		if(jQuery("#bureau .modules div.col").hasClass('last')){
			jQuery(".modules div.col").removeClass('last');
		}
	}
	else{
		jQuery('#ics'+dashboard+'_'+moduleuid).show('slow');
	}
}

function init_edit_tools(dashboard,ddsort,showTools) {
	//var cook = jQuery.cookie('ics_desktop_edit'+dashboard);
	/*if(cook){
		jQuery('#ics_dashboard_dashboard_'+dashboard).addClass('ics_dashboard_list');
		jQuery('#ics_dashboard_dashboard_'+dashboard+' div.col').each(function () {
			jQuery(this).addClass('ics_dashboard_edit');
		});
		jQuery('.ics_dashboard_add_edit_toollink_'+dashboard).each( function () {
			jQuery(this).text('OK');
		});	
		if (ddsort){
			/****Sort with Drag and Drop *************
			jQuery(".ics_dashboard_list").sortable({
			handle : '.ics_dashboard_handle',
			update : function () {
				var order = jQuery('.ics_dashboard_list').sortable('serialize');
				//jQuery("#info").load('index.php?eID=ics_dashboard&'+order+'&dashboard='+dashboard);
				jQuery.ajax({url: 'index.php?eID=ics_dashboard&'+order+'&dashboard='+dashboard});
				}
			});
		}
	}*/
	jQuery.cookie('ics_desktop_edit'+dashboard, null, {path: '/' });
}

function add_edit_tools(dashboard,ddsort,showTools) {
	var cook = jQuery.cookie('ics_desktop_edit'+dashboard);
	if(cook){
			jQuery('#ics_dashboard_dashboard_'+dashboard+' .tools').remove();
			jQuery('#ics_dashboard_dashboard_'+dashboard+' .ics_dashboard_add_tool').remove();
			jQuery('#ics_dashboard_dashboard_'+dashboard+' .ics_dashboard_reset_tool').remove();
			jQuery('.ics_dashboard_add_edit_toollink_'+dashboard).text(cook);
			jQuery.cookie('ics_desktop_edit'+dashboard, null,{path: '/' });
	}
	else{
		jQuery.cookie('ics_desktop_edit'+dashboard, null,{path: '/' });
		jQuery.cookie('ics_desktop_edit'+dashboard, jQuery('.ics_dashboard_add_edit_toollink_'+dashboard).text(), { path: '/' });
		jQuery('#ics_dashboard_dashboard_'+dashboard).addClass('ics_dashboard_list');
		jQuery('#ics_dashboard_dashboard_'+dashboard+' div.col').each(function () {
			jQuery(this).addClass('ics_dashboard_edit');
		});
		jQuery('.ics_dashboard_add_edit_toollink_'+dashboard).each( function () {
			jQuery(this).text('OK');
		});	
		if (ddsort){
			/****Sort with Drag and Drop ************/
			jQuery(".ics_dashboard_list").sortable({
			handle : '.ics_dashboard_handle',
			update : function () {
				var order = jQuery('.ics_dashboard_list').sortable('serialize');
				//jQuery("#info").load('index.php?eID=ics_dashboard&'+order+'&dashboard='+dashboard);
				jQuery.ajax({url: 'index.php?eID=ics_dashboard&'+order+'&dashboard='+dashboard});
				jQuery(".modules div.col").removeClass('even');
				jQuery(".modules div.col").filter(":odd").addClass('even');
					if(jQuery(this).children('div.col').hasClass('last')){
						jQuery(".modules div.col").removeClass('last');
						jQuery(".modules div.col").filter(":last").addClass('last');
					}
				}
			});
		}
	}
}
