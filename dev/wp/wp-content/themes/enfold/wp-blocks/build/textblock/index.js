(()=>{"use strict";const t=window.wp.blocks,e=window.wp.blockEditor,n=window.ReactJSXRuntime;(0,t.registerBlockType)("enfold/custom-text-block",{title:"Enfold Block",description:"(in beta only - do not use)",icon:"edit",category:"text",attributes:{content:{type:"string",source:"html",selector:"p"}},edit:({attributes:t,setAttributes:o})=>{const{content:c}=t;return(0,n.jsx)(e.RichText,{tagName:"p",value:c,onChange:t=>o({content:t}),placeholder:"Enter your text here (Do not use - this is currently only a beta element ..."})},save:({attributes:t})=>{const{content:o}=t;return(0,n.jsx)(e.RichText.Content,{tagName:"p",value:o})}})})();