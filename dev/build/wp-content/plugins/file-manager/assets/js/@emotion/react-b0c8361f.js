import{g as Se,a as Ge,r as O,R as fe}from"../react-vendor-de3885fb.js";function qe(e,r){for(var t=0;t<r.length;t++){const n=r[t];if(typeof n!="string"&&!Array.isArray(n)){for(const a in n)if(a!=="default"&&!(a in e)){const s=Object.getOwnPropertyDescriptor(n,a);s&&Object.defineProperty(e,a,s.get?s:{enumerable:!0,get:()=>n[a]})}}}return Object.freeze(Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}))}function Ve(e){if(e.sheet)return e.sheet;for(var r=0;r<document.styleSheets.length;r++)if(document.styleSheets[r].ownerNode===e)return document.styleSheets[r]}function Ke(e){var r=document.createElement("style");return r.setAttribute("data-emotion",e.key),e.nonce!==void 0&&r.setAttribute("nonce",e.nonce),r.appendChild(document.createTextNode("")),r.setAttribute("data-s",""),r}var Ye=function(){function e(t){var n=this;this._insertTag=function(a){var s;n.tags.length===0?n.insertionPoint?s=n.insertionPoint.nextSibling:n.prepend?s=n.container.firstChild:s=n.before:s=n.tags[n.tags.length-1].nextSibling,n.container.insertBefore(a,s),n.tags.push(a)},this.isSpeedy=t.speedy===void 0?!0:t.speedy,this.tags=[],this.ctr=0,this.nonce=t.nonce,this.key=t.key,this.container=t.container,this.prepend=t.prepend,this.insertionPoint=t.insertionPoint,this.before=null}var r=e.prototype;return r.hydrate=function(n){n.forEach(this._insertTag)},r.insert=function(n){this.ctr%(this.isSpeedy?65e3:1)===0&&this._insertTag(Ke(this));var a=this.tags[this.tags.length-1];if(this.isSpeedy){var s=Ve(a);try{s.insertRule(n,s.cssRules.length)}catch{}}else a.appendChild(document.createTextNode(n));this.ctr++},r.flush=function(){this.tags.forEach(function(n){return n.parentNode&&n.parentNode.removeChild(n)}),this.tags=[],this.ctr=0},e}(),C="-ms-",H="-moz-",u="-webkit-",$e="comm",ae="rule",se="decl",Be="@import",Ee="@keyframes",He="@layer",Ue=Math.abs,Z=String.fromCharCode,Ze=Object.assign;function Je(e,r){return $(e,0)^45?(((r<<2^$(e,0))<<2^$(e,1))<<2^$(e,2))<<2^$(e,3):0}function Ce(e){return e.trim()}function Qe(e,r){return(e=r.exec(e))?e[0]:e}function d(e,r,t){return e.replace(r,t)}function ee(e,r){return e.indexOf(r)}function $(e,r){return e.charCodeAt(r)|0}function L(e,r,t){return e.slice(r,t)}function _(e){return e.length}function ie(e){return e.length}function V(e,r){return r.push(e),e}function Xe(e,r){return e.map(r).join("")}var J=1,F=1,Pe=0,T=0,x=0,z="";function Q(e,r,t,n,a,s,c){return{value:e,root:r,parent:t,type:n,props:a,children:s,line:J,column:F,length:c,return:""}}function W(e,r){return Ze(Q("",null,null,"",null,null,0),e,{length:-e.length},r)}function er(){return x}function rr(){return x=T>0?$(z,--T):0,F--,x===10&&(F=1,J--),x}function R(){return x=T<Pe?$(z,T++):0,F++,x===10&&(F=1,J++),x}function M(){return $(z,T)}function K(){return T}function q(e,r){return L(z,e,r)}function D(e){switch(e){case 0:case 9:case 10:case 13:case 32:return 5;case 33:case 43:case 44:case 47:case 62:case 64:case 126:case 59:case 123:case 125:return 4;case 58:return 3;case 34:case 39:case 40:case 91:return 2;case 41:case 93:return 1}return 0}function Oe(e){return J=F=1,Pe=_(z=e),T=0,[]}function Te(e){return z="",e}function Y(e){return Ce(q(T-1,re(e===91?e+2:e===40?e+1:e)))}function tr(e){for(;(x=M())&&x<33;)R();return D(e)>2||D(x)>3?"":" "}function nr(e,r){for(;--r&&R()&&!(x<48||x>102||x>57&&x<65||x>70&&x<97););return q(e,K()+(r<6&&M()==32&&R()==32))}function re(e){for(;R();)switch(x){case e:return T;case 34:case 39:e!==34&&e!==39&&re(x);break;case 40:e===41&&re(e);break;case 92:R();break}return T}function ar(e,r){for(;R()&&e+x!==47+10;)if(e+x===42+42&&M()===47)break;return"/*"+q(r,T-1)+"*"+Z(e===47?e:R())}function sr(e){for(;!D(M());)R();return q(e,T)}function ir(e){return Te(B("",null,null,null,[""],e=Oe(e),0,[0],e))}function B(e,r,t,n,a,s,c,o,f){for(var h=0,y=0,v=c,k=0,A=0,w=0,m=1,E=1,g=1,S=0,b="",I=a,i=s,P=n,p=b;E;)switch(w=S,S=R()){case 40:if(w!=108&&$(p,v-1)==58){ee(p+=d(Y(S),"&","&\f"),"&\f")!=-1&&(g=-1);break}case 34:case 39:case 91:p+=Y(S);break;case 9:case 10:case 13:case 32:p+=tr(w);break;case 92:p+=nr(K()-1,7);continue;case 47:switch(M()){case 42:case 47:V(cr(ar(R(),K()),r,t),f);break;default:p+="/"}break;case 123*m:o[h++]=_(p)*g;case 125*m:case 59:case 0:switch(S){case 0:case 125:E=0;case 59+y:g==-1&&(p=d(p,/\f/g,"")),A>0&&_(p)-v&&V(A>32?de(p+";",n,t,v-1):de(d(p," ","")+";",n,t,v-2),f);break;case 59:p+=";";default:if(V(P=ue(p,r,t,h,y,a,o,b,I=[],i=[],v),s),S===123)if(y===0)B(p,r,P,P,I,s,v,o,i);else switch(k===99&&$(p,3)===110?100:k){case 100:case 108:case 109:case 115:B(e,P,P,n&&V(ue(e,P,P,0,0,a,o,b,a,I=[],v),i),a,i,v,o,n?I:i);break;default:B(p,P,P,P,[""],i,0,o,i)}}h=y=A=0,m=g=1,b=p="",v=c;break;case 58:v=1+_(p),A=w;default:if(m<1){if(S==123)--m;else if(S==125&&m++==0&&rr()==125)continue}switch(p+=Z(S),S*m){case 38:g=y>0?1:(p+="\f",-1);break;case 44:o[h++]=(_(p)-1)*g,g=1;break;case 64:M()===45&&(p+=Y(R())),k=M(),y=v=_(b=p+=sr(K())),S++;break;case 45:w===45&&_(p)==2&&(m=0)}}return s}function ue(e,r,t,n,a,s,c,o,f,h,y){for(var v=a-1,k=a===0?s:[""],A=ie(k),w=0,m=0,E=0;w<n;++w)for(var g=0,S=L(e,v+1,v=Ue(m=c[w])),b=e;g<A;++g)(b=Ce(m>0?k[g]+" "+S:d(S,/&\f/g,k[g])))&&(f[E++]=b);return Q(e,r,t,a===0?ae:o,f,h,y)}function cr(e,r,t){return Q(e,r,t,$e,Z(er()),L(e,2,-2),0)}function de(e,r,t,n){return Q(e,r,t,se,L(e,0,n),L(e,n+1,-1),n)}function j(e,r){for(var t="",n=ie(e),a=0;a<n;a++)t+=r(e[a],a,e,r)||"";return t}function or(e,r,t,n){switch(e.type){case He:if(e.children.length)break;case Be:case se:return e.return=e.return||e.value;case $e:return"";case Ee:return e.return=e.value+"{"+j(e.children,n)+"}";case ae:e.value=e.props.join(",")}return _(t=j(e.children,n))?e.return=e.value+"{"+t+"}":""}function fr(e){var r=ie(e);return function(t,n,a,s){for(var c="",o=0;o<r;o++)c+=e[o](t,n,a,s)||"";return c}}function ur(e){return function(r){r.root||(r=r.return)&&e(r)}}var le=function(r){var t=new WeakMap;return function(n){if(t.has(n))return t.get(n);var a=r(n);return t.set(n,a),a}};function dr(e){var r=Object.create(null);return function(t){return r[t]===void 0&&(r[t]=e(t)),r[t]}}var lr=function(r,t,n){for(var a=0,s=0;a=s,s=M(),a===38&&s===12&&(t[n]=1),!D(s);)R();return q(r,T)},hr=function(r,t){var n=-1,a=44;do switch(D(a)){case 0:a===38&&M()===12&&(t[n]=1),r[n]+=lr(T-1,t,n);break;case 2:r[n]+=Y(a);break;case 4:if(a===44){r[++n]=M()===58?"&\f":"",t[n]=r[n].length;break}default:r[n]+=Z(a)}while(a=R());return r},pr=function(r,t){return Te(hr(Oe(r),t))},he=new WeakMap,yr=function(r){if(!(r.type!=="rule"||!r.parent||r.length<1)){for(var t=r.value,n=r.parent,a=r.column===n.column&&r.line===n.line;n.type!=="rule";)if(n=n.parent,!n)return;if(!(r.props.length===1&&t.charCodeAt(0)!==58&&!he.get(n))&&!a){he.set(r,!0);for(var s=[],c=pr(t,s),o=n.props,f=0,h=0;f<c.length;f++)for(var y=0;y<o.length;y++,h++)r.props[h]=s[f]?c[f].replace(/&\f/g,o[y]):o[y]+" "+c[f]}}},mr=function(r){if(r.type==="decl"){var t=r.value;t.charCodeAt(0)===108&&t.charCodeAt(2)===98&&(r.return="",r.value="")}};function Ae(e,r){switch(Je(e,r)){case 5103:return u+"print-"+e+e;case 5737:case 4201:case 3177:case 3433:case 1641:case 4457:case 2921:case 5572:case 6356:case 5844:case 3191:case 6645:case 3005:case 6391:case 5879:case 5623:case 6135:case 4599:case 4855:case 4215:case 6389:case 5109:case 5365:case 5621:case 3829:return u+e+e;case 5349:case 4246:case 4810:case 6968:case 2756:return u+e+H+e+C+e+e;case 6828:case 4268:return u+e+C+e+e;case 6165:return u+e+C+"flex-"+e+e;case 5187:return u+e+d(e,/(\w+).+(:[^]+)/,u+"box-$1$2"+C+"flex-$1$2")+e;case 5443:return u+e+C+"flex-item-"+d(e,/flex-|-self/,"")+e;case 4675:return u+e+C+"flex-line-pack"+d(e,/align-content|flex-|-self/,"")+e;case 5548:return u+e+C+d(e,"shrink","negative")+e;case 5292:return u+e+C+d(e,"basis","preferred-size")+e;case 6060:return u+"box-"+d(e,"-grow","")+u+e+C+d(e,"grow","positive")+e;case 4554:return u+d(e,/([^-])(transform)/g,"$1"+u+"$2")+e;case 6187:return d(d(d(e,/(zoom-|grab)/,u+"$1"),/(image-set)/,u+"$1"),e,"")+e;case 5495:case 3959:return d(e,/(image-set\([^]*)/,u+"$1$`$1");case 4968:return d(d(e,/(.+:)(flex-)?(.*)/,u+"box-pack:$3"+C+"flex-pack:$3"),/s.+-b[^;]+/,"justify")+u+e+e;case 4095:case 3583:case 4068:case 2532:return d(e,/(.+)-inline(.+)/,u+"$1$2")+e;case 8116:case 7059:case 5753:case 5535:case 5445:case 5701:case 4933:case 4677:case 5533:case 5789:case 5021:case 4765:if(_(e)-1-r>6)switch($(e,r+1)){case 109:if($(e,r+4)!==45)break;case 102:return d(e,/(.+:)(.+)-([^]+)/,"$1"+u+"$2-$3$1"+H+($(e,r+3)==108?"$3":"$2-$3"))+e;case 115:return~ee(e,"stretch")?Ae(d(e,"stretch","fill-available"),r)+e:e}break;case 4949:if($(e,r+1)!==115)break;case 6444:switch($(e,_(e)-3-(~ee(e,"!important")&&10))){case 107:return d(e,":",":"+u)+e;case 101:return d(e,/(.+:)([^;!]+)(;|!.+)?/,"$1"+u+($(e,14)===45?"inline-":"")+"box$3$1"+u+"$2$3$1"+C+"$2box$3")+e}break;case 5936:switch($(e,r+11)){case 114:return u+e+C+d(e,/[svh]\w+-[tblr]{2}/,"tb")+e;case 108:return u+e+C+d(e,/[svh]\w+-[tblr]{2}/,"tb-rl")+e;case 45:return u+e+C+d(e,/[svh]\w+-[tblr]{2}/,"lr")+e}return u+e+C+e+e}return e}var br=function(r,t,n,a){if(r.length>-1&&!r.return)switch(r.type){case se:r.return=Ae(r.value,r.length);break;case Ee:return j([W(r,{value:d(r.value,"@","@"+u)})],a);case ae:if(r.length)return Xe(r.props,function(s){switch(Qe(s,/(::plac\w+|:read-\w+)/)){case":read-only":case":read-write":return j([W(r,{props:[d(s,/:(read-\w+)/,":"+H+"$1")]})],a);case"::placeholder":return j([W(r,{props:[d(s,/:(plac\w+)/,":"+u+"input-$1")]}),W(r,{props:[d(s,/:(plac\w+)/,":"+H+"$1")]}),W(r,{props:[d(s,/:(plac\w+)/,C+"input-$1")]})],a)}return""})}},gr=[br],vr=function(r){var t=r.key;if(t==="css"){var n=document.querySelectorAll("style[data-emotion]:not([data-s])");Array.prototype.forEach.call(n,function(m){var E=m.getAttribute("data-emotion");E.indexOf(" ")!==-1&&(document.head.appendChild(m),m.setAttribute("data-s",""))})}var a=r.stylisPlugins||gr,s={},c,o=[];c=r.container||document.head,Array.prototype.forEach.call(document.querySelectorAll('style[data-emotion^="'+t+' "]'),function(m){for(var E=m.getAttribute("data-emotion").split(" "),g=1;g<E.length;g++)s[E[g]]=!0;o.push(m)});var f,h=[yr,mr];{var y,v=[or,ur(function(m){y.insert(m)})],k=fr(h.concat(a,v)),A=function(E){return j(ir(E),k)};f=function(E,g,S,b){y=S,A(E?E+"{"+g.styles+"}":g.styles),b&&(w.inserted[g.name]=!0)}}var w={key:t,sheet:new Ye({key:t,container:c,nonce:r.nonce,speedy:r.speedy,prepend:r.prepend,insertionPoint:r.insertionPoint}),nonce:r.nonce,inserted:s,registered:{},insert:f};return w.sheet.hydrate(o),w};function te(){return te=Object.assign?Object.assign.bind():function(e){for(var r=1;r<arguments.length;r++){var t=arguments[r];for(var n in t)Object.prototype.hasOwnProperty.call(t,n)&&(e[n]=t[n])}return e},te.apply(this,arguments)}var Re={exports:{}},l={};/** @license React v16.13.1
 * react-is.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var pe;function xr(){if(pe)return l;pe=1;var e=typeof Symbol=="function"&&Symbol.for,r=e?Symbol.for("react.element"):60103,t=e?Symbol.for("react.portal"):60106,n=e?Symbol.for("react.fragment"):60107,a=e?Symbol.for("react.strict_mode"):60108,s=e?Symbol.for("react.profiler"):60114,c=e?Symbol.for("react.provider"):60109,o=e?Symbol.for("react.context"):60110,f=e?Symbol.for("react.async_mode"):60111,h=e?Symbol.for("react.concurrent_mode"):60111,y=e?Symbol.for("react.forward_ref"):60112,v=e?Symbol.for("react.suspense"):60113,k=e?Symbol.for("react.suspense_list"):60120,A=e?Symbol.for("react.memo"):60115,w=e?Symbol.for("react.lazy"):60116,m=e?Symbol.for("react.block"):60121,E=e?Symbol.for("react.fundamental"):60117,g=e?Symbol.for("react.responder"):60118,S=e?Symbol.for("react.scope"):60119;function b(i){if(typeof i=="object"&&i!==null){var P=i.$$typeof;switch(P){case r:switch(i=i.type,i){case f:case h:case n:case s:case a:case v:return i;default:switch(i=i&&i.$$typeof,i){case o:case y:case w:case A:case c:return i;default:return P}}case t:return P}}}function I(i){return b(i)===h}return l.AsyncMode=f,l.ConcurrentMode=h,l.ContextConsumer=o,l.ContextProvider=c,l.Element=r,l.ForwardRef=y,l.Fragment=n,l.Lazy=w,l.Memo=A,l.Portal=t,l.Profiler=s,l.StrictMode=a,l.Suspense=v,l.isAsyncMode=function(i){return I(i)||b(i)===f},l.isConcurrentMode=I,l.isContextConsumer=function(i){return b(i)===o},l.isContextProvider=function(i){return b(i)===c},l.isElement=function(i){return typeof i=="object"&&i!==null&&i.$$typeof===r},l.isForwardRef=function(i){return b(i)===y},l.isFragment=function(i){return b(i)===n},l.isLazy=function(i){return b(i)===w},l.isMemo=function(i){return b(i)===A},l.isPortal=function(i){return b(i)===t},l.isProfiler=function(i){return b(i)===s},l.isStrictMode=function(i){return b(i)===a},l.isSuspense=function(i){return b(i)===v},l.isValidElementType=function(i){return typeof i=="string"||typeof i=="function"||i===n||i===h||i===s||i===a||i===v||i===k||typeof i=="object"&&i!==null&&(i.$$typeof===w||i.$$typeof===A||i.$$typeof===c||i.$$typeof===o||i.$$typeof===y||i.$$typeof===E||i.$$typeof===g||i.$$typeof===S||i.$$typeof===m)},l.typeOf=b,l}Re.exports=xr();var ke=Re.exports;const wr=Se(ke),Sr=qe({__proto__:null,default:wr},[ke]),$r=Ge(Sr);var ce=$r,Er={childContextTypes:!0,contextType:!0,contextTypes:!0,defaultProps:!0,displayName:!0,getDefaultProps:!0,getDerivedStateFromError:!0,getDerivedStateFromProps:!0,mixins:!0,propTypes:!0,type:!0},Cr={name:!0,length:!0,prototype:!0,caller:!0,callee:!0,arguments:!0,arity:!0},Pr={$$typeof:!0,render:!0,defaultProps:!0,displayName:!0,propTypes:!0},_e={$$typeof:!0,compare:!0,defaultProps:!0,displayName:!0,propTypes:!0,type:!0},oe={};oe[ce.ForwardRef]=Pr;oe[ce.Memo]=_e;function ye(e){return ce.isMemo(e)?_e:oe[e.$$typeof]||Er}var Or=Object.defineProperty,Tr=Object.getOwnPropertyNames,me=Object.getOwnPropertySymbols,Ar=Object.getOwnPropertyDescriptor,Rr=Object.getPrototypeOf,be=Object.prototype;function Ne(e,r,t){if(typeof r!="string"){if(be){var n=Rr(r);n&&n!==be&&Ne(e,n,t)}var a=Tr(r);me&&(a=a.concat(me(r)));for(var s=ye(e),c=ye(r),o=0;o<a.length;++o){var f=a[o];if(!Cr[f]&&!(t&&t[f])&&!(c&&c[f])&&!(s&&s[f])){var h=Ar(r,f);try{Or(e,f,h)}catch{}}}}return e}var kr=Ne;const Yr=Se(kr);var _r=!0;function Nr(e,r,t){var n="";return t.split(" ").forEach(function(a){e[a]!==void 0?r.push(e[a]+";"):n+=a+" "}),n}var Me=function(r,t,n){var a=r.key+"-"+t.name;(n===!1||_r===!1)&&r.registered[a]===void 0&&(r.registered[a]=t.styles)},Ie=function(r,t,n){Me(r,t,n);var a=r.key+"-"+t.name;if(r.inserted[t.name]===void 0){var s=t;do r.insert(t===s?"."+a:"",s,r.sheet,!0),s=s.next;while(s!==void 0)}};function Mr(e){for(var r=0,t,n=0,a=e.length;a>=4;++n,a-=4)t=e.charCodeAt(n)&255|(e.charCodeAt(++n)&255)<<8|(e.charCodeAt(++n)&255)<<16|(e.charCodeAt(++n)&255)<<24,t=(t&65535)*1540483477+((t>>>16)*59797<<16),t^=t>>>24,r=(t&65535)*1540483477+((t>>>16)*59797<<16)^(r&65535)*1540483477+((r>>>16)*59797<<16);switch(a){case 3:r^=(e.charCodeAt(n+2)&255)<<16;case 2:r^=(e.charCodeAt(n+1)&255)<<8;case 1:r^=e.charCodeAt(n)&255,r=(r&65535)*1540483477+((r>>>16)*59797<<16)}return r^=r>>>13,r=(r&65535)*1540483477+((r>>>16)*59797<<16),((r^r>>>15)>>>0).toString(36)}var Ir={animationIterationCount:1,aspectRatio:1,borderImageOutset:1,borderImageSlice:1,borderImageWidth:1,boxFlex:1,boxFlexGroup:1,boxOrdinalGroup:1,columnCount:1,columns:1,flex:1,flexGrow:1,flexPositive:1,flexShrink:1,flexNegative:1,flexOrder:1,gridRow:1,gridRowEnd:1,gridRowSpan:1,gridRowStart:1,gridColumn:1,gridColumnEnd:1,gridColumnSpan:1,gridColumnStart:1,msGridRow:1,msGridRowSpan:1,msGridColumn:1,msGridColumnSpan:1,fontWeight:1,lineHeight:1,opacity:1,order:1,orphans:1,tabSize:1,widows:1,zIndex:1,zoom:1,WebkitLineClamp:1,fillOpacity:1,floodOpacity:1,stopOpacity:1,strokeDasharray:1,strokeDashoffset:1,strokeMiterlimit:1,strokeOpacity:1,strokeWidth:1},jr=/[A-Z]|^ms/g,Fr=/_EMO_([^_]+?)_([^]*?)_EMO_/g,je=function(r){return r.charCodeAt(1)===45},ge=function(r){return r!=null&&typeof r!="boolean"},X=dr(function(e){return je(e)?e:e.replace(jr,"-$&").toLowerCase()}),ve=function(r,t){switch(r){case"animation":case"animationName":if(typeof t=="string")return t.replace(Fr,function(n,a,s){return N={name:a,styles:s,next:N},a})}return Ir[r]!==1&&!je(r)&&typeof t=="number"&&t!==0?t+"px":t};function G(e,r,t){if(t==null)return"";if(t.__emotion_styles!==void 0)return t;switch(typeof t){case"boolean":return"";case"object":{if(t.anim===1)return N={name:t.name,styles:t.styles,next:N},t.name;if(t.styles!==void 0){var n=t.next;if(n!==void 0)for(;n!==void 0;)N={name:n.name,styles:n.styles,next:N},n=n.next;var a=t.styles+";";return a}return zr(e,r,t)}case"function":{if(e!==void 0){var s=N,c=t(e);return N=s,G(e,r,c)}break}}if(r==null)return t;var o=r[t];return o!==void 0?o:t}function zr(e,r,t){var n="";if(Array.isArray(t))for(var a=0;a<t.length;a++)n+=G(e,r,t[a])+";";else for(var s in t){var c=t[s];if(typeof c!="object")r!=null&&r[c]!==void 0?n+=s+"{"+r[c]+"}":ge(c)&&(n+=X(s)+":"+ve(s,c)+";");else if(Array.isArray(c)&&typeof c[0]=="string"&&(r==null||r[c[0]]===void 0))for(var o=0;o<c.length;o++)ge(c[o])&&(n+=X(s)+":"+ve(s,c[o])+";");else{var f=G(e,r,c);switch(s){case"animation":case"animationName":{n+=X(s)+":"+f+";";break}default:n+=s+"{"+f+"}"}}}return n}var xe=/label:\s*([^\s;\n{]+)\s*(;|$)/g,N,Fe=function(r,t,n){if(r.length===1&&typeof r[0]=="object"&&r[0]!==null&&r[0].styles!==void 0)return r[0];var a=!0,s="";N=void 0;var c=r[0];c==null||c.raw===void 0?(a=!1,s+=G(n,t,c)):s+=c[0];for(var o=1;o<r.length;o++)s+=G(n,t,r[o]),a&&(s+=c[o]);xe.lastIndex=0;for(var f="",h;(h=xe.exec(s))!==null;)f+="-"+h[1];var y=Mr(s)+f;return{name:y,styles:s,next:N}},Wr=function(r){return r()},ze=fe["useInsertionEffect"]?fe["useInsertionEffect"]:!1,Lr=ze||Wr,we=ze||O.useLayoutEffect,We={}.hasOwnProperty,Le=O.createContext(typeof HTMLElement<"u"?vr({key:"css"}):null);Le.Provider;var De=function(r){return O.forwardRef(function(t,n){var a=O.useContext(Le);return r(t,a,n)})},U=O.createContext({}),Dr=function(r,t){if(typeof t=="function"){var n=t(r);return n}return te({},r,t)},Gr=le(function(e){return le(function(r){return Dr(e,r)})}),Br=function(r){var t=O.useContext(U);return r.theme!==t&&(t=Gr(t)(r.theme)),O.createElement(U.Provider,{value:t},r.children)},ne="__EMOTION_TYPE_PLEASE_DO_NOT_USE__",Hr=function(r,t){var n={};for(var a in t)We.call(t,a)&&(n[a]=t[a]);return n[ne]=r,n},qr=function(r){var t=r.cache,n=r.serialized,a=r.isStringTag;return Me(t,n,a),Lr(function(){return Ie(t,n,a)}),null},Vr=De(function(e,r,t){var n=e.css;typeof n=="string"&&r.registered[n]!==void 0&&(n=r.registered[n]);var a=e[ne],s=[n],c="";typeof e.className=="string"?c=Nr(r.registered,s,e.className):e.className!=null&&(c=e.className+" ");var o=Fe(s,void 0,O.useContext(U));c+=r.key+"-"+o.name;var f={};for(var h in e)We.call(e,h)&&h!=="css"&&h!==ne&&(f[h]=e[h]);return f.ref=t,f.className=c,O.createElement(O.Fragment,null,O.createElement(qr,{cache:r,serialized:o,isStringTag:typeof a=="string"}),O.createElement(a,f))}),Ur=Vr,Zr=De(function(e,r){var t=e.styles,n=Fe([t],void 0,O.useContext(U)),a=O.useRef();return we(function(){var s=r.key+"-global",c=new r.sheet.constructor({key:s,nonce:r.sheet.nonce,container:r.sheet.container,speedy:r.sheet.isSpeedy}),o=!1,f=document.querySelector('style[data-emotion="'+s+" "+n.name+'"]');return r.sheet.tags.length&&(c.before=r.sheet.tags[0]),f!==null&&(o=!0,f.setAttribute("data-emotion",s),c.hydrate([f])),a.current=[c,o],function(){c.flush()}},[r]),we(function(){var s=a.current,c=s[0],o=s[1];if(o){s[1]=!1;return}if(n.next!==void 0&&Ie(r,n.next,!0),c.tags.length){var f=c.tags[c.tags.length-1].nextElementSibling;c.before=f,c.flush()}r.insert("",n,c,!1)},[r,n.name]),null});export{Ur as E,Zr as G,Br as T,te as _,Yr as a,Hr as c,We as h};