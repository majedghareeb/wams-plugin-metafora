(()=>{"use strict";var t={n:e=>{var n=e&&e.__esModule?()=>e.default:()=>e;return t.d(n,{a:n}),n},d:(e,n)=>{for(var a in n)t.o(n,a)&&!t.o(e,a)&&Object.defineProperty(e,a,{enumerable:!0,get:n[a]})},o:(t,e)=>Object.prototype.hasOwnProperty.call(t,e)};const e=window.wp.element,n=window.wp.blocks,a=window.wp.serverSideRender;var o=t.n(a);const u=window.wp.blockEditor,r=window.wp.components;(0,n.registerBlockType)("um-block/um-account",{edit:function(t){let{tab:n,setAttributes:a}=t.attributes;const c=(0,u.useBlockProps)();return(0,e.createElement)("div",c,(0,e.createElement)(o(),{block:"um-block/um-account",attributes:t.attributes}),(0,e.createElement)(u.InspectorControls,null,(0,e.createElement)(r.PanelBody,{title:wp.i18n.__("Account Tab","wams")},(0,e.createElement)(r.SelectControl,{label:wp.i18n.__("Select Tab","wams"),className:"wams_select_account_tab",value:n,options:function(){var t=[];for(var e in t.push({label:wp.i18n.__("All","wams"),value:"all"}),wams_account_settings)wams_account_settings.hasOwnProperty(e)&&wams_account_settings[e].enabled&&t.push({label:wams_account_settings[e].label,value:e});return t}(),style:{height:"35px",lineHeight:"20px",padding:"0 7px"},onChange:e=>{t.setAttributes({tab:e}),function(e){var n="[wams_account";"all"!==e&&(n=n+' tab="'+e+'"'),n+="]",t.setAttributes({content:n})}(e)}}))))},save:function(t){return null}}),jQuery(window).on("load",(function(t){new MutationObserver((function(t){t.forEach((function(t){jQuery(t.addedNodes).find(".um.um-account").each((function(){var t=jQuery(this).find(".um-account-main").attr("data-current_tab");t&&(jQuery(this).find('.um-account-tab[data-tab="'+t+'"]').show(),jQuery(this).find(".um-account-tab:not(:visible)").find("input, select, textarea").not(":disabled").addClass("wams_account_inactive").prop("disabled",!0).attr("disabled",!0),wams_responsive(),wams_modal_responsive())}))}))})).observe(document,{attributes:!1,childList:!0,characterData:!1,subtree:!0})}))})();