(()=>{"use strict";var e,t={172:()=>{const e=window.wp.blocks,t=window.wp.blockEditor,i=window.wp.components,s=window.wp.element,n=window.wp.i18n,l=window.ReactJSXRuntime;(0,e.registerBlockType)("integlight/tab",{title:(0,n.__)("Tab","integlight"),parent:["integlight/tab-block"],icon:"screenoptions",category:"layout",attributes:{tabTitle:{type:"string",source:"html",selector:".tab-title h4",default:""}},edit:e=>{const{attributes:{tabTitle:i},setAttributes:s,className:a}=e,r=(0,t.useBlockProps)({className:"tab"});return(0,l.jsxs)("div",{...r,children:[(0,l.jsx)("div",{className:"tab-title",children:(0,l.jsx)(t.RichText,{tagName:"h4",placeholder:(0,n.__)("Tab title...","integlight"),value:i,onChange:e=>s({tabTitle:e})})}),(0,l.jsx)("div",{className:"tab-content",children:(0,l.jsx)(t.InnerBlocks,{})})]})},save:e=>{const{attributes:{tabTitle:i}}=e,s=t.useBlockProps.save({className:"wp-block-integlight-tab tab"});return(0,l.jsxs)("div",{...s,children:[(0,l.jsx)("div",{className:"tab-title",children:(0,l.jsx)(t.RichText.Content,{tagName:"h4",value:i})}),(0,l.jsx)("div",{className:"tab-content",children:(0,l.jsx)(t.InnerBlocks.Content,{})})]})}}),(0,e.registerBlockType)("integlight/tab-block",{edit:e=>{const a=(0,t.useBlockProps)({className:"integlight-tabs-block"});return(0,l.jsxs)(s.Fragment,{children:[(0,l.jsx)(t.InspectorControls,{children:(0,l.jsx)(i.PanelBody,{title:(0,n.__)("Tab setting","integlight"),initialOpen:!0})}),(0,l.jsxs)("div",{...a,children:[(0,l.jsx)("div",{className:"tabs-navigation-editor",children:(0,l.jsx)("p",{children:(0,n.__)("Tab switching is reflected when the website is displayed.","integlight")})}),(0,l.jsx)("div",{children:(0,l.jsx)(t.InnerBlocks,{allowedBlocks:["integlight/tab"],template:[["integlight/tab",{}]],templateLock:!1,renderAppender:t.InnerBlocks.ButtonBlockAppender})})]})]})},save:()=>{const e=t.useBlockProps.save({className:"integlight-tabs"});return(0,l.jsx)("div",{...e,children:(0,l.jsx)("div",{children:(0,l.jsx)(t.InnerBlocks.Content,{})})})}})}},i={};function s(e){var n=i[e];if(void 0!==n)return n.exports;var l=i[e]={exports:{}};return t[e](l,l.exports,s),l.exports}s.m=t,e=[],s.O=(t,i,n,l)=>{if(!i){var a=1/0;for(d=0;d<e.length;d++){for(var[i,n,l]=e[d],r=!0,o=0;o<i.length;o++)(!1&l||a>=l)&&Object.keys(s.O).every((e=>s.O[e](i[o])))?i.splice(o--,1):(r=!1,l<a&&(a=l));if(r){e.splice(d--,1);var c=n();void 0!==c&&(t=c)}}return t}l=l||0;for(var d=e.length;d>0&&e[d-1][2]>l;d--)e[d]=e[d-1];e[d]=[i,n,l]},s.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e={57:0,350:0};s.O.j=t=>0===e[t];var t=(t,i)=>{var n,l,[a,r,o]=i,c=0;if(a.some((t=>0!==e[t]))){for(n in r)s.o(r,n)&&(s.m[n]=r[n]);if(o)var d=o(s)}for(t&&t(i);c<a.length;c++)l=a[c],s.o(e,l)&&e[l]&&e[l][0](),e[l]=0;return s.O(d)},i=globalThis.webpackChunk=globalThis.webpackChunk||[];i.forEach(t.bind(null,0)),i.push=t.bind(null,i.push.bind(i))})();var n=s.O(void 0,[350],(()=>s(172)));n=s.O(n)})();