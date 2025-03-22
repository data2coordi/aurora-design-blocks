(()=>{"use strict";var e,o={325:()=>{const e=window.wp.blocks,o=window.wp.blockEditor,r=window.wp.components,n=window.wp.i18n,i=window.ReactJSXRuntime,a=e=>e>=0?`rgba(0, 0, 0, ${e/100})`:`rgba(255, 255, 255, ${Math.abs(e)/100})`;(0,e.registerBlockType)("aurora-design-blocks/custom-cover",{edit:({attributes:e,setAttributes:t})=>{const{innerWidthArticle:l,url:s,id:c,alt:d,focalPoint:u,dimRatio:g}=e,v=(0,o.useBlockProps)({className:"wp-block-aurora-design-blocks-custom-cover alignfull",style:{backgroundImage:s?`url(${s})`:void 0,backgroundPosition:s?`${100*u.x}% ${100*u.y}%`:void 0,backgroundSize:"cover",position:"relative"}}),b=l?"inner-article":"inner-full";return(0,i.jsxs)(i.Fragment,{children:[(0,i.jsx)(o.InspectorControls,{children:(0,i.jsxs)(r.PanelBody,{title:(0,n.__)("Cover Settings","aurora-design-blocks"),children:[(0,i.jsx)(r.ToggleControl,{label:(0,n.__)("Use Article Width for Inner Content","aurora-design-blocks"),checked:l,onChange:()=>t({innerWidthArticle:!l})}),(0,i.jsx)(r.RangeControl,{label:(0,n.__)("Overlay Opacity (-100 for bright, 100 for dark)","aurora-design-blocks"),value:g,onChange:e=>t({dimRatio:e}),min:-100,max:100}),(0,i.jsx)(o.MediaUploadCheck,{children:(0,i.jsx)(o.MediaUpload,{onSelect:e=>t({url:e.url,id:e.id,alt:e.alt}),allowedTypes:["image"],value:c,render:({open:e})=>(0,i.jsx)(r.Button,{onClick:e,isPrimary:!0,children:s?(0,n.__)("Change Background Image","aurora-design-blocks"):(0,n.__)("Upload Background Image","aurora-design-blocks")})})}),s&&(0,i.jsx)(r.Button,{onClick:()=>t({url:"",id:void 0,alt:""}),isSecondary:!0,style:{marginTop:"10px"},children:(0,n.__)("Remove Background Image","aurora-design-blocks")})]})}),(0,i.jsxs)("div",{...v,children:[(0,i.jsx)("div",{className:"cover-overlay",style:{background:a(g),position:"absolute",top:0,left:0,right:0,bottom:0,zIndex:1,pointerEvents:"none"}}),(0,i.jsx)("div",{className:`inner-container ${b}`,style:{position:"relative",zIndex:2},children:(0,i.jsx)(o.InnerBlocks,{})})]})]})},save:({attributes:e})=>{const{innerWidthArticle:r,url:n,focalPoint:t,dimRatio:l}=e,s=o.useBlockProps.save({className:"wp-block-aurora-design-blocks-custom-cover alignfull",style:{backgroundImage:n?`url(${n})`:void 0,backgroundPosition:n?`${100*t.x}% ${100*t.y}%`:void 0,backgroundSize:"cover",position:"relative"}}),c=r?"inner-article":"inner-full";return(0,i.jsxs)("div",{...s,children:[(0,i.jsx)("div",{className:"cover-overlay",style:{background:a(l),position:"absolute",top:0,left:0,right:0,bottom:0,zIndex:1,pointerEvents:"none"}}),(0,i.jsx)("div",{className:`inner-container ${c}`,style:{position:"relative",zIndex:2},children:(0,i.jsx)(o.InnerBlocks.Content,{})})]})}})}},r={};function n(e){var i=r[e];if(void 0!==i)return i.exports;var a=r[e]={exports:{}};return o[e](a,a.exports,n),a.exports}n.m=o,e=[],n.O=(o,r,i,a)=>{if(!r){var t=1/0;for(d=0;d<e.length;d++){for(var[r,i,a]=e[d],l=!0,s=0;s<r.length;s++)(!1&a||t>=a)&&Object.keys(n.O).every((e=>n.O[e](r[s])))?r.splice(s--,1):(l=!1,a<t&&(t=a));if(l){e.splice(d--,1);var c=i();void 0!==c&&(o=c)}}return o}a=a||0;for(var d=e.length;d>0&&e[d-1][2]>a;d--)e[d]=e[d-1];e[d]=[r,i,a]},n.o=(e,o)=>Object.prototype.hasOwnProperty.call(e,o),(()=>{var e={57:0,350:0};n.O.j=o=>0===e[o];var o=(o,r)=>{var i,a,[t,l,s]=r,c=0;if(t.some((o=>0!==e[o]))){for(i in l)n.o(l,i)&&(n.m[i]=l[i]);if(s)var d=s(n)}for(o&&o(r);c<t.length;c++)a=t[c],n.o(e,a)&&e[a]&&e[a][0](),e[a]=0;return n.O(d)},r=globalThis.webpackChunk=globalThis.webpackChunk||[];r.forEach(o.bind(null,0)),r.push=o.bind(null,r.push.bind(r))})();var i=n.O(void 0,[350],(()=>n(325)));i=n.O(i)})();