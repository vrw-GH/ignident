!function(){"use strict";function t(t,e,n,o,r){return{sel:t,data:e,children:n,text:o,elm:r,key:void 0===e?void 0:e.key}}const e=Array.isArray;function n(t){return"string"==typeof t||"number"==typeof t||t instanceof String||t instanceof Number}function o(t){if(r(t)){for(;t&&r(t);)t=i(t).parent;return null!=t?t:null}return t.parentNode}function r(t){return 11===t.nodeType}function i(t,e){var n,o,r;const i=t;return null!==(n=i.parent)&&void 0!==n||(i.parent=null!=e?e:null),null!==(o=i.firstChildNode)&&void 0!==o||(i.firstChildNode=t.firstChild),null!==(r=i.lastChildNode)&&void 0!==r||(i.lastChildNode=t.lastChild),i}const l={createElement:function(t,e){return document.createElement(t,e)},createElementNS:function(t,e,n){return document.createElementNS(t,e,n)},createTextNode:function(t){return document.createTextNode(t)},createDocumentFragment:function(){return i(document.createDocumentFragment())},createComment:function(t){return document.createComment(t)},insertBefore:function(t,e,n){if(r(t)){let e=t;for(;e&&r(e);)e=i(e).parent;t=null!=e?e:t}r(e)&&(e=i(e,t)),n&&r(n)&&(n=i(n).firstChildNode),t.insertBefore(e,n)},removeChild:function(t,e){t.removeChild(e)},appendChild:function(t,e){r(e)&&(e=i(e,t)),t.appendChild(e)},parentNode:o,nextSibling:function(t){var e;if(r(t)){const n=i(t),r=o(n);if(r&&n.lastChildNode){const t=Array.from(r.childNodes),o=t.indexOf(n.lastChildNode);return null!==(e=t[o+1])&&void 0!==e?e:null}return null}return t.nextSibling},tagName:function(t){return t.tagName},setTextContent:function(t,e){t.textContent=e},getTextContent:function(t){return t.textContent},isElement:function(t){return 1===t.nodeType},isText:function(t){return 3===t.nodeType},isComment:function(t){return 8===t.nodeType},isDocumentFragment:r};function a(t){return void 0===t}function s(t){return void 0!==t}const c=t("",{},[],void 0,void 0);function d(t,e){var n,o;const r=t.key===e.key,i=(null===(n=t.data)||void 0===n?void 0:n.is)===(null===(o=e.data)||void 0===o?void 0:o.is),l=t.sel===e.sel,a=!(!t.sel&&t.sel===e.sel)||typeof t.text==typeof e.text;return l&&r&&i&&a}function u(){throw new Error("The document fragment is not supported on this platform.")}function f(t,e,n){var o;const r={};for(let i=e;i<=n;++i){const e=null===(o=t[i])||void 0===o?void 0:o.key;void 0!==e&&(r[e]=i)}return r}const h=["create","update","remove","destroy","pre","post"];function v(t,e,n){if("function"==typeof t)t.call(e,n,e);else if("object"==typeof t)for(let o=0;o<t.length;o++)v(t[o],e,n)}function m(t,e){const n=t.type,o=e.data.on;o&&o[n]&&v(o[n],e,t)}function g(t,e){const n=t.data.on,o=t.listener,r=t.elm,i=e&&e.data.on,l=e&&e.elm;let a;if(n!==i){if(n&&o)if(i)for(a in n)i[a]||r.removeEventListener(a,o,!1);else for(a in n)r.removeEventListener(a,o,!1);if(i){const o=e.listener=t.listener||function t(e){m(e,t.vnode)};if(o.vnode=e,n)for(a in i)n[a]||l.addEventListener(a,o,!1);else for(a in i)l.addEventListener(a,o,!1)}}}const p={create:g,update:g,destroy:g};function y(t,e){let n;const o=e.elm;let r=t.data.attrs,i=e.data.attrs;if((r||i)&&r!==i){for(n in r=r||{},i=i||{},i){const t=i[n];r[n]!==t&&(!0===t?o.setAttribute(n,""):!1===t?o.removeAttribute(n):120!==n.charCodeAt(0)?o.setAttribute(n,t):58===n.charCodeAt(3)?o.setAttributeNS("http://www.w3.org/XML/1998/namespace",n,t):58===n.charCodeAt(5)?109===n.charCodeAt(1)?o.setAttributeNS("http://www.w3.org/2000/xmlns/",n,t):o.setAttributeNS("http://www.w3.org/1999/xlink",n,t):o.setAttribute(n,t))}for(n in r)n in i||o.removeAttribute(n)}}const x={create:y,update:y};function w(t,e,n){if(t.ns="http://www.w3.org/2000/svg","foreignObject"!==n&&void 0!==e)for(let t=0;t<e.length;++t){const n=e[t];if("string"==typeof n)continue;const o=n.data;void 0!==o&&w(o,n.children,n.sel)}}function k(o,r,i){let l,a,s,c={};if(void 0!==i?(null!==r&&(c=r),e(i)?l=i:n(i)?a=i.toString():i&&i.sel&&(l=[i])):null!=r&&(e(r)?l=r:n(r)?a=r.toString():r&&r.sel?l=[r]:c=r),void 0!==l)for(s=0;s<l.length;++s)n(l[s])&&(l[s]=t(void 0,void 0,void 0,l[s],void 0));return!o.startsWith("svg")||3!==o.length&&"."!==o[3]&&"#"!==o[3]||w(c,l,o),t(o,c,l,a,void 0)}const{nonce:C,root:N}=window.koko_analytics;function b(t,e={}){let n=N+"koko-analytics/v1"+t;return n=n+(n.indexOf("?")>-1?"&":"?")+new URLSearchParams(e),fetch(n,{headers:{"X-WP-Nonce":C,Accepts:"application/json"},credentials:"same-origin"}).then((t=>{if(t.status>=400)throw console.error("Koko Analytics encountered an error trying to request data from the REST endpoints. Please check your PHP error logs for the error that occurred."),new Error(t.statusText);return t})).then((t=>t.json()))}window.setTimeout((()=>{window.location.reload()}),432e5);const S=1e6,T=1e3,A=/\.0+$/;function E(t){let e=0;return t>=S?(t/=S,e=Math.max(0,3-String(Math.round(t)).length),t.toFixed(e).replace(A,"")+"M"):t>=10*T?(t/=T,e=Math.max(0,3-String(Math.round(t)).length),t.toFixed(e).replace(A,"")+"K"):String(t)}function M(t){if(null===t)return null;const e=t.split("-");if(2===e.length&&e.push("1"),3!==e.length)return null;let[n,o,r]=e.map((t=>parseInt(t,10)));return n<1e3&&(n+=2e3),n<2e3||n>3e3||o<1||o>12||r<1||r>31?null:new Date(n,o-1,r)}function B(t,e){t="string"==typeof t?M(t):t,e=e||{day:"numeric",month:"short",year:"numeric"};try{return new Intl.DateTimeFormat(void 0,e).format(t)}catch(t){}return t.toLocaleDateString()}const{i18n:D}=window.koko_analytics,L=function(o,r,i){const v={create:[],update:[],remove:[],destroy:[],pre:[],post:[]},m=l;for(const t of h)for(const e of o){const n=e[t];void 0!==n&&v[t].push(n)}function g(t,e){return function(){if(0==--e){const e=m.parentNode(t);null!==e&&m.removeChild(e,t)}}}function p(t,o){var r,l,d,f;let h,g=t.data;if(void 0!==g){const e=null===(r=g.hook)||void 0===r?void 0:r.init;s(e)&&(e(t),g=t.data)}const y=t.children,x=t.sel;if("!"===x)a(t.text)&&(t.text=""),t.elm=m.createComment(t.text);else if(""===x)t.elm=m.createTextNode(t.text);else if(void 0!==x){const r=x.indexOf("#"),i=x.indexOf(".",r),a=r>0?r:x.length,d=i>0?i:x.length,u=-1!==r||-1!==i?x.slice(0,Math.min(a,d)):x,f=t.elm=s(g)&&s(h=g.ns)?m.createElementNS(h,u,g):m.createElement(u,g);for(a<d&&f.setAttribute("id",x.slice(a+1,d)),i>0&&f.setAttribute("class",x.slice(d+1).replace(/\./g," ")),h=0;h<v.create.length;++h)v.create[h](c,t);if(!n(t.text)||e(y)&&0!==y.length||m.appendChild(f,m.createTextNode(t.text)),e(y))for(h=0;h<y.length;++h){const t=y[h];null!=t&&m.appendChild(f,p(t,o))}const w=t.data.hook;s(w)&&(null===(l=w.create)||void 0===l||l.call(w,c,t),w.insert&&o.push(t))}else if((null===(d=null==i?void 0:i.experimental)||void 0===d?void 0:d.fragments)&&t.children){for(t.elm=(null!==(f=m.createDocumentFragment)&&void 0!==f?f:u)(),h=0;h<v.create.length;++h)v.create[h](c,t);for(h=0;h<t.children.length;++h){const e=t.children[h];null!=e&&m.appendChild(t.elm,p(e,o))}}else t.elm=m.createTextNode(t.text);return t.elm}function y(t,e,n,o,r,i){for(;o<=r;++o){const r=n[o];null!=r&&m.insertBefore(t,p(r,i),e)}}function x(t){var e,n;const o=t.data;if(void 0!==o){null===(n=null===(e=null==o?void 0:o.hook)||void 0===e?void 0:e.destroy)||void 0===n||n.call(e,t);for(let e=0;e<v.destroy.length;++e)v.destroy[e](t);if(void 0!==t.children)for(let e=0;e<t.children.length;++e){const n=t.children[e];null!=n&&"string"!=typeof n&&x(n)}}}function w(t,e,n,o){for(var r,i;n<=o;++n){let o,l;const a=e[n];if(null!=a)if(s(a.sel)){x(a),o=v.remove.length+1,l=g(a.elm,o);for(let t=0;t<v.remove.length;++t)v.remove[t](a,l);const t=null===(i=null===(r=null==a?void 0:a.data)||void 0===r?void 0:r.hook)||void 0===i?void 0:i.remove;s(t)?t(a,l):l()}else a.children?(x(a),w(t,a.children,0,a.children.length-1)):m.removeChild(t,a.elm)}}function k(t,e,n){var o,r,i,l,c,u,h,g;const x=null===(o=e.data)||void 0===o?void 0:o.hook;null===(r=null==x?void 0:x.prepatch)||void 0===r||r.call(x,t,e);const C=e.elm=t.elm;if(t===e)return;if(void 0!==e.data||s(e.text)&&e.text!==t.text){null!==(i=e.data)&&void 0!==i||(e.data={}),null!==(l=t.data)&&void 0!==l||(t.data={});for(let n=0;n<v.update.length;++n)v.update[n](t,e);null===(h=null===(u=null===(c=e.data)||void 0===c?void 0:c.hook)||void 0===u?void 0:u.update)||void 0===h||h.call(u,t,e)}const N=t.children,b=e.children;a(e.text)?s(N)&&s(b)?N!==b&&function(t,e,n,o){let r,i,l,s,c=0,u=0,h=e.length-1,v=e[0],g=e[h],x=n.length-1,C=n[0],N=n[x];for(;c<=h&&u<=x;)null==v?v=e[++c]:null==g?g=e[--h]:null==C?C=n[++u]:null==N?N=n[--x]:d(v,C)?(k(v,C,o),v=e[++c],C=n[++u]):d(g,N)?(k(g,N,o),g=e[--h],N=n[--x]):d(v,N)?(k(v,N,o),m.insertBefore(t,v.elm,m.nextSibling(g.elm)),v=e[++c],N=n[--x]):d(g,C)?(k(g,C,o),m.insertBefore(t,g.elm,v.elm),g=e[--h],C=n[++u]):(void 0===r&&(r=f(e,c,h)),i=r[C.key],a(i)?(m.insertBefore(t,p(C,o),v.elm),C=n[++u]):a(r[N.key])?(m.insertBefore(t,p(N,o),m.nextSibling(g.elm)),N=n[--x]):(l=e[i],l.sel!==C.sel?m.insertBefore(t,p(C,o),v.elm):(k(l,C,o),e[i]=void 0,m.insertBefore(t,l.elm,v.elm)),C=n[++u]));u<=x&&(s=null==n[x+1]?null:n[x+1].elm,y(t,s,n,u,x,o)),c<=h&&w(t,e,c,h)}(C,N,b,n):s(b)?(s(t.text)&&m.setTextContent(C,""),y(C,null,b,0,b.length-1,n)):s(N)?w(C,N,0,N.length-1):s(t.text)&&m.setTextContent(C,""):t.text!==e.text&&(s(N)&&w(C,N,0,N.length-1),m.setTextContent(C,e.text)),null===(g=null==x?void 0:x.postpatch)||void 0===g||g.call(x,t,e)}return function(e,n){let o,r,i;const l=[];for(o=0;o<v.pre.length;++o)v.pre[o]();for(function(t,e){return t.isElement(e)}(m,e)?e=function(e){const n=e.id?"#"+e.id:"",o=e.getAttribute("class"),r=o?"."+o.split(" ").join("."):"";return t(m.tagName(e).toLowerCase()+n+r,{},[],void 0,e)}(e):function(t,e){return t.isDocumentFragment(e)}(m,e)&&(e=t(void 0,{},[],void 0,e)),d(e,n)?k(e,n,l):(r=e.elm,i=m.parentNode(r),p(n,l),null!==i&&(m.insertBefore(i,n.elm,m.nextSibling(r)),w(i,[e],0,0))),o=0;o<l.length;++o)l[o].data.hook.insert(l[o]);for(o=0;o<v.post.length;++o)v.post[o]();return n}}([p,x]),F=function(){const t=document.createElement("div");return t.style.display="none",t.className="ka-chart--tooltip",t.innerHTML=`\n<div class="ka-chart--tooltip-box">\n  <div class="ka-chart--tooltip-heading"></div>\n  <div style="display: flex">\n    <div class="ka-chart--tooltip-content ka--visitors">\n      <div class="ka-chart--tooltip-amount"></div>\n      <div>${D.Visitors}</div>\n    </div>\n    <div class="ka-chart--tooltip-content ka--pageviews">\n      <div class="ka-chart--tooltip-amount"></div>\n      <div>${D.Pageviews}</div>\n    </div>\n  </div>\n</div>\n<div class="ka-chart--tooltip-arrow"></div>`,t}();function j(){F.style.display="none"}const $=document.getElementById("koko-analytics-dashboard-widget-mount"),{data:P,startDate:q,endDate:O}=window.koko_analytics;function W(){$.clientWidth&&function(t,e,n,o,r,i){i||(i=280);const l=t.clientWidth;t.parentElement.style.minHeight=`${i+4}px`;let a=o-n>=314496e5?{month:"short",year:"numeric"}:void 0;function s(e,n,o){const r=M(n)-M(e)>=314496e5;a=r?{month:"short",year:"numeric"}:void 0,b("/stats",{start_date:e,end_date:n,monthly:r?1:0,page:o>0?o:0}).then((e=>{t=L(t,d(e))}))}function c(t){return Math.round(100*t)/100}function d(t){if(t.length<=1)return k("!");const e=function(t){let e,n=0;for(let o=0;o<t.length;o++)e=t[o].pageviews,e>n&&(n=e);return n}(t),n=function(t){if(t<10)return 10;if(t>1e5)return 1e4*Math.ceil(t/1e4);const e=Math.floor(Math.log10(t)),n=Math.pow(10,e);return Math.ceil(t/n)*n}(e),o=[0,n/2,n],r=t.length<=90,s=4+8*Math.max(5,String(E(n)).length),d=l-s,u=i-24-6,f=u/n,h=c(d/t.length),v=7*t.length<d?2:0,m=h-2*v,g=t=>c(t*h),p=n<=0?()=>u:t=>u-t*f;return k("svg",{attrs:{width:"100%",height:i}},[k("g",[k("g",{attrs:{transform:"translate(0, 6)","text-anchor":"end"}},o.map((t=>{const e=p(t);return k("g",[k("line",{attrs:{stroke:"#eee",x1:s,x2:d+s,y1:e,y2:e}}),k("text",{attrs:{y:e,fill:"#757575",x:c(.9*s-4),dy:"0.33em"}},E(t))])}))),k("g",{attrs:{class:"axes-x","text-anchor":"start",transform:`translate(${s}, ${6+u})`}},t.map(((e,n)=>{let o=0===n||n===t.length-1?e.date:null;if(!r&&!o)return null;const i=g(n)+.5*h;return k("g",[k("line",{attrs:{stroke:"#ddd",x1:i,x2:i,y1:0,y2:6}}),o?k("text",{attrs:{fill:"#757575",x:i,y:10,dy:"1em","text-anchor":0===n?"start":"end"}},B(e.date,a)):""])})).filter((t=>null!==t)))]),k("g",{attrs:{class:"bars",transform:`translate(${s}, 6)`}},t.map(((t,e)=>{const n=t.pageviews*f,o=t.visitors*f,r=g(e),i=function(t,e){return n=>{F.querySelector(".ka-chart--tooltip-heading").textContent=B(t.date,a),F.querySelector(".ka--visitors").children[0].textContent=t.visitors,F.querySelector(".ka--pageviews").children[0].textContent=t.pageviews,F.style.display="block";const o=n.currentTarget.getBoundingClientRect(),r=o.left+window.scrollX-.5*F.clientWidth+.5*e+"px",i=o.y+window.scrollY-F.clientHeight+"px";F.style.left=r,F.style.top=i}}(t,m);return k("g",{on:{click:i,mouseenter:i,mouseleave:j}},[k("rect",{attrs:{class:"ka--pageviews",height:n,width:m,x:r+v,y:p(t.pageviews)}}),k("rect",{attrs:{class:"ka--visitors",height:o,width:m,x:r+v,y:p(t.visitors)}})])})))])}e.length?t=L(t,d(e)):s(n,o,r),document.body.appendChild(F),addEventListener("click",(e=>{for(let n=e.target;null!==n;n=n.parentElement)if(n===t.elm)return;j()}))}($,P.chart,q,O,0,200)}$.parentElement.style.display="",requestAnimationFrame(W),jQuery&&jQuery(document).on("postbox-toggled",W)}();