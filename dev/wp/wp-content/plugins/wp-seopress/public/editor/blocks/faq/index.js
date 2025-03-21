(()=>{"use strict";const e=window.wp.blocks,t=window.wp.i18n,s=JSON.parse('{"u2":"wpseopress/faq-block"}'),a=window.wp.blockEditor,n=window.wp.data;var r=window.wp,o=r.data.withSelect,l=(r.element.Component,r.components),i=l.Spinner,c=l.Button,u=(l.ResponsiveWrapper,r.compose.compose),__=r.i18n.__,p=["image"];const m=u(o((function(e,t){return{image:t.value?e("core").getMedia(t.value):null}})))((function(e){var t=e.value,s=e.image,n=React.createElement("p",null,__("To edit the background image, you need permission to upload media.","wp-seopress"));return React.createElement("div",{className:"wp-block-wp-seopress-image"},React.createElement(a.MediaUploadCheck,{fallback:n},React.createElement(a.MediaUpload,{title:__("Set Image","wp-seopress"),onSelect:function(t){e.onSelect(t.id,e.index)},allowedTypes:p,value:t,render:function(a){var n=a.open,r=function(t){var s=null;try{if(null!=t&&((s={}).source_url=t.guid.raw,null!=t.media_details.sizes))switch(s=null,e.imageSize){case"thumbnail":s=null!=t?t.media_details.sizes.thumbnail:null;break;case"medium":s=null!=t?t.media_details.sizes.medium:null;break;case"large":s=null!=t?null!=t.media_details.sizes.large?t.media_details.sizes.large:t.media_details.sizes.medium_large:null;break;default:s=null!=t?t.media_details.sizes.full:null}return s}catch(e){return s}}(s);return React.createElement(c,{className:t?"editor-post-featured-image__preview":"editor-post-featured-image__toggle",onClick:n},!t&&__("Set Image","wp-seopress"),!!t&&!s&&React.createElement(i,null),!!t&&s&&r&&r.source_url&&React.createElement("img",{src:r.source_url,alt:__("Set Image","wp-seopress")}))}})),!!t&&React.createElement(a.MediaUploadCheck,null,React.createElement(c,{onClick:function(){e.onRemoveImage(e.index)},isLink:!0,isDestructive:!0},__("Remove Image","wp-seopress"))))})),f=window.wp.components,d=function(e){var s=e.attributes,n=e.setAttributes,r=s.listStyle,o=s.titleWrapper,l=s.imageSize,i=s.showFAQScheme,c=s.showAccordion,u=s.isProActive;return React.createElement(a.InspectorControls,null,React.createElement(f.PanelBody,{title:(0,t.__)("FAQ Settings","wp-seopress")},React.createElement(f.PanelRow,{className:"wpseopress-faqs-list-style"},React.createElement("h3",null,(0,t.__)("List Style","wp-seopress")),React.createElement(f.ButtonGroup,null,React.createElement(f.Button,{isPressed:"none"==r,onClick:function(e){n({listStyle:"none"})}},(0,t._x)("NONE","Div tag List","wp-seopress")),React.createElement(f.Button,{isPressed:"ol"==r,onClick:function(e){n({listStyle:"ol"})}},(0,t._x)("OL","Numbered List","wp-seopress")),React.createElement(f.Button,{isPressed:"ul"==r,onClick:function(e){n({listStyle:"ul"})}},(0,t._x)("UL","Unordered List","wp-seopress")))),React.createElement(f.PanelRow,{className:"wpseopress-faqs-title-wrapper"},React.createElement("h3",null,(0,t.__)("Title Wrapper","wp-seopress")),React.createElement(f.ButtonGroup,null,React.createElement(f.Button,{isPressed:"h2"==o,onClick:function(e){n({titleWrapper:"h2"})}},(0,t._x)("H2","H2 title tag","wp-seopress")),React.createElement(f.Button,{isPressed:"h3"==o,onClick:function(e){n({titleWrapper:"h3"})}},(0,t._x)("H3","H3 title tag","wp-seopress")),React.createElement(f.Button,{isPressed:"h4"==o,onClick:function(e){n({titleWrapper:"h4"})}},(0,t._x)("H4","H4 title tag","wp-seopress")),React.createElement(f.Button,{isPressed:"h5"==o,onClick:function(e){n({titleWrapper:"h5"})}},(0,t._x)("H5","H5 title tag","wp-seopress")),React.createElement(f.Button,{isPressed:"h6"==o,onClick:function(e){n({titleWrapper:"h6"})}},(0,t._x)("H6","H6 title tag","wp-seopress")),React.createElement(f.Button,{isPressed:"p"==o,onClick:function(e){n({titleWrapper:"p"})}},(0,t._x)("P","P title tag","wp-seopress")),React.createElement(f.Button,{isPressed:"div"==o,onClick:function(e){n({titleWrapper:"div"})}},(0,t._x)("DIV","DIV title tag","wp-seopress")))),React.createElement(f.PanelRow,{className:"wpseopress-faqs-image-size"},React.createElement("h3",null,(0,t.__)("Image Size","wp-seopress")),React.createElement(f.ButtonGroup,null,React.createElement(f.Button,{isPressed:"thumbnail"==l,onClick:function(e){n({imageSize:"thumbnail"})}},(0,t._x)("S","Thubmnail Size","wp-seopress")),React.createElement(f.Button,{isPressed:"medium"==l,onClick:function(e){n({imageSize:"medium"})}},(0,t._x)("M","Medium Size","wp-seopress")),React.createElement(f.Button,{isPressed:"large"==l,onClick:function(e){n({imageSize:"large"})}},(0,t._x)("L","Large Size","wp-seopress")),React.createElement(f.Button,{isPressed:"full"==l,onClick:function(e){n({imageSize:"full"})}},(0,t._x)("XL","Original Size","wp-seopress")))),u&&React.createElement(React.Fragment,null,React.createElement(f.PanelRow,{className:"wpseopress-faqs-seo-settings"},React.createElement("h3",null,(0,t.__)("SEO Settings","wp-seopress")),React.createElement(f.ToggleControl,{label:(0,t.__)("Enable FAQ Schema","wp-seopress"),checked:!!i,onChange:function(e){n({showFAQScheme:!i})}}))),React.createElement(f.PanelRow,{className:"wpseopress-faqs-display-settings"},React.createElement("h3",null,(0,t.__)("Display","wp-seopress")),React.createElement(f.ToggleControl,{label:(0,t.__)("Enable accordion","wp-seopress"),checked:!!c,onChange:function(e){n({showAccordion:!c})}}))))};function w(e){return w="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},w(e)}function g(e,t){var s=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),s.push.apply(s,a)}return s}function R(e){for(var t=1;t<arguments.length;t++){var s=null!=arguments[t]?arguments[t]:{};t%2?g(Object(s),!0).forEach((function(t){var a,n,r,o;a=e,n=t,r=s[t],o=function(e,t){if("object"!=w(e)||!e)return e;var s=e[Symbol.toPrimitive];if(void 0!==s){var a=s.call(e,"string");if("object"!=w(a))return a;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(e)}(n),(n="symbol"==w(o)?o:String(o))in a?Object.defineProperty(a,n,{value:r,enumerable:!0,configurable:!0,writable:!0}):a[n]=r})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(s)):g(Object(s)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(s,t))}))}return e}function b(e,t){(null==t||t>e.length)&&(t=e.length);for(var s=0,a=new Array(t);s<t;s++)a[s]=e[s];return a}const v=(0,n.withSelect)((function(e,t){var s=t.attributes,a=e("core").getMedia,n=s.selectedImageId;return{selectedImage:n?a(n):0}}))((function(e){var s=e.attributes,n=e.setAttributes,r=s.listStyle,o=function(){return"none"===s.listStyle&&s.faqs.map((function(e,n){return React.createElement("div",{key:n,className:"wpseopress-faqs-area"},React.createElement("div",{className:"wpseopress-faq"},React.createElement(a.RichText,{tagName:s.titleWrapper,className:"wpseopress-faq-question",placeholder:(0,t.__)("Question...","wp-seopress"),value:e?e.question:"",onChange:function(e){return i(e,n)}}),React.createElement("div",{className:"wpseopress-answer-meta"},React.createElement(m,{value:e?e.image:"",onSelect:u,onRemoveImage:p,imageSize:s.imageSize,index:n}),React.createElement(a.RichText,{tagName:"p",className:"wpseopress-faq-answer",placeholder:(0,t.__)("Answer...","wp-seopress"),value:e?e.answer:"",onChange:function(e){return c(e,n)}}))),React.createElement("div",{className:"wpseopress-faq-cta"},React.createElement("button",{className:"components-button is-tertiary is-destructive",value:(0,t.__)("Remove","wp-seopress"),onClick:function(){return l(n)}},(0,t.__)("Remove","wp-seopress"))))}))||("ul"===s.listStyle||"ol"===s.listStyle)&&s.faqs.map((function(e,n){return React.createElement("li",{key:n,className:"wpseopress-faqs-area"},React.createElement("div",{className:"wpseopress-faq"},React.createElement(a.RichText,{tagName:s.titleWrapper,className:"wpseopress-faq-question",placeholder:(0,t.__)("Question...","wp-seopress"),value:e?e.question:"",onChange:function(e){return i(e,n)}}),React.createElement("div",{className:"wpseopress-answer-meta"},React.createElement(m,{value:e?e.image:"",onSelect:u,onRemoveImage:p,imageSize:s.imageSize,index:n}),React.createElement(a.RichText,{tagName:"div",className:"wpseopress-faq-answer",placeholder:(0,t.__)("Answer...","wp-seopress"),value:e?e.answer:"",onChange:function(e){return c(e,n)}}))),React.createElement("div",{className:"wpseopress-faq-cta"},React.createElement("button",{className:"components-button is-tertiary is-destructive",value:(0,t.__)("Remove","wp-seopress"),onClick:function(){return l(n)}},(0,t.__)("Remove","wp-seopress"))))}))},l=function(t){var a=s.faqs.filter((function(e,s){return s!==t}));e.setAttributes({faqs:a})},i=function(t,a){var n=s.faqs.map((function(e,s){return s!==a?e:R(R({},e),{},{question:t})}));e.setAttributes({faqs:n})},c=function(t,a){var n=s.faqs.map((function(e,s){return s!==a?e:R(R({},e),{},{answer:t})}));e.setAttributes({faqs:n})},u=function(t,a){var n=s.faqs.map((function(e,s){return s!==a?e:R(R({},e),{},{image:t})}));e.setAttributes({faqs:n})},p=function(t){var a=s.faqs.map((function(e,s){return s!==t?e:R(R({},e),{},{image:null})}));e.setAttributes({faqs:a})};return React.createElement(React.Fragment,null,React.createElement(d,{attributes:s,setAttributes:n}),React.createElement("div",(0,a.useBlockProps)({className:"wpseopress-faqs"}),"ul"===r&&React.createElement("ul",null,o()),"ol"===r&&React.createElement("ol",null,o()),"none"===r&&o(),React.createElement("div",{className:"wpseopress-faqs-actions"},React.createElement("button",{type:"button",title:(0,t.__)("Add FAQ","wp-seopress"),className:"add-faq components-button is-secondary",onClick:function(t){var a;t.preventDefault(),e.setAttributes({faqs:[].concat((a=s.faqs,function(e){if(Array.isArray(e))return b(e)}(a)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(a)||function(e,t){if(e){if("string"==typeof e)return b(e,t);var s=Object.prototype.toString.call(e).slice(8,-1);return"Object"===s&&e.constructor&&(s=e.constructor.name),"Map"===s||"Set"===s?Array.from(e):"Arguments"===s||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(s)?b(e,t):void 0}}(a)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()),[{question:"",answer:"",image:""}])})}},(0,t.__)("Add FAQ","wp-seopress")))))}));(0,e.registerBlockType)(s.u2,{title:(0,t.__)("FAQ (legacy)","wp-seopress"),description:(0,t.__)("Allows to easily build FAQs.","wp-seopress"),keywords:[(0,t.__)("FAQ","wp-seopress")],edit:v,save:function(){return null}})})();