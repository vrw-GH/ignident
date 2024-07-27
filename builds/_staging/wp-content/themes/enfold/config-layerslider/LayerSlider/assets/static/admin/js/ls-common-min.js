function isNumber(e){return"object"!=typeof e&&!isNaN(e)&&(!isNaN(parseFloat(e))&&isFinite(e))}function ucFirst(e){return e.charAt(0).toUpperCase()+e.slice(1)}Array.prototype.indexOf||(Array.prototype.indexOf=function(e){"use strict";if(null===this)throw new TypeError;var t=Object(this),n=t.length>>>0;if(0===n)return-1;var o=0;if(arguments.length>1&&((o=Number(arguments[1]))!=o?o=0:0!=o&&o!=1/0&&o!=-1/0&&(o=(o>0||-1)*Math.floor(Math.abs(o)))),o>=n)return-1;for(var i=o>=0?o:Math.max(n-Math.abs(o),0);i<n;i++)if(i in t&&t[i]===e)return i;return-1}),Array.prototype.fill=function(e,t){for(;t--;)this[t]=e;return this},Storage.prototype.setObject=function(e,t){this.setItem(e,JSON.stringify(t))},Storage.prototype.getObject=function(e){var t=this.getItem(e);return t&&JSON.parse(t)},function($){$.fn.appendToWithIndex=function(e,t){return e instanceof jQuery||(e=$(e)),0==t?this.prependTo(e):this.insertAfter(e.children(":eq("+(t-1)+")")),this}}(jQuery);var LS_ContextMenu={open:function(e,t){t=t||{},e.preventDefault(),e.stopPropagation(),LS_ContextMenu.close(t);var n=e.pageY;ml=e.pageX;var o=jQuery(t.selector);o.length||(o=jQuery(jQuery(t.template).text()).prependTo("body")),t.width&&o.css("width",t.width);var i=o.width(),a=jQuery(window).width();a-(ml+i)>0?(o.removeClass("ls-context-left-subs"),o.css({top:n,left:ml+3})):(e.alignRight&&(n=e.alignRight.pageY||n,ml=e.alignRight.pageX||ml),o.addClass("ls-context-left-subs"),o.css({top:n,left:"auto",right:a-ml})),t.onBeforeOpen&&t.onBeforeOpen(o),o.addClass("ls-context-menu-opened"),t.onOpen&&t.onOpen(o),setTimeout((function(){jQuery("body").off("click.ls-context-menu").on("click.ls-context-menu",(function(){LS_ContextMenu.close(t)}))}),e.manualOpen?100:0)},close:function(e){e&&e.onClose&&e.onClose(),jQuery("body").off("click.ls-context-menu"),jQuery(".ls-context-menu").removeClass("ls-context-menu-opened")}},LS_CodeMirror={init:function(e){var t={mode:"css",theme:"solarized",lineNumbers:!0,lineWrapping:!0,autofocus:!0,indentUnit:4,indentWithTabs:!0,foldGutter:!0,gutters:["CodeMirror-linenumbers","CodeMirror-foldgutter"],styleActiveLine:!0,extraKeys:{"Ctrl-Q":function(e){e.foldCode(e.getCursor())}}};jQuery(".ls-codemirror").each((function(){var n=jQuery.extend(!0,{},t,e||{});jQuery(this).prop("readonly")&&(n.readOnly=!0,n.theme+=" readonly");var o=CodeMirror.fromTextArea(this,n);o.on("change",(function(e){e.save(),jQuery(e.getTextArea()).trigger("updated.ls",e)})),jQuery(this).closest("#lse-callback-events").length&&o.on("beforeChange",(function(e,t){0===t.from.line&&t.to.line===e.lastLine()?(e.setSelection({line:1,ch:0},{line:e.lastLine()-1,ch:99999}),e.replaceSelection(t.text[0]),t.cancel()):(0===t.from.line&&(t.from.line=1,"+delete"===t.origin&&t.cancel()),t.to.line===e.lastLine()&&(t.to.line=e.lastLine()-1,"+delete"===t.origin&&t.cancel())),jQuery(e.getTextArea()).trigger("updated.ls",e)}))}))}};jQuery((function($){"undefined"!=typeof LS_statusMessage&&kmUI.notify.show(LS_statusMessage),$(document).on("click",".ls-install-plugin-update",(function(e){if(e.preventDefault(),LS_slidersMeta.isActivatedSite)try{lsInstallPluginUpdate()}catch(e){$(this).attr("href")&&(window.location.href=$(this).attr("href"))}else lsDisplayActivationWindow()}));var e=0;$(document).on("click","#ls-plugin-update-success-modal .ls-checkmark-holder",(function(){var t=$(this),n=t.parent(),o=t.closest(".kmw-modal-container"),i=[-10,20,-30,15,-5,5],a=[1.6,1.3,1.1,1.8,1.6,1.4],s=i[e%i.length],l=a[e%a.length];t.css("transform","scale("+l+") rotate("+s+"deg)"),++e>12?(e=0,lsAddUpdateEasterEggExplosion(),setTimeout((function(){lsAddUpdateEasterEggExplosion()}),200),setTimeout((function(){lsAddUpdateEasterEggExplosion()}),400),setTimeout((function(){lsAddUpdateEasterEggExplosion()}),600),setTimeout((function(){lsAddUpdateEasterEggExplosion()}),800),setTimeout((function(){lsAddUpdateEasterEggExplosion()}),1e3),setTimeout((function(){lsAddUpdateEasterEggExplosion()}),1200),setTimeout((function(){lsAddUpdateEasterEggExplosion()}),1400),setTimeout((function(){lsAddUpdateEasterEggExplosion()}),1600),setTimeout((function(){kmw.modal.close(),lsDisplayUpdateEasterEgg()}),1600)):e>6?o.css("animation","ls-shake 500ms infinite"):e>4?n.addClass("ls-animate").css("animation-iteration-count","infinite"):e>2&&(n.removeClass("ls-animate"),setTimeout((function(){n.addClass("ls-animate")}),100))})),$(".ls-pagination-limit select").on("change",(function(){var e={action:"ls_save_pagination_limit",limit:jQuery(this).val()};$.post(ajaxurl,e,(function(){document.location.href=LS_l10n.adminURL+"?page=layerslider"}))}));var t={init:function(){$(document).on("click","[data-ls-su]",(function(){t.open($(this))}))},create:function(e){"static"==e.parent().css("position")&&e.parent().css("position","relative"),"static"==e.css("position")&&e.css("position","relative");var t=$("<div>"),n=$("<div>"),o=$("<div>");t.addClass("ls-su"),n.addClass("ls-su-inner"),o.addClass("ls-su-content"),e.parent().prepend(t.append(n.append(o)));var a=["borderRightStyle","borderRightWidth","borderRightColor","borderLeftStyle","borderLeftWidth","borderLeftColor","borderBottomStyle","borderBottomWidth","borderBottomColor","backgroundColor"];for(i=0;i<a.length;i++)n.css(a[i],e.css(a[i]));o.css({paddingTop:e.css("paddingLeft"),paddingLeft:e.css("paddingLeft"),paddingBottom:e.css("paddingLeft"),paddingRight:e.css("paddingRight")}),t.css({left:e.position().left+parseInt(e.css("marginLeft")),top:e.position().top+parseInt(e.css("marginTop"))+e.outerHeight(),width:e.width()+parseInt(e.css("paddingLeft"))+parseInt(e.css("paddingRight"))+parseInt(e.css("borderLeftWidth"))+parseInt(e.css("borderRightWidth"))}),o.append(e.siblings(".ls-su-data").html())},open:function(e){e.parent().find(".ls-su").length||t.create(e),$su=e.parent().find(".ls-su"),$sui=e.parent().find(".ls-su-inner"),$su.hasClass("ls-su-opened")||($su.addClass("ls-su-opened"),TweenLite.set($su.parent()[0],{z:100}),TweenLite.set($su[0],{opacity:.7,height:"auto",transformOrigin:"center top",rotationX:90,transformPerspective:500}),TweenLite.set($sui[0],{top:0}),TweenLite.to($su[0],2,{opacity:1,rotationX:0,ease:"Elastic.easeOut"}),$(document).one("click",(function(e){t.close($su,$sui)})))},close:function(e,t){TweenLite.to(t[0],.3,{top:-t.outerHeight(),ease:"Quart.easeIn"}),TweenLite.to(e[0],.3,{opacity:.7,height:0,ease:"Quart.easeIn",onComplete:function(){e.removeClass("ls-su-opened")}})}};t.init(),window.kmUI&&kmUI.popover.init(),-1===document.location.href.indexOf("&action=edit")&&LS_CodeMirror.init(),-1!==document.location.href.indexOf("section=skin-editor")&&$('select[name="skin"]').change((function(){document.location.href=LS_l10n.adminURL+"?page=layerslider&section=skin-editor&skin="+$(this).children(":selected").val()})),-1!==navigator.platform.indexOf("Mac")&&$("body").addClass("ls-platform-mac")}));var lsDisplayActivationWindow=function(e){var t={into:"body",title:LS_l10n.activationFeature,content:"#tmpl-activation",minHeight:740,maxHeight:740};e=jQuery.extend(!0,t,e),kmw.modal.open({uid:"activation-window",into:e.into,title:e.title,content:e.content,minWidth:880,maxWidth:880,minHeight:e.minHeight,maxHeight:e.maxHeight,zIndex:9999999,modalClasses:"lse-activation-modal-window",overlaySettings:{animationIn:"fade",zIndex:9999999},onOpen:function(e){jQuery(e.element).addClass("kmw-modal-visible")}})},lsDisplayUpdateEasterEgg=function(){for(var e=0;e<150;e++)setTimeout((function(e){lsAddUpdateEasterEggIcon(e)}),600*e,e);kmw.modal.open({maxWidth:500,zIndex:100200,closeButton:!1,closeOnEscape:!1,content:"#tmpl-plugin-update-easter-egg-modal",animationIn:"scale",overlaySettings:{closeOnClick:!1,animationIn:"fade",zIndex:100100}})},lsInstallPluginUpdate=function(){wp.updates.maybeRequestFilesystemCredentials(),kmw.modal.open({content:"#tmpl-plugin-update-loading-modal",minWidth:300,maxWidth:300,closeButton:!1,closeOnEscape:!1,animationIn:"scale",overlaySettings:{closeOnClick:!1,animationIn:"fade"}}),wp.updates.ajax("update-plugin",{plugin:LS_ENV.base,slug:LS_ENV.slug,success:function(){kmw.modal.close(),kmw.modal.open({maxWidth:400,zIndex:100200,closeButton:!1,closeOnEscape:!1,content:"#tmpl-plugin-update-success-modal",animationIn:"scale",overlaySettings:{closeOnClick:!1,animationIn:"fade",zIndex:100100}})},error:function(){kmw.modal.close(),kmw.modal.open({maxWidth:500,closeButton:!1,closeOnEscape:!1,content:"#tmpl-plugin-update-error-modal",animationIn:"scale",overlaySettings:{closeOnClick:!1,animationIn:"fade"}})}})},lsAddUpdateEasterEggExplosion=function(){var e=jQuery(window).width()/1.5,t=jQuery(window).height()/1.5;["check","cat","heart","rocket","hourglass","ufo","station","alien","galaxy"].forEach((function(n){var o=jQuery(LS_InterfaceIcons.easteregg[n]).addClass("ls-update-easter-egg-explosion-icon").appendTo("body");setTimeout((function(){var n=Math.floor(90*Math.random())+1;n*=1==Math.floor(2*Math.random())?1:-1;var i=Math.floor(Math.random()*e)+1;i*=1==Math.floor(2*Math.random())?1:-1;var a=Math.floor(Math.random()*t)+1;a*=1==Math.floor(2*Math.random())?1:-1;var s=600+Math.floor(400*Math.random());o.css({transition:"transform "+s+"ms, opacity 200ms",transform:"translate("+i+"px, "+a+"px) rotate("+n+"deg)"}),setTimeout((function(){o.css("opacity",0),setTimeout((function(){o.remove()}),200)}),s+100)}),100)}))},lsAddUpdateEasterEggIcon=function(e){var t=["check","cat","heart","rocket","hourglass","ufo","station","alien","galaxy"],n=t[e%t.length],o=jQuery(LS_InterfaceIcons.easteregg[n]).addClass("ls-update-easter-egg-icon").appendTo("body"),i=Math.floor(45*Math.random())+1;i*=1==Math.floor(2*Math.random())?1:-1;var a=Math.floor(360*Math.random())+1;a*=1==Math.floor(2*Math.random())?1:-1;var s=jQuery(document).height(),l=jQuery(window).width(),r=((-19.9*Math.random()+20).toFixed(3),(-.9*Math.random()+1.8).toFixed(3)),c=Math.floor(Math.random()*l)+1;TweenLite.set(o[0],{y:-120,x:c,scale:r,rotation:i}),TweenLite.to(o[0],30,{y:s,rotation:a})},lsCommon={};jQuery(document).ready((function($){lsCommon={init:function(){lsCommon.smartAlert.init()},smartAlert:{nameSpace:"smartAlert",defaults:{buttons:{alert:{ok:{label:LS_l10n.ok,enter:!0,esc:!0}},confirm:{ok:{label:LS_l10n.ok,enter:!0},cancel:{label:LS_l10n.cancel,esc:!0}},triple:{ok:{label:LS_l10n.ok,enter:!0},other:{label:""},cancel:{label:LS_l10n.cancel,esc:!0}}},type:"alert"},init:function(){let e=this;e.$alert=$("lse-smart-alert").on("click."+e.nameSpace,(function(e){e.stopPropagation()})).prependTo("body"),e.$overlay=$("lse-smart-alert-overlay").on("click."+e.nameSpace,(function(t){t.preventDefault(),t.stopPropagation(),e.getAttention()})).prependTo("body")},open:function(e={}){this.opened&&this.close(),this.opened=!0,window.LS_preventKeyboardShortcuts=!0;let t=this;"string"==typeof e&&(e={text:e}),$(document).on("keydown."+t.nameSpace,(function(n){if(-1!==[13,27].indexOf(n.which)&&n.preventDefault(),e.disableKeys)switch(n.which){case 13:case 27:t.getAttention()}else switch(n.which){case 13:t.$alert.find('[data-smart-alert-action*="enter"]').click();break;case 27:t.$alert.find('[data-smart-alert-action*="esc"]').click()}})),e.theme&&t.$alert.addClass("lse-"+e.theme),e.title&&(t.$title=$("<lse-smart-alert-title>").text(e.title).prependTo(t.$alert)),e.width&&t.$alert.css("width",e.width),e.text&&(t.$text=$("<lse-smart-alert-text>").html(e.text).appendTo(t.$alert)),e.textAlign&&t.$text.css("text-align",e.textAlign),e.type||(e.type=t.defaults.type);let n=t.defaults.buttons[e.type];e.buttons&&(n=$.extend(!0,{},n,e.buttons)),t.$buttons=$("<lse-button-group>").appendTo(t.$alert);for(let i in n){let a=n[i],s=$("<lse-button>").attr("data-smart-alert-button-type",i).on("click."+t.nameSpace,(function(n){e.clickedButton=i,t.close(e)})).appendTo(t.$buttons);var o=[];for(let e in a)switch(e){case"label":s.text(a[e]);break;case"enter":o.push("enter");break;case"esc":o.push("esc");break;case"callback":s.on("click.custom"+t.nameSpace,(function(){a[e]()}))}s.attr("data-smart-alert-action",o.join(" "))}this.$alert.addClass("lse-visible"),this.$overlay.addClass("lse-visible"),this.options=e},confirm:function(e,t,n){2===arguments.length&&"function"==typeof t&&(n=t,t=e,e=""),this.open({title:e,text:t,type:"confirm",onConfirm:n})},close:function(e){e=e||this.options,this.$alert.removeAttr("class style").html(""),this.$overlay.removeClass("lse-visible"),$(document).off("keydown."+this.nameSpace),window.LS_preventKeyboardShortcuts=!1,e.onClose&&e.onClose(e.clickedButton),e.onConfirm&&"ok"===e.clickedButton&&e.onConfirm(),delete this.opened,delete this.options},getAttention:function(){let e=this;e.$alert.addClass("lse-get-attention"),setTimeout((function(){e.$alert.removeClass("lse-get-attention")}),400)}}},lsCommon.init(),jQuery('#lse-revisions-settings :input[name="enabled"]').on("change",(function(){const e=jQuery(this).prop("checked");window.LS_editorMeta&&(LS_editorMeta.revisionsEnabled=e),e||promptToDeleteRevisions()}))}));var promptToDeleteRevisions=function(){lsCommon.smartAlert.open({type:"confirm",width:600,theme:"red",title:LS_l10n.SBRevisionsDeleteTitle,text:LS_l10n.SBRevisionsDeleteMessage,buttons:{ok:{label:LS_l10n.SBRevisionsDeleteButton}},onConfirm:()=>{const e={};jQuery("#lse-revisions-settings :input").each((function(){e[jQuery(this).attr("name")]=jQuery(this).val()})),e.action="ls_delete_revisions",e["delete-all"]=1,jQuery.post(ajaxurl,e,(()=>{}))}})};