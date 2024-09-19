"use strict";(self.webpackChunkrealCookieBanner_=self.webpackChunkrealCookieBanner_||[]).push([[40],{9487:(e,t,n)=>{function o(e,t,n){void 0===n&&(n=0);const o=[];let i=e.parentElement;const r=void 0!==t;let s=0;for(;null!==i;){const a=i.nodeType===Node.ELEMENT_NODE;if(0===s&&1===n&&a&&r){const n=e.closest(t);return n?[n]:[]}if((!r||a&&i.matches(t))&&o.push(i),i=i.parentElement,0!==n&&o.length>=n)break;s++}return o}n.d(t,{M:()=>o})},4421:(e,t,n)=>{n.r(t),n.d(t,{WebsiteBanner:()=>Ce});var o=n(6425),i=n(7936),r=n(2974),s=n(9810),a=n(6513),c=n(6005);var l=n(5535),d=n(3477);const u=async e=>{let{supportsCookiesName:t}=e;const n=[];document.dispatchEvent(new CustomEvent("RCB/PreDecision/Promises",{detail:{promises:n}}));try{const e=await Promise.all(n);for(const t of e)if(t)return t}catch(e){}return!1};var h=n(3422);const v=async e=>{let{supportsCookiesName:t}=e;return!(0,h.s)(t)&&"essentials"};var g=n(5585);const m=async e=>{let{decisionCookieName:t,revisionHash:n}=e;const o=(0,g.y)(t);if(!1===o)return!1;const{revision:i}=o;return n===i&&"consent"};var f=n(5276),p=n(5790),y=n(3256);function b(){const{userAgent:e}=navigator;return!!e&&!/chrome-lighthouse/i.test(e)&&!(0,y.W)()&&(0,p.S1)(e)}const w=(e,t,n)=>(void 0===t&&(t=1e4),void 0===n&&(n=!0),async o=>{let{decisionCookieName:i,revisionHash:r}=o;if(b()||!n)return!1;const s=(0,g.y)(i);if(s){const{revision:e}=s;if(r===e)return"consent"}try{const{predecision:n}=await(a=e(),c=t,new Promise(((e,t)=>{a.then(e,t);const n=new Error("Timed out");setTimeout(t,c,n)})));return n}catch(e){return!1}var a,c});var C=n(3697),k=n(6305);const x={path:"/consent/dynamic-predecision",method:k.X.POST,obfuscatePath:"keep-last-part"};function P(e){(0,i.vJ)((()=>{if((0,r.j)().customizeIdsBanner)return;const{restNamespace:t,restRoot:o,restQuery:i,restNonce:h,restPathObfuscateOffset:p,others:{isPreventPreDecision:y,hasDynamicPreDecisions:k,frontend:{isRespectDoNotTrack:P,isAcceptAllForBots:O}}}=(0,s.b)(),{onSave:N,suspense:S}=e;var D,T,B,A;!async function(e,t){let o=!0;const i=e instanceof c.U?e.getOptions():e,{gateways:r,args:s,onIsDoNotTrack:a,onShowCookieBanner:u}=t;for(const e of r){const t=await e(i,...s);if(!1!==t){o=!1;const e=e=>Promise.all([n.e(261),n.e(886),n.e(436),n.e(4)]).then(n.bind(n,7218)).then((t=>{let{apply:n}=t;return n({type:e,...i})}));"all"===t?e("all"):"essentials"===t?e("essentials"):"dnt"===t?a((()=>e("essentials"))):"consent"===t&&e("consent");break}}o&&(u(),document.dispatchEvent(new CustomEvent("RCB/Banner/Show/Interactive")),await(0,d.G)(),document.dispatchEvent(new CustomEvent(l.Z,{detail:{}})))}((0,a.C)(),{gateways:[async()=>(await S.tcf,!1),u,v,m,(B=["login-action-"],"force-cookie-banner",async()=>{const{className:e}=document.body;return!(e&&e.indexOf("force-cookie-banner")>-1)&&B.filter((t=>e.indexOf(t)>-1)).length>0&&"consent"}),(T=!!O&&"all",async e=>{let{decisionCookieName:t}=e;return await(0,f.P)(),!(!1!==(0,g.y)(t)||!T)&&!!b()&&T}),(A=P,void 0===A&&(A=!0),async e=>{let{decisionCookieName:t,groups:n}=e;const o=n.find((e=>{let{isEssential:t}=e;return t}));if(!1!==(0,g.y)(t)||!A)return!1;for(const e of n)if(e!==o)for(const{legalBasis:t}of e.items)if("legitimate-interest"===t)return!1;return!!function(){try{const e=window;if((e.doNotTrack||e.navigator.doNotTrack||e.navigator.msDoNotTrack||"msTrackingProtectionEnabled"in e.external)&&("1"==e.doNotTrack||"yes"==e.navigator.doNotTrack||"1"==e.navigator.doNotTrack||"1"==e.navigator.msDoNotTrack||e.external.msTrackingProtectionEnabled()))return!0}catch(e){}return!1}()&&"dnt"}),w((()=>{const{clientWidth:e,clientHeight:n}=document.documentElement;return(0,C.h)({location:x,options:{restNamespace:t,restRoot:o,restQuery:i,restNonce:h,restPathObfuscateOffset:p},sendRestNonce:!1,sendReferer:!0,request:{viewPortWidth:e,viewPortHeight:n,referer:window.location.href}})}),1e4,k),(D=y,async()=>!!D&&(b()?"all":"consent"))],args:[e],onIsDoNotTrack:()=>{N(!0,"none")},onShowCookieBanner:()=>{e.set({visible:!0})}})}),[])}var O=n(5830),N=n(4265),S=n(6021),D=n(295),T=n(2425),B=n(3912);const A=()=>{const{headerDesign:{fontColor:e,fontSize:t},texts:{acceptEssentials:n},activeAction:r,pageRequestUuid4:s,i18n:{close:a,closeWithoutSaving:c},buttonClicked:l=""}=(0,S.Y)(),{buttonClickedCloseIcon:d,closeIcon:u}=(0,T.C)(),h=(0,i.Kr)((()=>window.innerWidth),[]);return(0,o.Y)(B.U,{width:t,color:e,tooltipText:r?"change"===r?c:a:n,tooltipAlways:h<D.X5,framed:l===d,renderInContainer:document.getElementById(s).querySelector("dialog"),onClick:u})};var E=n(5263);const Y=[Symbol("extendBannerContentStylesheet"),(e,t)=>{let{boolIf:n,boolSwitch:o,boolOr:i,computed:r,boolNot:s,jsx:a,variable:c}=e,{dimsOverlay:l,dimsHeader:d,dimsFooter:u,dimsRightSidebar:h,boolLargeOrMobile:v,isMobile:g,isBanner:m,design:f,bodyDesign:p,headerDesign:y,layout:b,decision:w,mobile:C,texts:k,activeAction:x,footerDesign:P,individualLayout:O,individualPrivacyOpen:N,footerBorderStyle:S,headerBorderStyle:D}=t;const T=r([y.logo,y.logoRetina,y.logoFitDim,y.logoRetinaFitDim,y.logoMaxHeight],(e=>{let[t,n,o,i,r]=e;const s=n&&!(null==t?void 0:t.endsWith(".svg"))&&window.devicePixelRatio>1?i:o;return(null==s?void 0:s[0])>0?{width:(0,E.dD)(s[0]),height:(0,E.dD)(s[1])}:{width:"auto",height:(0,E.dD)(r)}})),B=n({when:m,then:{when:[N,s(O.inheritBannerMaxWidth)],then:O.bannerMaxWidth(),or:b.bannerMaxWidth()}}),A=v(y.borderWidth,n),[Y]=a("div",{classNames:"header-container",position:"sticky",zIndex:9,top:0,background:n(y.inheritBg,f.bg(),y.bg()),padding:v(y.padding,n),paddingBottom:`calc(${A} + ${v(y.padding,n,2)})`,...D,pseudos:{":has(>div:empty)":{display:"none"},":has(>div:empty)+div":D,":after":{content:"''",display:"block",position:"absolute",left:"0px",right:"0px",bottom:"0px",background:y.borderColor(),height:A},">div":{transition:"width 500ms, max-width 500ms",maxWidth:B,margin:"auto",display:"flex",alignItems:"center",position:"relative",textAlign:n(y.inheritTextAlign,f.textAlign("val"),y.textAlign("val")),justifyContent:n(y.inheritTextAlign,o([[f.textAlign("is-center"),"center"],[f.textAlign("is-right"),"flex-end"]]),o([[y.textAlign("is-center"),"center"],[y.textAlign("is-right"),"flex-end"]])),flexDirection:n({when:[y.logo("is-filled"),k.headline("is-filled")],then:o([[y.logoPosition("is-left"),"row"],[y.logoPosition("is-right"),"row-reverse"]],"column")})},">div>img":{margin:v(y.logoMargin,n),width:T.width(),height:T.height()}}}),R=o([[[x("is-filled"),w.showCloseIcon()],"51px"]],"0px"),I=l[1].height(),L=c(`calc(${I} - ${n(m,"0px","20px")} - ${R})`),$=c(`calc(100px + ${h[1].height()} + ${d[1].height()} + ${u[1].height()})`),[F]=a("div",{classNames:"content",position:"relative",overflow:"auto",maxHeight:n({when:g,then:{when:N,then:`calc(${I} - ${R})`,or:`calc(min(${I}, ${C.maxHeight()}) - ${R})`},or:{when:i([N,s(b.maxHeightEnabled)]),then:L(),or:`min(max(${b.maxHeight()}, ${$()}), ${L()})`}}),..."Win32"===navigator.platform?{overflow:CSS.supports("overflow","overlay")?"overlay":"scroll",scrollbarWidth:"thin",scrollbarColor:`${p.teachingsFontColor()} transparent`,pseudos:{"::-webkit-scrollbar":{width:"11px"},"::-webkit-scrollbar-track":{background:"transparent"},"::-webkit-scrollbar-thumb":{background:p.teachingsFontColor(),borderRadius:b.dialogBorderRadius(),border:`3px solid ${f.bg()}`}}}:{}}),H=v(P.borderWidth,n),[J]=a("div",{classNames:"footer-container",fontWeight:P.fontWeight(),color:P.fontColor(),position:"sticky",bottom:"0px",zIndex:1,padding:v(P.padding,n),paddingTop:`calc(${H} + ${v(P.padding,n,0)})`,background:n(P.inheritBg,f.bg(),P.bg()),fontSize:v(P.fontSize,n),textAlign:n(P.inheritTextAlign,f.textAlign("val"),P.textAlign()),...S,pseudos:{":after":{content:"''",display:"block",position:"absolute",left:"0px",right:"0px",top:"0px",background:P.borderColor(),height:H},">div":{transition:"width 500ms, max-width 500ms",maxWidth:B,margin:"auto",lineHeight:"1.8"},":has(>div:empty)":{display:"none"}}});return{HeaderContainer:Y,Content:F,FooterContainer:J}}];var R=n(7805);const I=(0,i.Rf)(((e,t)=>{let{className:n}=e;const i=(0,N.y)(),{a11yIds:r,HeaderContainer:s,hasCloseIcon:a,HeaderTitle:c}=i.extend(...Y).extend(...R.h),{headerDesign:{logo:l,logoRetina:d,logoAlt:u},decision:{showCloseIcon:h},texts:{headline:v},activeAction:g,individualPrivacyOpen:m,individualTexts:f,i18n:{headerTitlePrivacyPolicyHistory:p}}=(0,S.Y)(),y=d&&!(null==l?void 0:l.endsWith(".svg"))&&window.devicePixelRatio>1?d:l,b=!!h||!!g,w=m?"history"===g?p:f.headline:v;return(0,o.Y)(s,{ref:t,className:n,children:(0,o.FD)("div",{children:[!!y&&(0,o.Y)("img",{"aria-hidden":!0,alt:u||"",src:y}),!!w&&(0,o.Y)(c,{id:r.headline,className:b?a:void 0,children:w}),b&&(0,o.Y)(A,{})]})})}));var L=n(1282),$=n(2416),F=n(7899);const H=()=>{const{FooterLanguageSwitcherSelect:e}=(0,F.o)().extend(...R.h),{footerDesign:{languageSwitcher:t},languageSwitcher:n,onLanguageSwitch:r}=(0,S.Y)(),s=(0,i.Kr)((()=>n.find((e=>{let{current:t}=e;return t}))),[n]),a="flags"===t&&!!(null==s?void 0:s.flag);return(0,o.FD)(e,{"data-flag":a,children:[a&&(0,o.Y)("span",{style:{backgroundImage:`url(${s.flag})`}}),(0,o.Y)("select",{value:null==s?void 0:s.locale,"aria-label":null==s?void 0:s.name,onChange:e=>{null==r||r(n.find((t=>{let{locale:n}=t;return n===e.target.value})))},children:n.map((e=>{let{locale:t,name:n}=e;return(0,o.Y)("option",{value:t,children:n},t)}))})]})},J=(0,i.Rf)(((e,t)=>{const{FooterContainer:n}=(0,N.y)().extend(...Y),r=(0,S.Y)(),{isTcf:s,layout:{type:a},footerDesign:{languageSwitcher:c},individualPrivacyOpen:l,onClose:d,i18n:{tcf:u},isConsentRecord:h,languageSwitcher:v,set:g}=r,m=(0,i.hb)((e=>{d(),e.preventDefault()}),[d]),{rows:f,render:p}=(0,$.D)({onClose:h?m:void 0,putPoweredByLinkInRow:"banner"===a?0:1,row1:[!1],row1End:[(null==v?void 0:v.length)>0&&c&&"disabled"!==c&&(0,o.Y)(H,{},"languageSwitcher")]});return(0,o.Y)(n,{ref:t,children:(0,o.Y)("div",{children:p(f)})})}));var W=n(6929),M=n(1685),z=n.n(M),q=n(1453);const G=(0,q.g)(Promise.resolve(I),"BannerHeader"),U=(0,q.g)(Promise.resolve(L.R),"BannerBody"),K=(0,q.g)(Promise.resolve(J),"BannerFooter");var V=n(9487),_=n(4914);const j='[href^="#consent-"]';function Q(){window.location.hash.startsWith("#consent-")&&(window.location.hash="")}var X=n(3533);function Z(e,t){const n=(0,i.li)(0),o=(0,i.li)(0),[r,s]=(0,i.J0)(e),[a,c]=(0,i.J0)(void 0),[l,d]=(0,i.J0)(t),[u,h]=(0,i.J0)(void 0);return(0,i.vJ)((()=>{n.current>0&&("none"===e?s(e):(s("none"),c(e))),n.current++}),[e]),(0,i.vJ)((()=>{o.current>0&&(0===t?d(t):(d(0),h(t),s("none"),c(e))),o.current++}),[t]),(0,i.vJ)((()=>{void 0!==a&&(s(a),c(void 0))}),[a]),(0,i.vJ)((()=>{void 0!==u&&(d(u),h(void 0))}),[u]),[r,l]}var ee=n(105),te=n(360),ne=n(1555),oe=n(2315);const ie=(0,q.g)(Promise.resolve((()=>{const{Content:e,hideOnMobileClass:t,dimsContent:n,dimsOverlay:r,dimsHeader:s,dimsFooter:a,dimsRightSidebar:c,A11ySkipToLink:l,a11yIds:{firstButton:d}}=(0,N.y)().extend(...W.R).extend(...Y),{decision:{acceptAll:u,acceptEssentials:h,showCloseIcon:v},mobile:g,individualPrivacyOpen:m,bodyDesign:{acceptEssentialsUseAcceptAll:p},activeAction:y,pageRequestUuid4:b,i18n:{skipToConsentChoices:w}}=(0,S.Y)(),C=(0,i.li)(),k=p&&u===h?u:h,x=!g.hideHeader||y||m||"hide"===k&&v?"":t,P=(0,i.li)();P.current=P.current||{};const O=(0,i.hb)((()=>[document.querySelector(`#${b} div[class*="animate__"]`)]),[b]),D=(0,i.hb)(((e,t)=>{let[n,,o]=e;t?n(t,O()):o()}),[O]),T=(0,i.hb)((e=>D(s,e)),[D]),B=(0,i.hb)((e=>D(a,e)),[D]),A=(0,i.hb)((e=>D(c,e)),[D]);return(0,i.vJ)((()=>{const e=O(),t=[n[0](C.current),r[0](document.querySelector(`#${b}`),e)];return()=>t.forEach((e=>e()))}),[]),(0,i.vJ)((()=>{z().mutate((()=>(0,f.P)().then((()=>C.current.scrollTop=0))))}),[m]),(0,o.FD)(e,{ref:C,children:[(0,o.Y)(l,{href:`#${d}`,children:w}),(0,o.Y)(G,{ref:T,className:x}),(0,o.Y)(U,{rightSideContainerRef:A}),(0,o.Y)(K,{ref:B})]})})),"BannerContent"),re=(0,q.g)(Promise.all([n.e(261),n.e(886),n.e(436),n.e(4)]).then(n.bind(n,511)).then((e=>{let{BannerSticky:t}=e;return t}))),se=(e,t)=>{const{dataset:n,style:o}=document.body;void 0===n.rcbPreviousOverflow&&(n.rcbPreviousOverflow=o.overflow),o.overflow=e&&t?"hidden":n.rcbPreviousOverflow,document.body.parentElement.style.overflow=o.overflow},ae=(0,q.g)(Promise.resolve((()=>{const e=(0,S.Y)(),{recorder:t,visible:n,isConsentGiven:r,skipOverlay:s,pageRequestUuid4:a,individualPrivacyOpen:c,fetchLazyLoadedDataForSecondView:l,onClose:d,layout:{overlay:u,animationInDuration:h,animationOutDuration:v},sticky:g}=e,m=(0,i.li)(),f=(0,i.li)(),p=(0,i.li)(!1),[y,b]=function(e){let{animationIn:t,animationInOnlyMobile:n,animationOut:o,animationOutOnlyMobile:r}=e;const s=(0,i.Kr)((()=>window.innerWidth),[])<D.X5;let a=n?s?t:"none":t,c=r?s?o:"none":o;return(0,i.Kr)((()=>{const e=window.navigator.userAgent.toLowerCase();return 4===["firefox","gecko","mobile","android"].map((t=>e.indexOf(t)>-1)).filter(Boolean).length}),[])&&(a="none",c="none"),[a,c]}(e.layout),[w,C]=Z(y,h),[k,x]=Z("none"===b?"fadeOut":b,"none"===b?0:v),[P,O]=(0,ne.F)(["BannerContent","BannerHeader","BannerBody","BannerFooter","BodyDescription"],z().mutate.bind(z()),(()=>m.current.style.removeProperty("display"))),N=(0,F.o)(),{a11yIds:{firstButton:T},inner:B,Dialog:A,Overlay:E,individualPrivacyOpen:Y,registerWindowResize:R}=N.extend(...te.Z);(0,i.Kr)((()=>{Y.update(c),c&&(null==l||l())}),[c]),(0,i.vJ)(R,[]),(0,i.vJ)((()=>()=>{se(!1,u)}),[u]),function(){const{openBanner:e,openHistory:t,revokeConsent:n}=(0,S.Y)();(0,i.vJ)((()=>{const o=(o,i,r)=>{if(e)switch(o){case"change":e(r);break;case"history":t(r);break;case"revoke":n(i,r)}},i=t=>{if(!e)return;const n=t.target;(0,V.M)(n,j).concat((0,_.B)(n,j)?[n]:[]).forEach((e=>{o(e.getAttribute("href").slice(9),e.getAttribute("data-success-message"),t)})),(0,_.B)(n,".rcb-sc-link")&&o(n.getAttribute("href").slice(1),n.getAttribute("data-success-message"),t)},r=()=>{const{hash:e}=window.location;e.startsWith("#consent-")&&o(e.substring(9),void 0,void 0)};return r(),window.addEventListener("hashchange",r),document.addEventListener("click",i,!0),()=>{window.removeEventListener("hashchange",r),document.removeEventListener("click",i,!0)}}),[e,t,n])}(),(0,i.vJ)((()=>{n&&t&&z().mutate((()=>{t.restart()}))}),[n,t]),(0,i.vJ)((()=>{const e=m.current,t=f.current||document.getElementById(a),o=function(e){this.querySelector(`a[href="#${T}"]`).focus(),e.preventDefault()};if(n?(p.current=!0,(null==e?void 0:e.isConnected)&&(e.open&&(null==e.close||e.close.call(e)),z().mutate((()=>{var t;null==(t=e[u?"showModal":"show"])||t.call(e)})),e.addEventListener("cancel",o))):e&&(null==e.close||e.close.call(e)),t){const e=0,o=n?"none"===y?e:h:"none"===b?e:v,i=o>0,r=e=>{i&&(t.style.transition=`background ${o}ms`),t.style.display=e?"block":"none",se(e,u)};n?z().mutate((()=>{r(!0)})):p.current&&(setTimeout((()=>z().mutate((()=>r(!1)))),o),Q())}return()=>{null==e||e.removeEventListener("keyup",o)}}),[n,u]),(0,i.vJ)((()=>{n&&z().mutate((()=>m.current.focus({preventScroll:!0})))}),[n,c]),(0,i.vJ)((()=>{const e=e=>{let{detail:{triggeredByOtherTab:t}}=e;t&&d()};return document.addEventListener(oe.r,e),()=>{document.removeEventListener(oe.r,e)}}),[d]);const I=[];if(r&&g.enabled&&I.push((0,o.Y)(re,{},"sticky")),n||p.current){const e=(0,o.Y)(A,{className:"wp-exclude-emoji "+(c?"second-layer":""),ref:m,style:{display:"none"},"data-nosnippet":!0,children:(0,o.Y)(P,{value:O,children:(0,o.Y)(X.N,{animationIn:w,animationInDuration:C,animationOut:k,animationOutDuration:x,isVisible:n,className:B,children:(0,o.Y)(ie,{})})})},"dialog");I.push(s?e:(0,o.Y)(E,{id:a,className:N.className,ref:f,children:e},"overlay"))}return(0,o.Y)(i.FK,{children:I})})));var ce=n(7116),le=n(4008),de=n(5765);const ue=e=>{e&&(e.preventDefault(),e.stopPropagation())},he=(e,t)=>Object.assign(e,{activeAction:t,individualPrivacyOpen:!0,refreshSiteAfterSave:"change"===t&&2e3,visible:!0}),ve={path:"/revision/second-view",method:k.X.GET,obfuscatePath:"keep-last-part"},ge={path:"/consent",method:k.X.GET,obfuscatePath:"keep-last-part"};var me=n(2588),fe=n(9485),pe=n(2368);const ye=e=>{let{children:t}=e;return(0,o.Y)(i.FK,{children:t})},be=e=>{let{promise:t,children:n,suspenseProbs:r}=e;const s=(0,i.Kr)((()=>(0,q.g)((t||Promise.resolve()).then((()=>ye)),void 0,r)),[t]);return(0,o.Y)(s,{children:n})},we=(0,q.g)(Promise.resolve((()=>{const{pageRequestUuid4:e}=(0,S.Y)(),t=(0,ee.N)();t.specify(e);const[n,i]=(0,F.d)(t);return(0,o.Y)(n,{value:i,children:(0,o.Y)(ae,{})})}))),Ce=e=>{let{poweredLink:t}=e;const{frontend:n,customizeValuesBanner:c,pageRequestUuid4:l,iso3166OneAlpha2:d,bannerDesignVersion:u,bannerI18n:h,isPro:v,isLicensed:m,isDevLicense:p,affiliate:y,isCurrentlyInTranslationEditorPreview:b}=(0,r.j)(),{restNamespace:w,restRoot:k,restQuery:x,restNonce:N,restPathObfuscateOffset:D}=(0,s.b)(),{decisionCookieName:T}=n,B=(0,a.C)(),A=B.getUserDecision(!0),E=!1===A?void 0:A.buttonClicked,Y=(0,pe.J)(B.getOption("gcmCookieName"),E),R=(z=n.isTcf,q=n.tcf,G=n.tcfMetadata,B.getOptions(),U=async()=>{},K=[z,q,G,E],(0,i.Kr)((()=>(0,f.P)().then(U)),K)),[I,L]=function(e,t){const o=window.rcbLazyPromise;let i,r;if(o)[r,i]=o;else{let e=!1;r=!1===t?Promise.resolve({}):new Promise((t=>{i=async()=>{e||(e=!0,t(await(0,C.h)({location:ve,options:{restNamespace:w,restRoot:k,restQuery:x,restNonce:N,restPathObfuscateOffset:D},params:{revisionHash:n.revisionHash},sendRestNonce:!1})))}}))}return[r,i]}(0,n.hasLazyData),$=document.getElementById(l),F=(0,i.Kr)((()=>new fe.v($)),[]),H=(0,ce.u)(),J={onClose:e=>{Object.assign(e,{visible:!1,refreshSiteAfterSave:!1})},openHistory:(e,t)=>{he(e,"history"),ue(t)},openBanner:(e,t)=>{he(e,"change"),ue(t)},revokeConsent:(e,t,n)=>{let{onPersistConsent:o,onApplyConsent:i,isTcf:r,tcf:s,isGcm:a,groups:c}=e;o({consent:(0,de.w)(c,!0),gcmConsent:a?[]:void 0,buttonClicked:"shortcode_revoke",tcfString:void 0}).then((()=>i())).then((()=>{t&&alert(t),Q(),setTimeout((()=>window.location.reload()),2e3)})),ue(n)},onSave:(e,t,n)=>{const{refreshSiteAfterSave:o}=e,i=(0,f.P)().then((async()=>{const{onPersistConsent:o,onApplyConsent:i,activeAction:r,consent:s,tcf:a,isTcf:c,isGcm:l,gcmConsent:d,recorder:u}=e;let h;if(l)switch(n){case"ind_all":case"ind_custom":case"main_all":case"main_custom":h=d;break;default:h=[]}return o({consent:s,gcmConsent:h,markAsDoNotTrack:t,buttonClicked:n,tcfString:void 0,recorderJsonString:u?JSON.stringify(u.createReplay()):void 0,uiView:"change"===r?"change":"revoke"!==r?"initial":void 0}).then((()=>i()))}));o?i.then((()=>{Q(),setTimeout((()=>window.location.reload()),o||2e3)})):Object.assign(e,{visible:!1})},updateCookieChecked:(e,t,n,o)=>{const{consent:i,isGcm:r,groups:s,updateGcmConsentTypeChecked:a}=e;i[t]||(i[t]=[]);const c=i[t],l=c.indexOf(n);if(o&&-1===l?c.push(n):!o&&l>-1&&c.splice(l,1),c.length||delete i[t],r){const e=s.map((e=>{let{id:t,items:n}=e;return n.filter((e=>{let{id:n}=e;var o;return(null==(o=i[t])?void 0:o.indexOf(n))>-1}))})).flat();for(const i of s.find((e=>{let{id:n}=e;return n===t})).items.find((e=>{let{id:t}=e;return n===t})).googleConsentModeConsentTypes)o?a(i,!0):e.some((e=>{let{googleConsentModeConsentTypes:t}=e;return t.includes(i)}))||a(i,!1)}},updateGroupChecked:(e,t,n)=>{const{groups:o,updateCookieChecked:i}=e;for(const e of o.find((e=>{let{id:n}=e;return n===t})).items)i(t,e.id,n)}},[W,M]=(0,S.d)({...c,...n,blocker:void 0,recorder:F,productionNotice:(0,o.Y)(me.A,{isPro:v,isLicensed:m,isDevLicense:p,i18n:h}),pageRequestUuid4:l,iso3166OneAlpha2:d,gcmConsent:Y,tcf:void 0,tcfFilterBy:"legInt",poweredLink:t,visible:!1,skipOverlay:!0,previewCheckboxActiveState:!1,individualPrivacyOpen:!1,designVersion:u,i18n:h,keepVariablesInTexts:b,affiliate:y,consent:{...!1===A?{}:A.consent,...(0,a.C)().getDefaultDecision(!1===A)},onPersistConsent:O.x,onApplyConsent:()=>(0,a.C)().applyCookies({type:"consent"}),didGroupFirstChange:!1,fetchLazyLoadedDataForSecondView:L,suspense:{tcf:R,lazyLoadedDataForSecondView:I}},{...H,...J,fetchHistory:async()=>{const e=[];try{e.push(...await(0,C.h)({location:ge,options:{restNamespace:w,restRoot:k,restQuery:x,restNonce:N,restPathObfuscateOffset:D},cookieValueAsParam:[T],sendRestNonce:!1}))}catch(e){}for(const{createdClientTime:t}of B.getConsentQueue())e.unshift({created:new Date(t).toISOString(),isDoNotTrack:!1,isForwarded:!1,isUnblock:!1,context:void 0,id:new Date(t).getTime(),uuid:void 0});return e},onLanguageSwitch:(e,t)=>{let{url:n}=t;window.location.href=n}},{deps:[R]});var z,q,G,U,K;P(M),function(e,t){(0,i.vJ)((()=>{const n=()=>{const n=(0,g.y)(t);n&&e.set({consent:n.consent,isConsentGiven:!0})};return document.addEventListener(le.T,n),()=>{document.removeEventListener(le.T,n)}}),[])}(M,T);const V=(e=>{const t=(0,i.li)(!1),n=(0,i.li)(null),o=(0,i.li)(new Promise((e=>{})));return(0,i.vJ)((()=>t.current?()=>{}:(n.current=e,e.then((i=>{n.current!==e||t.current||(t.current=!0,o.current=Promise.resolve(i))})).catch((()=>{})),()=>{n.current=null})),[e]),o.current})(R);return(0,o.Y)(W,{value:M,children:(0,o.Y)(be,{promise:V,children:(0,o.Y)(we,{})})})}}}]);
//# sourceMappingURL=https://sourcemap.devowl.io/real-cookie-banner/4.7.15/b0e3b960ad7a805a19bcb91dd6e46618/banner-pro-banner-ui.pro.js.map