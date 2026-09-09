
/*
	* KM-Tabs
	*
	* (c) 2019-2026 Kreatura Media, AgeraWeb, George K., John G.
	*
*/



jQuery(document).ready(function(a){a(document).on("click.km-tabs",".km-tabs-list > *:not(.kmw-disabled, .kmw-unselectable, kmw-menutitle)",function(){var e=a(this),t=e.parent(),m=e.closest(".kmw-sidebar-sub"),i=t.closest(".kmw-modal"),s=e.find("kmw-menutext").text(),d=a(t.data("target")),n=t.is("[data-disable-auto-rename]")||e.is("[data-disable-auto-rename]"),k=e.is("[data-rename-from-sub-sidebar]"),l='[data-kmw-uid="'+a(this).closest(".kmw-modal-container").data("kmwUid")+'"] ',w=l+"kmw-menuitem, "+l+".kmw-menuitem",o=l+".kmw-sidebar-sub kmw-navigation kmw-menuitem, "+l+".kmw-sidebar-sub kmw-navigation .kmw-menuitem",r=m.length?e.index(o):e.index(w),c=e.data("tab-target")||"",b=d.find('[data-tab="'+c+'"]');e.hasClass("kmw-active")||(e.siblings().removeClass("kmw-active"),e.addClass("kmw-active"),c&&b.length?(b.siblings().removeClass("kmw-active"),b.addClass("kmw-active")):(d.children().removeClass("kmw-active"),d.children().eq(r).addClass("kmw-active"))),k?(s=i.find(".kmw-sidebar-sub kmw-navigation .kmw-active kmw-menutext").text(),i.find("kmw-h1.kmw-modal-title").text(s)):n||i.find("kmw-h1.kmw-modal-title").text(s),void 0===t.data("disableDataNameChange")&&(e.is("[data-name]")?i.attr("data-selected",e.attr("data-name")):i.removeAttr("data-selected"))})});