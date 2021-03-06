/*!
 * Braintree End-to-End Encryption Library
 * http://www.braintreepayments.com
 *
 * Licensed under the MIT or GPL Version 2 licenses.
 *
 * Includes pidCrypt
 * Copyright (c) 2009 pidder <www.pidder.com>
 * Released under the GPL License.
 *
 * Includes SJCL
 * Copyright 2009-2010 Emily Stark, Mike Hamburg, Dan Boneh, Stanford University.
 * Released under the GPL Version 2 License.
 */
/* eslint-disable */

Braintree=(function(){function pidCrypt(){function getRandomBytes(len){if(!len)len=8;var bytes=new Array(len);var field=[];for(var i=0;i<256;i++)field[i]=i;for(i=0;i<bytes.length;i++)
bytes[i]=field[Math.floor(Math.random()*field.length)];return bytes}
this.setDefaults=function(){this.params.nBits=256;this.params.salt=getRandomBytes(8);this.params.salt=pidCryptUtil.byteArray2String(this.params.salt);this.params.salt=pidCryptUtil.convertToHex(this.params.salt);this.params.blockSize=16;this.params.UTF8=true;this.params.A0_PAD=true;}
this.debug=true;this.params={};this.params.dataIn='';this.params.dataOut='';this.params.decryptIn='';this.params.decryptOut='';this.params.encryptIn='';this.params.encryptOut='';this.params.key='';this.params.iv='';this.params.clear=true;this.setDefaults();this.errors='';this.warnings='';this.infos='';this.debugMsg='';this.setParams=function(pObj){if(!pObj)pObj={};for(var p in pObj)
this.params[p]=pObj[p];}
this.getParams=function(){return this.params;}
this.getParam=function(p){return this.params[p]||'';}
this.clearParams=function(){this.params={};}
this.getNBits=function(){return this.params.nBits;}
this.getOutput=function(){return this.params.dataOut;}
this.setError=function(str){this.error=str;}
this.appendError=function(str){this.errors+=str;return'';}
this.getErrors=function(){return this.errors;}
this.isError=function(){if(this.errors.length>0)
return true;return false}
this.appendInfo=function(str){this.infos+=str;return'';}
this.getInfos=function()
{return this.infos;}
this.setDebug=function(flag){this.debug=flag;}
this.appendDebug=function(str)
{this.debugMsg+=str;return'';}
this.isDebug=function(){return this.debug;}
this.getAllMessages=function(options){var defaults={lf:'\n',clr_mes:false,verbose:15};if(!options)options=defaults;for(var d in defaults)
if(typeof(options[d])=='undefined')options[d]=defaults[d];var mes='';var tmp='';for(var p in this.params){switch(p){case'encryptOut':tmp=pidCryptUtil.toByteArray(this.params[p].toString());tmp=pidCryptUtil.fragment(tmp.join(),64,options.lf)
break;case'key':case'iv':tmp=pidCryptUtil.formatHex(this.params[p],48);break;default:tmp=pidCryptUtil.fragment(this.params[p].toString(),64,options.lf);}
mes+='<p><b>'+p+'</b>:<pre>'+tmp+'</pre></p>';}
if(this.debug)mes+='debug: '+this.debug+options.lf;if(this.errors.length>0&&((options.verbose&1)==1))mes+='Errors:'+options.lf+this.errors+options.lf;if(this.warnings.length>0&&((options.verbose&2)==2))mes+='Warnings:'+options.lf+this.warnings+options.lf;if(this.infos.length>0&&((options.verbose&4)==4))mes+='Infos:'+options.lf+this.infos+options.lf;if(this.debug&&((options.verbose&8)==8))mes+='Debug messages:'+options.lf+this.debugMsg+options.lf;if(options.clr_mes)
this.errors=this.infos=this.warnings=this.debug='';return mes;}
this.getRandomBytes=function(len){return getRandomBytes(len);}}
pidCryptUtil={};pidCryptUtil.encodeBase64=function(str,utf8encode){if(!str)str="";var b64="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";utf8encode=(typeof utf8encode=='undefined')?false:utf8encode;var o1,o2,o3,bits,h1,h2,h3,h4,e=[],pad='',c,plain,coded;plain=utf8encode?pidCryptUtil.encodeUTF8(str):str;c=plain.length%3;if(c>0){while(c++<3){pad+='=';plain+='\0';}}
for(c=0;c<plain.length;c+=3){o1=plain.charCodeAt(c);o2=plain.charCodeAt(c+1);o3=plain.charCodeAt(c+2);bits=o1<<16|o2<<8|o3;h1=bits>>18&0x3f;h2=bits>>12&0x3f;h3=bits>>6&0x3f;h4=bits&0x3f;e[c/3]=b64.charAt(h1)+b64.charAt(h2)+b64.charAt(h3)+b64.charAt(h4);}
coded=e.join('');coded=coded.slice(0,coded.length-pad.length)+pad;return coded;}
pidCryptUtil.decodeBase64=function(str,utf8decode){if(!str)str="";var b64="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";utf8decode=(typeof utf8decode=='undefined')?false:utf8decode;var o1,o2,o3,h1,h2,h3,h4,bits,d=[],plain,coded;coded=utf8decode?pidCryptUtil.decodeUTF8(str):str;for(var c=0;c<coded.length;c+=4){h1=b64.indexOf(coded.charAt(c));h2=b64.indexOf(coded.charAt(c+1));h3=b64.indexOf(coded.charAt(c+2));h4=b64.indexOf(coded.charAt(c+3));bits=h1<<18|h2<<12|h3<<6|h4;o1=bits>>>16&0xff;o2=bits>>>8&0xff;o3=bits&0xff;d[c/4]=String.fromCharCode(o1,o2,o3);if(h4==0x40)d[c/4]=String.fromCharCode(o1,o2);if(h3==0x40)d[c/4]=String.fromCharCode(o1);}
plain=d.join('');plain=utf8decode?pidCryptUtil.decodeUTF8(plain):plain
return plain;}
pidCryptUtil.encodeUTF8=function(str){if(!str)str="";str=str.replace(/[\u0080-\u07ff]/g,function(c){var cc=c.charCodeAt(0);return String.fromCharCode(0xc0|cc>>6,0x80|cc&0x3f);});str=str.replace(/[\u0800-\uffff]/g,function(c){var cc=c.charCodeAt(0);return String.fromCharCode(0xe0|cc>>12,0x80|cc>>6&0x3F,0x80|cc&0x3f);});return str;}
pidCryptUtil.decodeUTF8=function(str){if(!str)str="";str=str.replace(/[\u00c0-\u00df][\u0080-\u00bf]/g,function(c){var cc=(c.charCodeAt(0)&0x1f)<<6|c.charCodeAt(1)&0x3f;return String.fromCharCode(cc);});str=str.replace(/[\u00e0-\u00ef][\u0080-\u00bf][\u0080-\u00bf]/g,function(c){var cc=((c.charCodeAt(0)&0x0f)<<12)|((c.charCodeAt(1)&0x3f)<<6)|(c.charCodeAt(2)&0x3f);return String.fromCharCode(cc);});return str;}
pidCryptUtil.convertToHex=function(str){if(!str)str="";var hs='';var hv='';for(var i=0;i<str.length;i++){hv=str.charCodeAt(i).toString(16);hs+=(hv.length==1)?'0'+hv:hv;}
return hs;}
pidCryptUtil.convertFromHex=function(str){if(!str)str="";var s="";for(var i=0;i<str.length;i+=2){s+=String.fromCharCode(parseInt(str.substring(i,i+2),16));}
return s}
pidCryptUtil.stripLineFeeds=function(str){if(!str)str="";var s='';s=str.replace(/\n/g,'');s=s.replace(/\r/g,'');return s;}
pidCryptUtil.toByteArray=function(str){if(!str)str="";var ba=[];for(var i=0;i<str.length;i++)
ba[i]=str.charCodeAt(i);return ba;}
pidCryptUtil.fragment=function(str,length,lf){if(!str)str="";if(!length||length>=str.length)return str;if(!lf)lf='\n'
var tmp='';for(var i=0;i<str.length;i+=length)
tmp+=str.substr(i,length)+lf;return tmp;}
pidCryptUtil.formatHex=function(str,length){if(!str)str="";if(!length)length=45;var str_new='';var j=0;var hex=str.toLowerCase();for(var i=0;i<hex.length;i+=2)
str_new+=hex.substr(i,2)+':';hex=this.fragment(str_new,length);return hex;}
pidCryptUtil.byteArray2String=function(b){var s='';for(var i=0;i<b.length;i++){s+=String.fromCharCode(b[i]);}
return s;}
function Arcfour(){this.i=0;this.j=0;this.S=new Array();}
function ARC4init(key){var i,j,t;for(i=0;i<256;++i)
this.S[i]=i;j=0;for(i=0;i<256;++i){j=(j+this.S[i]+key[i%key.length])&255;t=this.S[i];this.S[i]=this.S[j];this.S[j]=t;}
this.i=0;this.j=0;}
function ARC4next(){var t;this.i=(this.i+1)&255;this.j=(this.j+this.S[this.i])&255;t=this.S[this.i];this.S[this.i]=this.S[this.j];this.S[this.j]=t;return this.S[(t+this.S[this.i])&255];}
Arcfour.prototype.init=ARC4init;Arcfour.prototype.next=ARC4next;function prng_newstate(){return new Arcfour();}
var rng_psize=256;function SecureRandom(){this.rng_state;this.rng_pool;this.rng_pptr;this.rng_seed_int=function(x){this.rng_pool[this.rng_pptr++]^=x&255;this.rng_pool[this.rng_pptr++]^=(x>>8)&255;this.rng_pool[this.rng_pptr++]^=(x>>16)&255;this.rng_pool[this.rng_pptr++]^=(x>>24)&255;if(this.rng_pptr>=rng_psize)this.rng_pptr-=rng_psize;}
this.rng_seed_time=function(){this.rng_seed_int(new Date().getTime());}
if(this.rng_pool==null){this.rng_pool=new Array();this.rng_pptr=0;var t;if(navigator.appName=="Netscape"&&navigator.appVersion<"5"&&window.crypto){var z=window.crypto.random(32);for(t=0;t<z.length;++t)
this.rng_pool[this.rng_pptr++]=z.charCodeAt(t)&255;}
while(this.rng_pptr<rng_psize){t=Math.floor(65536*Math.random());this.rng_pool[this.rng_pptr++]=t>>>8;this.rng_pool[this.rng_pptr++]=t&255;}
this.rng_pptr=0;this.rng_seed_time();}
this.rng_get_byte=function(){if(this.rng_state==null){this.rng_seed_time();this.rng_state=prng_newstate();this.rng_state.init(this.rng_pool);for(this.rng_pptr=0;this.rng_pptr<this.rng_pool.length;++this.rng_pptr)
this.rng_pool[this.rng_pptr]=0;this.rng_pptr=0;}
return this.rng_state.next();}
this.nextBytes=function(ba){var i;for(i=0;i<ba.length;++i)ba[i]=this.rng_get_byte();}}
function Stream(enc,pos){if(enc instanceof Stream){this.enc=enc.enc;this.pos=enc.pos;}else{this.enc=enc;this.pos=pos;}}
Stream.prototype.parseStringHex=function(start,end){if(typeof(end)=='undefined')end=this.enc.length;var s="";for(var i=start;i<end;++i){var h=this.get(i);s+=this.hexDigits.charAt(h>>4)+this.hexDigits.charAt(h&0xF);}
return s;}
Stream.prototype.get=function(pos){if(pos==undefined)
pos=this.pos++;if(pos>=this.enc.length)
throw'Requesting byte offset '+pos+' on a stream of length '+this.enc.length;return this.enc[pos];}
Stream.prototype.hexDigits="0123456789ABCDEF";Stream.prototype.hexDump=function(start,end){var s="";for(var i=start;i<end;++i){var h=this.get(i);s+=this.hexDigits.charAt(h>>4)+this.hexDigits.charAt(h&0xF);if((i&0xF)==0x7)
s+=' ';s+=((i&0xF)==0xF)?'\n':' ';}
return s;}
Stream.prototype.parseStringISO=function(start,end){var s="";for(var i=start;i<end;++i)
s+=String.fromCharCode(this.get(i));return s;}
Stream.prototype.parseStringUTF=function(start,end){var s="",c=0;for(var i=start;i<end;){var c=this.get(i++);if(c<128)
s+=String.fromCharCode(c);else
if((c>191)&&(c<224))
s+=String.fromCharCode(((c&0x1F)<<6)|(this.get(i++)&0x3F));else
s+=String.fromCharCode(((c&0x0F)<<12)|((this.get(i++)&0x3F)<<6)|(this.get(i++)&0x3F));}
return s;}
Stream.prototype.reTime=/^((?:1[89]|2\d)?\d\d)(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])([01]\d|2[0-3])(?:([0-5]\d)(?:([0-5]\d)(?:[.,](\d{1,3}))?)?)?(Z|[-+](?:[0]\d|1[0-2])([0-5]\d)?)?$/;Stream.prototype.parseTime=function(start,end){var s=this.parseStringISO(start,end);var m=this.reTime.exec(s);if(!m)
return"Unrecognized time: "+s;s=m[1]+"-"+m[2]+"-"+m[3]+" "+m[4];if(m[5]){s+=":"+m[5];if(m[6]){s+=":"+m[6];if(m[7])
s+="."+m[7];}}
if(m[8]){s+=" UTC";if(m[8]!='Z'){s+=m[8];if(m[9])
s+=":"+m[9];}}
return s;}
Stream.prototype.parseInteger=function(start,end){if((end-start)>4)
return undefined;var n=0;for(var i=start;i<end;++i)
n=(n<<8)|this.get(i);return n;}
Stream.prototype.parseOID=function(start,end){var s,n=0,bits=0;for(var i=start;i<end;++i){var v=this.get(i);n=(n<<7)|(v&0x7F);bits+=7;if(!(v&0x80)){if(s==undefined)
s=parseInt(n/40)+"."+(n%40);else
s+="."+((bits>=31)?"big":n);n=bits=0;}
s+=String.fromCharCode();}
return s;}
if(typeof(pidCrypt)!='undefined')
{pidCrypt.ASN1=function(stream,header,length,tag,sub){this.stream=stream;this.header=header;this.length=length;this.tag=tag;this.sub=sub;}
pidCrypt.ASN1.prototype.toHexTree=function(){var node={};node.type=this.typeName();if(node.type!='SEQUENCE')
node.value=this.stream.parseStringHex(this.posContent(),this.posEnd());if(this.sub!=null){node.sub=[];for(var i=0,max=this.sub.length;i<max;++i)
node.sub[i]=this.sub[i].toHexTree();}
return node;}
pidCrypt.ASN1.prototype.typeName=function(){if(this.tag==undefined)
return"unknown";var tagClass=this.tag>>6;var tagConstructed=(this.tag>>5)&1;var tagNumber=this.tag&0x1F;switch(tagClass){case 0:switch(tagNumber){case 0x00:return"EOC";case 0x01:return"BOOLEAN";case 0x02:return"INTEGER";case 0x03:return"BIT_STRING";case 0x04:return"OCTET_STRING";case 0x05:return"NULL";case 0x06:return"OBJECT_IDENTIFIER";case 0x07:return"ObjectDescriptor";case 0x08:return"EXTERNAL";case 0x09:return"REAL";case 0x0A:return"ENUMERATED";case 0x0B:return"EMBEDDED_PDV";case 0x0C:return"UTF8String";case 0x10:return"SEQUENCE";case 0x11:return"SET";case 0x12:return"NumericString";case 0x13:return"PrintableString";case 0x14:return"TeletexString";case 0x15:return"VideotexString";case 0x16:return"IA5String";case 0x17:return"UTCTime";case 0x18:return"GeneralizedTime";case 0x19:return"GraphicString";case 0x1A:return"VisibleString";case 0x1B:return"GeneralString";case 0x1C:return"UniversalString";case 0x1E:return"BMPString";default:return"Universal_"+tagNumber.toString(16);}
case 1:return"Application_"+tagNumber.toString(16);case 2:return"["+tagNumber+"]";case 3:return"Private_"+tagNumber.toString(16);}}
pidCrypt.ASN1.prototype.content=function(){if(this.tag==undefined)
return null;var tagClass=this.tag>>6;if(tagClass!=0)
return null;var tagNumber=this.tag&0x1F;var content=this.posContent();var len=Math.abs(this.length);switch(tagNumber){case 0x01:return(this.stream.get(content)==0)?"false":"true";case 0x02:return this.stream.parseInteger(content,content+len);case 0x06:return this.stream.parseOID(content,content+len);case 0x0C:return this.stream.parseStringUTF(content,content+len);case 0x12:case 0x13:case 0x14:case 0x15:case 0x16:case 0x1A:return this.stream.parseStringISO(content,content+len);case 0x17:case 0x18:return this.stream.parseTime(content,content+len);}
return null;}
pidCrypt.ASN1.prototype.toString=function(){return this.typeName()+"@"+this.stream.pos+"[header:"+this.header+",length:"+this.length+",sub:"+((this.sub==null)?'null':this.sub.length)+"]";}
pidCrypt.ASN1.prototype.print=function(indent){if(indent==undefined)indent='';document.writeln(indent+this);if(this.sub!=null){indent+='  ';for(var i=0,max=this.sub.length;i<max;++i)
this.sub[i].print(indent);}}
pidCrypt.ASN1.prototype.toPrettyString=function(indent){if(indent==undefined)indent='';var s=indent+this.typeName()+" @"+this.stream.pos;if(this.length>=0)
s+="+";s+=this.length;if(this.tag&0x20)
s+=" (constructed)";else
if(((this.tag==0x03)||(this.tag==0x04))&&(this.sub!=null))
s+=" (encapsulates)";s+="\n";if(this.sub!=null){indent+='  ';for(var i=0,max=this.sub.length;i<max;++i)
s+=this.sub[i].toPrettyString(indent);}
return s;}
pidCrypt.ASN1.prototype.toDOM=function(){var node=document.createElement("div");node.className="node";node.asn1=this;var head=document.createElement("div");head.className="head";var s=this.typeName();head.innerHTML=s;node.appendChild(head);this.head=head;var value=document.createElement("div");value.className="value";s="Offset: "+this.stream.pos+"<br/>";s+="Length: "+this.header+"+";if(this.length>=0)
s+=this.length;else
s+=(-this.length)+" (undefined)";if(this.tag&0x20)
s+="<br/>(constructed)";else if(((this.tag==0x03)||(this.tag==0x04))&&(this.sub!=null))
s+="<br/>(encapsulates)";var content=this.content();if(content!=null){s+="<br/>Value:<br/><b>"+content+"</b>";if((typeof(oids)=='object')&&(this.tag==0x06)){var oid=oids[content];if(oid){if(oid.d)s+="<br/>"+oid.d;if(oid.c)s+="<br/>"+oid.c;if(oid.w)s+="<br/>(warning!)";}}}
value.innerHTML=s;node.appendChild(value);var sub=document.createElement("div");sub.className="sub";if(this.sub!=null){for(var i=0,max=this.sub.length;i<max;++i)
sub.appendChild(this.sub[i].toDOM());}
node.appendChild(sub);head.switchNode=node;head.onclick=function(){var node=this.switchNode;node.className=(node.className=="node collapsed")?"node":"node collapsed";};return node;}
pidCrypt.ASN1.prototype.posStart=function(){return this.stream.pos;}
pidCrypt.ASN1.prototype.posContent=function(){return this.stream.pos+this.header;}
pidCrypt.ASN1.prototype.posEnd=function(){return this.stream.pos+this.header+Math.abs(this.length);}
pidCrypt.ASN1.prototype.toHexDOM_sub=function(node,className,stream,start,end){if(start>=end)
return;var sub=document.createElement("span");sub.className=className;sub.appendChild(document.createTextNode(stream.hexDump(start,end)));node.appendChild(sub);}
pidCrypt.ASN1.prototype.toHexDOM=function(){var node=document.createElement("span");node.className='hex';this.head.hexNode=node;this.head.onmouseover=function(){this.hexNode.className='hexCurrent';}
this.head.onmouseout=function(){this.hexNode.className='hex';}
this.toHexDOM_sub(node,"tag",this.stream,this.posStart(),this.posStart()+1);this.toHexDOM_sub(node,(this.length>=0)?"dlen":"ulen",this.stream,this.posStart()+1,this.posContent());if(this.sub==null)
node.appendChild(document.createTextNode(this.stream.hexDump(this.posContent(),this.posEnd())));else if(this.sub.length>0){var first=this.sub[0];var last=this.sub[this.sub.length-1];this.toHexDOM_sub(node,"intro",this.stream,this.posContent(),first.posStart());for(var i=0,max=this.sub.length;i<max;++i)
node.appendChild(this.sub[i].toHexDOM());this.toHexDOM_sub(node,"outro",this.stream,last.posEnd(),this.posEnd());}
return node;}
pidCrypt.ASN1.decodeLength=function(stream){var buf=stream.get();var len=buf&0x7F;if(len==buf)
return len;if(len>3)
throw"Length over 24 bits not supported at position "+(stream.pos-1);if(len==0)
return-1;buf=0;for(var i=0;i<len;++i)
buf=(buf<<8)|stream.get();return buf;}
pidCrypt.ASN1.hasContent=function(tag,len,stream){if(tag&0x20)
return true;if((tag<0x03)||(tag>0x04))
return false;var p=new Stream(stream);if(tag==0x03)p.get();var subTag=p.get();if((subTag>>6)&0x01)
return false;try{var subLength=pidCrypt.ASN1.decodeLength(p);return((p.pos-stream.pos)+subLength==len);}catch(exception){return false;}}
pidCrypt.ASN1.decode=function(stream){if(!(stream instanceof Stream))
stream=new Stream(stream,0);var streamStart=new Stream(stream);var tag=stream.get();var len=pidCrypt.ASN1.decodeLength(stream);var header=stream.pos-streamStart.pos;var sub=null;if(pidCrypt.ASN1.hasContent(tag,len,stream)){var start=stream.pos;if(tag==0x03)stream.get();sub=[];if(len>=0){var end=start+len;while(stream.pos<end)
sub[sub.length]=pidCrypt.ASN1.decode(stream);if(stream.pos!=end)
throw"Content size is not correct for container starting at offset "+start;}else{try{for(;;){var s=pidCrypt.ASN1.decode(stream);if(s.tag==0)
break;sub[sub.length]=s;}
len=start-stream.pos;}catch(e){throw"Exception while decoding undefined length content: "+e;}}}else
stream.pos+=len;return new pidCrypt.ASN1(streamStart,header,len,tag,sub);}
pidCrypt.ASN1.test=function(){var test=[{value:[0x27],expected:0x27},{value:[0x81,0xC9],expected:0xC9},{value:[0x83,0xFE,0xDC,0xBA],expected:0xFEDCBA}];for(var i=0,max=test.length;i<max;++i){var pos=0;var stream=new Stream(test[i].value,0);var res=pidCrypt.ASN1.decodeLength(stream);if(res!=test[i].expected)
document.write("In test["+i+"] expected "+test[i].expected+" got "+res+"\n");}}}
var dbits;var canary=0xdeadbeefcafe;var j_lm=((canary&0xffffff)==0xefcafe);function BigInteger(a,b,c){if(a!=null)
if("number"==typeof a)this.fromNumber(a,b,c);else if(b==null&&"string"!=typeof a)this.fromString(a,256);else this.fromString(a,b);}
function nbi(){return new BigInteger(null);}
function am1(i,x,w,j,c,n){while(--n>=0){var v=x*this[i++]+w[j]+c;c=Math.floor(v/0x4000000);w[j++]=v&0x3ffffff;}
return c;}
function am2(i,x,w,j,c,n){var xl=x&0x7fff,xh=x>>15;while(--n>=0){var l=this[i]&0x7fff;var h=this[i++]>>15;var m=xh*l+h*xl;l=xl*l+((m&0x7fff)<<15)+w[j]+(c&0x3fffffff);c=(l>>>30)+(m>>>15)+xh*h+(c>>>30);w[j++]=l&0x3fffffff;}
return c;}
function am3(i,x,w,j,c,n){var xl=x&0x3fff,xh=x>>14;while(--n>=0){var l=this[i]&0x3fff;var h=this[i++]>>14;var m=xh*l+h*xl;l=xl*l+((m&0x3fff)<<14)+w[j]+c;c=(l>>28)+(m>>14)+xh*h;w[j++]=l&0xfffffff;}
return c;}
if(j_lm&&(navigator.appName=="Microsoft Internet Explorer")){BigInteger.prototype.am=am2;dbits=30;}
else if(j_lm&&(navigator.appName!="Netscape")){BigInteger.prototype.am=am1;dbits=26;}
else{BigInteger.prototype.am=am3;dbits=28;}
BigInteger.prototype.DB=dbits;BigInteger.prototype.DM=((1<<dbits)-1);BigInteger.prototype.DV=(1<<dbits);var BI_FP=52;BigInteger.prototype.FV=Math.pow(2,BI_FP);BigInteger.prototype.F1=BI_FP-dbits;BigInteger.prototype.F2=2*dbits-BI_FP;var BI_RM="0123456789abcdefghijklmnopqrstuvwxyz";var BI_RC=new Array();var rr,vv;rr="0".charCodeAt(0);for(vv=0;vv<=9;++vv)BI_RC[rr++]=vv;rr="a".charCodeAt(0);for(vv=10;vv<36;++vv)BI_RC[rr++]=vv;rr="A".charCodeAt(0);for(vv=10;vv<36;++vv)BI_RC[rr++]=vv;function int2char(n){return BI_RM.charAt(n);}
function intAt(s,i){var c=BI_RC[s.charCodeAt(i)];return(c==null)?-1:c;}
function bnpCopyTo(r){for(var i=this.t-1;i>=0;--i)r[i]=this[i];r.t=this.t;r.s=this.s;}
function bnpFromInt(x){this.t=1;this.s=(x<0)?-1:0;if(x>0)this[0]=x;else if(x<-1)this[0]=x+DV;else this.t=0;}
function nbv(i){var r=nbi();r.fromInt(i);return r;}
function bnpFromString(s,b){var k;if(b==16)k=4;else if(b==8)k=3;else if(b==256)k=8;else if(b==2)k=1;else if(b==32)k=5;else if(b==4)k=2;else{this.fromRadix(s,b);return;}
this.t=0;this.s=0;var i=s.length,mi=false,sh=0;while(--i>=0){var x=(k==8)?s[i]&0xff:intAt(s,i);if(x<0){if(s.charAt(i)=="-")mi=true;continue;}
mi=false;if(sh==0)
this[this.t++]=x;else if(sh+k>this.DB){this[this.t-1]|=(x&((1<<(this.DB-sh))-1))<<sh;this[this.t++]=(x>>(this.DB-sh));}
else
this[this.t-1]|=x<<sh;sh+=k;if(sh>=this.DB)sh-=this.DB;}
if(k==8&&(s[0]&0x80)!=0){this.s=-1;if(sh>0)this[this.t-1]|=((1<<(this.DB-sh))-1)<<sh;}
this.clamp();if(mi)BigInteger.ZERO.subTo(this,this);}
function bnpClamp(){var c=this.s&this.DM;while(this.t>0&&this[this.t-1]==c)--this.t;}
function bnToString(b){if(this.s<0)return"-"+this.negate().toString(b);var k;if(b==16)k=4;else if(b==8)k=3;else if(b==2)k=1;else if(b==32)k=5;else if(b==4)k=2;else return this.toRadix(b);var km=(1<<k)-1,d,m=false,r="",i=this.t;var p=this.DB-(i*this.DB)%k;if(i-->0){if(p<this.DB&&(d=this[i]>>p)>0){m=true;r=int2char(d);}
while(i>=0){if(p<k){d=(this[i]&((1<<p)-1))<<(k-p);d|=this[--i]>>(p+=this.DB-k);}
else{d=(this[i]>>(p-=k))&km;if(p<=0){p+=this.DB;--i;}}
if(d>0)m=true;if(m)r+=int2char(d);}}
return m?r:"0";}
function bnNegate(){var r=nbi();BigInteger.ZERO.subTo(this,r);return r;}
function bnAbs(){return(this.s<0)?this.negate():this;}
function bnCompareTo(a){var r=this.s-a.s;if(r!=0)return r;var i=this.t;r=i-a.t;if(r!=0)return r;while(--i>=0)if((r=this[i]-a[i])!=0)return r;return 0;}
function nbits(x){var r=1,t;if((t=x>>>16)!=0){x=t;r+=16;}
if((t=x>>8)!=0){x=t;r+=8;}
if((t=x>>4)!=0){x=t;r+=4;}
if((t=x>>2)!=0){x=t;r+=2;}
if((t=x>>1)!=0){x=t;r+=1;}
return r;}
function bnBitLength(){if(this.t<=0)return 0;return this.DB*(this.t-1)+nbits(this[this.t-1]^(this.s&this.DM));}
function bnpDLShiftTo(n,r){var i;for(i=this.t-1;i>=0;--i)r[i+n]=this[i];for(i=n-1;i>=0;--i)r[i]=0;r.t=this.t+n;r.s=this.s;}
function bnpDRShiftTo(n,r){for(var i=n;i<this.t;++i)r[i-n]=this[i];r.t=Math.max(this.t-n,0);r.s=this.s;}
function bnpLShiftTo(n,r){var bs=n%this.DB;var cbs=this.DB-bs;var bm=(1<<cbs)-1;var ds=Math.floor(n/this.DB),c=(this.s<<bs)&this.DM,i;for(i=this.t-1;i>=0;--i){r[i+ds+1]=(this[i]>>cbs)|c;c=(this[i]&bm)<<bs;}
for(i=ds-1;i>=0;--i)r[i]=0;r[ds]=c;r.t=this.t+ds+1;r.s=this.s;r.clamp();}
function bnpRShiftTo(n,r){r.s=this.s;var ds=Math.floor(n/this.DB);if(ds>=this.t){r.t=0;return;}
var bs=n%this.DB;var cbs=this.DB-bs;var bm=(1<<bs)-1;r[0]=this[ds]>>bs;for(var i=ds+1;i<this.t;++i){r[i-ds-1]|=(this[i]&bm)<<cbs;r[i-ds]=this[i]>>bs;}
if(bs>0)r[this.t-ds-1]|=(this.s&bm)<<cbs;r.t=this.t-ds;r.clamp();}
function bnpSubTo(a,r){var i=0,c=0,m=Math.min(a.t,this.t);while(i<m){c+=this[i]-a[i];r[i++]=c&this.DM;c>>=this.DB;}
if(a.t<this.t){c-=a.s;while(i<this.t){c+=this[i];r[i++]=c&this.DM;c>>=this.DB;}
c+=this.s;}
else{c+=this.s;while(i<a.t){c-=a[i];r[i++]=c&this.DM;c>>=this.DB;}
c-=a.s;}
r.s=(c<0)?-1:0;if(c<-1)r[i++]=this.DV+c;else if(c>0)r[i++]=c;r.t=i;r.clamp();}
function bnpMultiplyTo(a,r){var x=this.abs(),y=a.abs();var i=x.t;r.t=i+y.t;while(--i>=0)r[i]=0;for(i=0;i<y.t;++i)r[i+x.t]=x.am(0,y[i],r,i,0,x.t);r.s=0;r.clamp();if(this.s!=a.s)BigInteger.ZERO.subTo(r,r);}
function bnpSquareTo(r){var x=this.abs();var i=r.t=2*x.t;while(--i>=0)r[i]=0;for(i=0;i<x.t-1;++i){var c=x.am(i,x[i],r,2*i,0,1);if((r[i+x.t]+=x.am(i+1,2*x[i],r,2*i+1,c,x.t-i-1))>=x.DV){r[i+x.t]-=x.DV;r[i+x.t+1]=1;}}
if(r.t>0)r[r.t-1]+=x.am(i,x[i],r,2*i,0,1);r.s=0;r.clamp();}
function bnpDivRemTo(m,q,r){var pm=m.abs();if(pm.t<=0)return;var pt=this.abs();if(pt.t<pm.t){if(q!=null)q.fromInt(0);if(r!=null)this.copyTo(r);return;}
if(r==null)r=nbi();var y=nbi(),ts=this.s,ms=m.s;var nsh=this.DB-nbits(pm[pm.t-1]);if(nsh>0){pm.lShiftTo(nsh,y);pt.lShiftTo(nsh,r);}
else{pm.copyTo(y);pt.copyTo(r);}
var ys=y.t;var y0=y[ys-1];if(y0==0)return;var yt=y0*(1<<this.F1)+((ys>1)?y[ys-2]>>this.F2:0);var d1=this.FV/yt,d2=(1<<this.F1)/yt,e=1<<this.F2;var i=r.t,j=i-ys,t=(q==null)?nbi():q;y.dlShiftTo(j,t);if(r.compareTo(t)>=0){r[r.t++]=1;r.subTo(t,r);}
BigInteger.ONE.dlShiftTo(ys,t);t.subTo(y,y);while(y.t<ys)y[y.t++]=0;while(--j>=0){var qd=(r[--i]==y0)?this.DM:Math.floor(r[i]*d1+(r[i-1]+e)*d2);if((r[i]+=y.am(0,qd,r,j,0,ys))<qd){y.dlShiftTo(j,t);r.subTo(t,r);while(r[i]<--qd)r.subTo(t,r);}}
if(q!=null){r.drShiftTo(ys,q);if(ts!=ms)BigInteger.ZERO.subTo(q,q);}
r.t=ys;r.clamp();if(nsh>0)r.rShiftTo(nsh,r);if(ts<0)BigInteger.ZERO.subTo(r,r);}
function bnMod(a){var r=nbi();this.abs().divRemTo(a,null,r);if(this.s<0&&r.compareTo(BigInteger.ZERO)>0)a.subTo(r,r);return r;}
function Classic(m){this.m=m;}
function cConvert(x){if(x.s<0||x.compareTo(this.m)>=0)return x.mod(this.m);else return x;}
function cRevert(x){return x;}
function cReduce(x){x.divRemTo(this.m,null,x);}
function cMulTo(x,y,r){x.multiplyTo(y,r);this.reduce(r);}
function cSqrTo(x,r){x.squareTo(r);this.reduce(r);}
Classic.prototype.convert=cConvert;Classic.prototype.revert=cRevert;Classic.prototype.reduce=cReduce;Classic.prototype.mulTo=cMulTo;Classic.prototype.sqrTo=cSqrTo;function bnpInvDigit(){if(this.t<1)return 0;var x=this[0];if((x&1)==0)return 0;var y=x&3;y=(y*(2-(x&0xf)*y))&0xf;y=(y*(2-(x&0xff)*y))&0xff;y=(y*(2-(((x&0xffff)*y)&0xffff)))&0xffff;y=(y*(2-x*y%this.DV))%this.DV;return(y>0)?this.DV-y:-y;}
function Montgomery(m){this.m=m;this.mp=m.invDigit();this.mpl=this.mp&0x7fff;this.mph=this.mp>>15;this.um=(1<<(m.DB-15))-1;this.mt2=2*m.t;}
function montConvert(x){var r=nbi();x.abs().dlShiftTo(this.m.t,r);r.divRemTo(this.m,null,r);if(x.s<0&&r.compareTo(BigInteger.ZERO)>0)this.m.subTo(r,r);return r;}
function montRevert(x){var r=nbi();x.copyTo(r);this.reduce(r);return r;}
function montReduce(x){while(x.t<=this.mt2)
x[x.t++]=0;for(var i=0;i<this.m.t;++i){var j=x[i]&0x7fff;var u0=(j*this.mpl+(((j*this.mph+(x[i]>>15)*this.mpl)&this.um)<<15))&x.DM;j=i+this.m.t;x[j]+=this.m.am(0,u0,x,i,0,this.m.t);while(x[j]>=x.DV){x[j]-=x.DV;x[++j]++;}}
x.clamp();x.drShiftTo(this.m.t,x);if(x.compareTo(this.m)>=0)x.subTo(this.m,x);}
function montSqrTo(x,r){x.squareTo(r);this.reduce(r);}
function montMulTo(x,y,r){x.multiplyTo(y,r);this.reduce(r);}
Montgomery.prototype.convert=montConvert;Montgomery.prototype.revert=montRevert;Montgomery.prototype.reduce=montReduce;Montgomery.prototype.mulTo=montMulTo;Montgomery.prototype.sqrTo=montSqrTo;function bnpIsEven(){return((this.t>0)?(this[0]&1):this.s)==0;}
function bnpExp(e,z){if(e>0xffffffff||e<1)return BigInteger.ONE;var r=nbi(),r2=nbi(),g=z.convert(this),i=nbits(e)-1;g.copyTo(r);while(--i>=0){z.sqrTo(r,r2);if((e&(1<<i))>0)z.mulTo(r2,g,r);else{var t=r;r=r2;r2=t;}}
return z.revert(r);}
function bnModPowInt(e,m){var z;if(e<256||m.isEven())z=new Classic(m);else z=new Montgomery(m);return this.exp(e,z);}
BigInteger.prototype.copyTo=bnpCopyTo;BigInteger.prototype.fromInt=bnpFromInt;BigInteger.prototype.fromString=bnpFromString;BigInteger.prototype.clamp=bnpClamp;BigInteger.prototype.dlShiftTo=bnpDLShiftTo;BigInteger.prototype.drShiftTo=bnpDRShiftTo;BigInteger.prototype.lShiftTo=bnpLShiftTo;BigInteger.prototype.rShiftTo=bnpRShiftTo;BigInteger.prototype.subTo=bnpSubTo;BigInteger.prototype.multiplyTo=bnpMultiplyTo;BigInteger.prototype.squareTo=bnpSquareTo;BigInteger.prototype.divRemTo=bnpDivRemTo;BigInteger.prototype.invDigit=bnpInvDigit;BigInteger.prototype.isEven=bnpIsEven;BigInteger.prototype.exp=bnpExp;BigInteger.prototype.toString=bnToString;BigInteger.prototype.negate=bnNegate;BigInteger.prototype.abs=bnAbs;BigInteger.prototype.compareTo=bnCompareTo;BigInteger.prototype.bitLength=bnBitLength;BigInteger.prototype.mod=bnMod;BigInteger.prototype.modPowInt=bnModPowInt;BigInteger.ZERO=nbv(0);BigInteger.ONE=nbv(1);function bnClone(){var r=nbi();this.copyTo(r);return r;}
function bnIntValue(){if(this.s<0){if(this.t==1)return this[0]-this.DV;else if(this.t==0)return-1;}
else if(this.t==1)return this[0];else if(this.t==0)return 0;return((this[1]&((1<<(32-this.DB))-1))<<this.DB)|this[0];}
function bnByteValue(){return(this.t==0)?this.s:(this[0]<<24)>>24;}
function bnShortValue(){return(this.t==0)?this.s:(this[0]<<16)>>16;}
function bnpChunkSize(r){return Math.floor(Math.LN2*this.DB/Math.log(r));}
function bnSigNum(){if(this.s<0)return-1;else if(this.t<=0||(this.t==1&&this[0]<=0))return 0;else return 1;}
function bnpToRadix(b){if(b==null)b=10;if(this.signum()==0||b<2||b>36)return"0";var cs=this.chunkSize(b);var a=Math.pow(b,cs);var d=nbv(a),y=nbi(),z=nbi(),r="";this.divRemTo(d,y,z);while(y.signum()>0){r=(a+z.intValue()).toString(b).substr(1)+r;y.divRemTo(d,y,z);}
return z.intValue().toString(b)+r;}
function bnpFromRadix(s,b){this.fromInt(0);if(b==null)b=10;var cs=this.chunkSize(b);var d=Math.pow(b,cs),mi=false,j=0,w=0;for(var i=0;i<s.length;++i){var x=intAt(s,i);if(x<0){if(s.charAt(i)=="-"&&this.signum()==0)mi=true;continue;}
w=b*w+x;if(++j>=cs){this.dMultiply(d);this.dAddOffset(w,0);j=0;w=0;}}
if(j>0){this.dMultiply(Math.pow(b,j));this.dAddOffset(w,0);}
if(mi)BigInteger.ZERO.subTo(this,this);}
function bnpFromNumber(a,b,c){if("number"==typeof b){if(a<2)this.fromInt(1);else{this.fromNumber(a,c);if(!this.testBit(a-1))
this.bitwiseTo(BigInteger.ONE.shiftLeft(a-1),op_or,this);if(this.isEven())this.dAddOffset(1,0);while(!this.isProbablePrime(b)){this.dAddOffset(2,0);if(this.bitLength()>a)this.subTo(BigInteger.ONE.shiftLeft(a-1),this);}}}
else{var x=new Array(),t=a&7;x.length=(a>>3)+1;b.nextBytes(x);if(t>0)x[0]&=((1<<t)-1);else x[0]=0;this.fromString(x,256);}}
function bnToByteArray(){var i=this.t,r=new Array();r[0]=this.s;var p=this.DB-(i*this.DB)%8,d,k=0;if(i-->0){if(p<this.DB&&(d=this[i]>>p)!=(this.s&this.DM)>>p)
r[k++]=d|(this.s<<(this.DB-p));while(i>=0){if(p<8){d=(this[i]&((1<<p)-1))<<(8-p);d|=this[--i]>>(p+=this.DB-8);}
else{d=(this[i]>>(p-=8))&0xff;if(p<=0){p+=this.DB;--i;}}
if((d&0x80)!=0)d|=-256;if(k==0&&(this.s&0x80)!=(d&0x80))++k;if(k>0||d!=this.s)r[k++]=d;}}
return r;}
function bnEquals(a){return(this.compareTo(a)==0);}
function bnMin(a){return(this.compareTo(a)<0)?this:a;}
function bnMax(a){return(this.compareTo(a)>0)?this:a;}
function bnpBitwiseTo(a,op,r){var i,f,m=Math.min(a.t,this.t);for(i=0;i<m;++i)r[i]=op(this[i],a[i]);if(a.t<this.t){f=a.s&this.DM;for(i=m;i<this.t;++i)r[i]=op(this[i],f);r.t=this.t;}
else{f=this.s&this.DM;for(i=m;i<a.t;++i)r[i]=op(f,a[i]);r.t=a.t;}
r.s=op(this.s,a.s);r.clamp();}
function op_and(x,y){return x&y;}
function bnAnd(a){var r=nbi();this.bitwiseTo(a,op_and,r);return r;}
function op_or(x,y){return x|y;}
function bnOr(a){var r=nbi();this.bitwiseTo(a,op_or,r);return r;}
function op_xor(x,y){return x^y;}
function bnXor(a){var r=nbi();this.bitwiseTo(a,op_xor,r);return r;}
function op_andnot(x,y){return x&~y;}
function bnAndNot(a){var r=nbi();this.bitwiseTo(a,op_andnot,r);return r;}
function bnNot(){var r=nbi();for(var i=0;i<this.t;++i)r[i]=this.DM&~this[i];r.t=this.t;r.s=~this.s;return r;}
function bnShiftLeft(n){var r=nbi();if(n<0)this.rShiftTo(-n,r);else this.lShiftTo(n,r);return r;}
function bnShiftRight(n){var r=nbi();if(n<0)this.lShiftTo(-n,r);else this.rShiftTo(n,r);return r;}
function lbit(x){if(x==0)return-1;var r=0;if((x&0xffff)==0){x>>=16;r+=16;}
if((x&0xff)==0){x>>=8;r+=8;}
if((x&0xf)==0){x>>=4;r+=4;}
if((x&3)==0){x>>=2;r+=2;}
if((x&1)==0)++r;return r;}
function bnGetLowestSetBit(){for(var i=0;i<this.t;++i)
if(this[i]!=0)return i*this.DB+lbit(this[i]);if(this.s<0)return this.t*this.DB;return-1;}
function cbit(x){var r=0;while(x!=0){x&=x-1;++r;}
return r;}
function bnBitCount(){var r=0,x=this.s&this.DM;for(var i=0;i<this.t;++i)r+=cbit(this[i]^x);return r;}
function bnTestBit(n){var j=Math.floor(n/this.DB);if(j>=this.t)return(this.s!=0);return((this[j]&(1<<(n%this.DB)))!=0);}
function bnpChangeBit(n,op){var r=BigInteger.ONE.shiftLeft(n);this.bitwiseTo(r,op,r);return r;}
function bnSetBit(n){return this.changeBit(n,op_or);}
function bnClearBit(n){return this.changeBit(n,op_andnot);}
function bnFlipBit(n){return this.changeBit(n,op_xor);}
function bnpAddTo(a,r){var i=0,c=0,m=Math.min(a.t,this.t);while(i<m){c+=this[i]+a[i];r[i++]=c&this.DM;c>>=this.DB;}
if(a.t<this.t){c+=a.s;while(i<this.t){c+=this[i];r[i++]=c&this.DM;c>>=this.DB;}
c+=this.s;}
else{c+=this.s;while(i<a.t){c+=a[i];r[i++]=c&this.DM;c>>=this.DB;}
c+=a.s;}
r.s=(c<0)?-1:0;if(c>0)r[i++]=c;else if(c<-1)r[i++]=this.DV+c;r.t=i;r.clamp();}
function bnAdd(a){var r=nbi();this.addTo(a,r);return r;}
function bnSubtract(a){var r=nbi();this.subTo(a,r);return r;}
function bnMultiply(a){var r=nbi();this.multiplyTo(a,r);return r;}
function bnDivide(a){var r=nbi();this.divRemTo(a,r,null);return r;}
function bnRemainder(a){var r=nbi();this.divRemTo(a,null,r);return r;}
function bnDivideAndRemainder(a){var q=nbi(),r=nbi();this.divRemTo(a,q,r);return new Array(q,r);}
function bnpDMultiply(n){this[this.t]=this.am(0,n-1,this,0,0,this.t);++this.t;this.clamp();}
function bnpDAddOffset(n,w){while(this.t<=w)this[this.t++]=0;this[w]+=n;while(this[w]>=this.DV){this[w]-=this.DV;if(++w>=this.t)this[this.t++]=0;++this[w];}}
function NullExp(){}
function nNop(x){return x;}
function nMulTo(x,y,r){x.multiplyTo(y,r);}
function nSqrTo(x,r){x.squareTo(r);}
NullExp.prototype.convert=nNop;NullExp.prototype.revert=nNop;NullExp.prototype.mulTo=nMulTo;NullExp.prototype.sqrTo=nSqrTo;function bnPow(e){return this.exp(e,new NullExp());}
function bnpMultiplyLowerTo(a,n,r){var i=Math.min(this.t+a.t,n);r.s=0;r.t=i;while(i>0)r[--i]=0;var j;for(j=r.t-this.t;i<j;++i)r[i+this.t]=this.am(0,a[i],r,i,0,this.t);for(j=Math.min(a.t,n);i<j;++i)this.am(0,a[i],r,i,0,n-i);r.clamp();}
function bnpMultiplyUpperTo(a,n,r){--n;var i=r.t=this.t+a.t-n;r.s=0;while(--i>=0)r[i]=0;for(i=Math.max(n-this.t,0);i<a.t;++i)
r[this.t+i-n]=this.am(n-i,a[i],r,0,0,this.t+i-n);r.clamp();r.drShiftTo(1,r);}
function Barrett(m){this.r2=nbi();this.q3=nbi();BigInteger.ONE.dlShiftTo(2*m.t,this.r2);this.mu=this.r2.divide(m);this.m=m;}
function barrettConvert(x){if(x.s<0||x.t>2*this.m.t)return x.mod(this.m);else if(x.compareTo(this.m)<0)return x;else{var r=nbi();x.copyTo(r);this.reduce(r);return r;}}
function barrettRevert(x){return x;}
function barrettReduce(x){x.drShiftTo(this.m.t-1,this.r2);if(x.t>this.m.t+1){x.t=this.m.t+1;x.clamp();}
this.mu.multiplyUpperTo(this.r2,this.m.t+1,this.q3);this.m.multiplyLowerTo(this.q3,this.m.t+1,this.r2);while(x.compareTo(this.r2)<0)x.dAddOffset(1,this.m.t+1);x.subTo(this.r2,x);while(x.compareTo(this.m)>=0)x.subTo(this.m,x);}
function barrettSqrTo(x,r){x.squareTo(r);this.reduce(r);}
function barrettMulTo(x,y,r){x.multiplyTo(y,r);this.reduce(r);}
Barrett.prototype.convert=barrettConvert;Barrett.prototype.revert=barrettRevert;Barrett.prototype.reduce=barrettReduce;Barrett.prototype.mulTo=barrettMulTo;Barrett.prototype.sqrTo=barrettSqrTo;function bnModPow(e,m){var i=e.bitLength(),k,r=nbv(1),z;if(i<=0)return r;else if(i<18)k=1;else if(i<48)k=3;else if(i<144)k=4;else if(i<768)k=5;else k=6;if(i<8)
z=new Classic(m);else if(m.isEven())
z=new Barrett(m);else
z=new Montgomery(m);var g=new Array(),n=3,k1=k-1,km=(1<<k)-1;g[1]=z.convert(this);if(k>1){var g2=nbi();z.sqrTo(g[1],g2);while(n<=km){g[n]=nbi();z.mulTo(g2,g[n-2],g[n]);n+=2;}}
var j=e.t-1,w,is1=true,r2=nbi(),t;i=nbits(e[j])-1;while(j>=0){if(i>=k1)w=(e[j]>>(i-k1))&km;else{w=(e[j]&((1<<(i+1))-1))<<(k1-i);if(j>0)w|=e[j-1]>>(this.DB+i-k1);}
n=k;while((w&1)==0){w>>=1;--n;}
if((i-=n)<0){i+=this.DB;--j;}
if(is1){g[w].copyTo(r);is1=false;}
else{while(n>1){z.sqrTo(r,r2);z.sqrTo(r2,r);n-=2;}
if(n>0)z.sqrTo(r,r2);else{t=r;r=r2;r2=t;}
z.mulTo(r2,g[w],r);}
while(j>=0&&(e[j]&(1<<i))==0){z.sqrTo(r,r2);t=r;r=r2;r2=t;if(--i<0){i=this.DB-1;--j;}}}
return z.revert(r);}
function bnGCD(a){var x=(this.s<0)?this.negate():this.clone();var y=(a.s<0)?a.negate():a.clone();if(x.compareTo(y)<0){var t=x;x=y;y=t;}
var i=x.getLowestSetBit(),g=y.getLowestSetBit();if(g<0)return x;if(i<g)g=i;if(g>0){x.rShiftTo(g,x);y.rShiftTo(g,y);}
while(x.signum()>0){if((i=x.getLowestSetBit())>0)x.rShiftTo(i,x);if((i=y.getLowestSetBit())>0)y.rShiftTo(i,y);if(x.compareTo(y)>=0){x.subTo(y,x);x.rShiftTo(1,x);}
else{y.subTo(x,y);y.rShiftTo(1,y);}}
if(g>0)y.lShiftTo(g,y);return y;}
function bnpModInt(n){if(n<=0)return 0;var d=this.DV%n,r=(this.s<0)?n-1:0;if(this.t>0)
if(d==0)r=this[0]%n;else for(var i=this.t-1;i>=0;--i)r=(d*r+this[i])%n;return r;}
function bnModInverse(m){var ac=m.isEven();if((this.isEven()&&ac)||m.signum()==0)return BigInteger.ZERO;var u=m.clone(),v=this.clone();var a=nbv(1),b=nbv(0),c=nbv(0),d=nbv(1);while(u.signum()!=0){while(u.isEven()){u.rShiftTo(1,u);if(ac){if(!a.isEven()||!b.isEven()){a.addTo(this,a);b.subTo(m,b);}
a.rShiftTo(1,a);}
else if(!b.isEven())b.subTo(m,b);b.rShiftTo(1,b);}
while(v.isEven()){v.rShiftTo(1,v);if(ac){if(!c.isEven()||!d.isEven()){c.addTo(this,c);d.subTo(m,d);}
c.rShiftTo(1,c);}
else if(!d.isEven())d.subTo(m,d);d.rShiftTo(1,d);}
if(u.compareTo(v)>=0){u.subTo(v,u);if(ac)a.subTo(c,a);b.subTo(d,b);}
else{v.subTo(u,v);if(ac)c.subTo(a,c);d.subTo(b,d);}}
if(v.compareTo(BigInteger.ONE)!=0)return BigInteger.ZERO;if(d.compareTo(m)>=0)return d.subtract(m);if(d.signum()<0)d.addTo(m,d);else return d;if(d.signum()<0)return d.add(m);else return d;}
var lowprimes=[2,3,5,7,11,13,17,19,23,29,31,37,41,43,47,53,59,61,67,71,73,79,83,89,97,101,103,107,109,113,127,131,137,139,149,151,157,163,167,173,179,181,191,193,197,199,211,223,227,229,233,239,241,251,257,263,269,271,277,281,283,293,307,311,313,317,331,337,347,349,353,359,367,373,379,383,389,397,401,409,419,421,431,433,439,443,449,457,461,463,467,479,487,491,499,503,509];var lplim=(1<<26)/lowprimes[lowprimes.length-1];function bnIsProbablePrime(t){var i,x=this.abs();if(x.t==1&&x[0]<=lowprimes[lowprimes.length-1]){for(i=0;i<lowprimes.length;++i)
if(x[0]==lowprimes[i])return true;return false;}
if(x.isEven())return false;i=1;while(i<lowprimes.length){var m=lowprimes[i],j=i+1;while(j<lowprimes.length&&m<lplim)m*=lowprimes[j++];m=x.modInt(m);while(i<j)if(m%lowprimes[i++]==0)return false;}
return x.millerRabin(t);}
function bnpMillerRabin(t){var n1=this.subtract(BigInteger.ONE);var k=n1.getLowestSetBit();if(k<=0)return false;var r=n1.shiftRight(k);t=(t+1)>>1;if(t>lowprimes.length)t=lowprimes.length;var a=nbi();for(var i=0;i<t;++i){a.fromInt(lowprimes[i]);var y=a.modPow(r,this);if(y.compareTo(BigInteger.ONE)!=0&&y.compareTo(n1)!=0){var j=1;while(j++<k&&y.compareTo(n1)!=0){y=y.modPowInt(2,this);if(y.compareTo(BigInteger.ONE)==0)return false;}
if(y.compareTo(n1)!=0)return false;}}
return true;}
BigInteger.prototype.chunkSize=bnpChunkSize;BigInteger.prototype.toRadix=bnpToRadix;BigInteger.prototype.fromRadix=bnpFromRadix;BigInteger.prototype.fromNumber=bnpFromNumber;BigInteger.prototype.bitwiseTo=bnpBitwiseTo;BigInteger.prototype.changeBit=bnpChangeBit;BigInteger.prototype.addTo=bnpAddTo;BigInteger.prototype.dMultiply=bnpDMultiply;BigInteger.prototype.dAddOffset=bnpDAddOffset;BigInteger.prototype.multiplyLowerTo=bnpMultiplyLowerTo;BigInteger.prototype.multiplyUpperTo=bnpMultiplyUpperTo;BigInteger.prototype.modInt=bnpModInt;BigInteger.prototype.millerRabin=bnpMillerRabin;BigInteger.prototype.clone=bnClone;BigInteger.prototype.intValue=bnIntValue;BigInteger.prototype.byteValue=bnByteValue;BigInteger.prototype.shortValue=bnShortValue;BigInteger.prototype.signum=bnSigNum;BigInteger.prototype.toByteArray=bnToByteArray;BigInteger.prototype.equals=bnEquals;BigInteger.prototype.min=bnMin;BigInteger.prototype.max=bnMax;BigInteger.prototype.and=bnAnd;BigInteger.prototype.or=bnOr;BigInteger.prototype.xor=bnXor;BigInteger.prototype.andNot=bnAndNot;BigInteger.prototype.not=bnNot;BigInteger.prototype.shiftLeft=bnShiftLeft;BigInteger.prototype.shiftRight=bnShiftRight;BigInteger.prototype.getLowestSetBit=bnGetLowestSetBit;BigInteger.prototype.bitCount=bnBitCount;BigInteger.prototype.testBit=bnTestBit;BigInteger.prototype.setBit=bnSetBit;BigInteger.prototype.clearBit=bnClearBit;BigInteger.prototype.flipBit=bnFlipBit;BigInteger.prototype.add=bnAdd;BigInteger.prototype.subtract=bnSubtract;BigInteger.prototype.multiply=bnMultiply;BigInteger.prototype.divide=bnDivide;BigInteger.prototype.remainder=bnRemainder;BigInteger.prototype.divideAndRemainder=bnDivideAndRemainder;BigInteger.prototype.modPow=bnModPow;BigInteger.prototype.modInverse=bnModInverse;BigInteger.prototype.pow=bnPow;BigInteger.prototype.gcd=bnGCD;BigInteger.prototype.isProbablePrime=bnIsProbablePrime;if(typeof(pidCrypt)!='undefined'&&typeof(BigInteger)!='undefined'&&typeof(SecureRandom)!='undefined'&&typeof(Arcfour)!='undefined')
{function parseBigInt(str,r){return new BigInteger(str,r);}
function linebrk(s,n){var ret="";var i=0;while(i+n<s.length){ret+=s.substring(i,i+n)+"\n";i+=n;}
return ret+s.substring(i,s.length);}
function byte2Hex(b){if(b<0x10)
return"0"+b.toString(16);else
return b.toString(16);}
function pkcs1unpad2(d,n){var b=d.toByteArray();var i=0;while(i<b.length&&b[i]==0)++i;if(b.length-i!=n-1||b[i]!=2)
return null;++i;while(b[i]!=0)
if(++i>=b.length)return null;var ret="";while(++i<b.length)
ret+=String.fromCharCode(b[i]);return ret;}
function pkcs1pad2(s,n){if(n<s.length+11){alert("Message too long for RSA");return null;}
var ba=new Array();var i=s.length-1;while(i>=0&&n>0){ba[--n]=s.charCodeAt(i--);};ba[--n]=0;var rng=new SecureRandom();var x=new Array();while(n>2){x[0]=0;while(x[0]==0)rng.nextBytes(x);ba[--n]=x[0];}
ba[--n]=2;ba[--n]=0;return new BigInteger(ba);}
pidCrypt.RSA=function(){this.n=null;this.e=0;this.d=null;this.p=null;this.q=null;this.dmp1=null;this.dmq1=null;this.coeff=null;}
pidCrypt.RSA.prototype.doPrivate=function(x){if(this.p==null||this.q==null)
return x.modPow(this.d,this.n);var xp=x.mod(this.p).modPow(this.dmp1,this.p);var xq=x.mod(this.q).modPow(this.dmq1,this.q);while(xp.compareTo(xq)<0)
xp=xp.add(this.p);return xp.subtract(xq).multiply(this.coeff).mod(this.p).multiply(this.q).add(xq);}
pidCrypt.RSA.prototype.setPublic=function(N,E,radix){if(typeof(radix)=='undefined')radix=16;if(N!=null&&E!=null&&N.length>0&&E.length>0){this.n=parseBigInt(N,radix);this.e=parseInt(E,radix);}
else
alert("Invalid RSA public key");}
pidCrypt.RSA.prototype.doPublic=function(x){return x.modPowInt(this.e,this.n);}
pidCrypt.RSA.prototype.encryptRaw=function(text){var m=pkcs1pad2(text,(this.n.bitLength()+7)>>3);if(m==null)return null;var c=this.doPublic(m);if(c==null)return null;var h=c.toString(16);if((h.length&1)==0)return h;else return"0"+h;}
pidCrypt.RSA.prototype.encrypt=function(text){text=pidCryptUtil.encodeBase64(text);return this.encryptRaw(text)}
pidCrypt.RSA.prototype.decryptRaw=function(ctext){var c=parseBigInt(ctext,16);var m=this.doPrivate(c);if(m==null)return null;return pkcs1unpad2(m,(this.n.bitLength()+7)>>3)}
pidCrypt.RSA.prototype.decrypt=function(ctext){var str=this.decryptRaw(ctext)
str=(str)?pidCryptUtil.decodeBase64(str):"";return str;}
pidCrypt.RSA.prototype.setPrivate=function(N,E,D,radix){if(typeof(radix)=='undefined')radix=16;if(N!=null&&E!=null&&N.length>0&&E.length>0){this.n=parseBigInt(N,radix);this.e=parseInt(E,radix);this.d=parseBigInt(D,radix);}
else
alert("Invalid RSA private key");}
pidCrypt.RSA.prototype.setPrivateEx=function(N,E,D,P,Q,DP,DQ,C,radix){if(typeof(radix)=='undefined')radix=16;if(N!=null&&E!=null&&N.length>0&&E.length>0){this.n=parseBigInt(N,radix);this.e=parseInt(E,radix);this.d=parseBigInt(D,radix);this.p=parseBigInt(P,radix);this.q=parseBigInt(Q,radix);this.dmp1=parseBigInt(DP,radix);this.dmq1=parseBigInt(DQ,radix);this.coeff=parseBigInt(C,radix);}
else
alert("Invalid RSA private key");}
pidCrypt.RSA.prototype.generate=function(B,E){var rng=new SecureRandom();var qs=B>>1;this.e=parseInt(E,16);var ee=new BigInteger(E,16);for(;;){for(;;){this.p=new BigInteger(B-qs,1,rng);if(this.p.subtract(BigInteger.ONE).gcd(ee).compareTo(BigInteger.ONE)==0&&this.p.isProbablePrime(10))break;}
for(;;){this.q=new BigInteger(qs,1,rng);if(this.q.subtract(BigInteger.ONE).gcd(ee).compareTo(BigInteger.ONE)==0&&this.q.isProbablePrime(10))break;}
if(this.p.compareTo(this.q)<=0){var t=this.p;this.p=this.q;this.q=t;}
var p1=this.p.subtract(BigInteger.ONE);var q1=this.q.subtract(BigInteger.ONE);var phi=p1.multiply(q1);if(phi.gcd(ee).compareTo(BigInteger.ONE)==0){this.n=this.p.multiply(this.q);this.d=ee.modInverse(phi);this.dmp1=this.d.mod(p1);this.dmq1=this.d.mod(q1);this.coeff=this.q.modInverse(this.p);break;}}}
pidCrypt.RSA.prototype.getASNData=function(tree){var params={};var data=[];var p=0;if(tree.value&&tree.type=='INTEGER')
data[p++]=tree.value;if(tree.sub)
for(var i=0;i<tree.sub.length;i++)
data=data.concat(this.getASNData(tree.sub[i]));return data;}
pidCrypt.RSA.prototype.setKeyFromASN=function(key,asntree){var keys=['N','E','D','P','Q','DP','DQ','C'];var params={};var asnData=this.getASNData(asntree);switch(key){case'Public':case'public':for(var i=0;i<asnData.length;i++)
params[keys[i]]=asnData[i].toLowerCase();this.setPublic(params.N,params.E,16);break;case'Private':case'private':for(var i=1;i<asnData.length;i++)
params[keys[i-1]]=asnData[i].toLowerCase();this.setPrivateEx(params.N,params.E,params.D,params.P,params.Q,params.DP,params.DQ,params.C,16);break;}}
pidCrypt.RSA.prototype.setPublicKeyFromASN=function(asntree){this.setKeyFromASN('public',asntree);}
pidCrypt.RSA.prototype.setPrivateKeyFromASN=function(asntree){this.setKeyFromASN('private',asntree);}
pidCrypt.RSA.prototype.getParameters=function(){var params={}
if(this.n!=null)params.n=this.n;params.e=this.e;if(this.d!=null)params.d=this.d;if(this.p!=null)params.p=this.p;if(this.q!=null)params.q=this.q;if(this.dmp1!=null)params.dmp1=this.dmp1;if(this.dmq1!=null)params.dmq1=this.dmq1;if(this.coeff!=null)params.c=this.coeff;return params;}}"use strict";var sjcl={cipher:{},hash:{},keyexchange:{},mode:{},misc:{},codec:{},exception:{corrupt:function(message){this.toString=function(){return"CORRUPT: "+this.message;};this.message=message;},invalid:function(message){this.toString=function(){return"INVALID: "+this.message;};this.message=message;},bug:function(message){this.toString=function(){return"BUG: "+this.message;};this.message=message;},notReady:function(message){this.toString=function(){return"NOT READY: "+this.message;};this.message=message;}}};sjcl.cipher.aes=function(key){if(!this._tables[0][0][0]){this._precompute();}
var i,j,tmp,encKey,decKey,sbox=this._tables[0][4],decTable=this._tables[1],keyLen=key.length,rcon=1;if(keyLen!==4&&keyLen!==6&&keyLen!==8){throw new sjcl.exception.invalid("invalid aes key size");}
this._key=[encKey=key.slice(0),decKey=[]];for(i=keyLen;i<4*keyLen+28;i++){tmp=encKey[i-1];if(i%keyLen===0||(keyLen===8&&i%keyLen===4)){tmp=sbox[tmp>>>24]<<24^sbox[tmp>>16&255]<<16^sbox[tmp>>8&255]<<8^sbox[tmp&255];if(i%keyLen===0){tmp=tmp<<8^tmp>>>24^rcon<<24;rcon=rcon<<1^(rcon>>7)*283;}}
encKey[i]=encKey[i-keyLen]^tmp;}
for(j=0;i;j++,i--){tmp=encKey[j&3?i:i-4];if(i<=4||j<4){decKey[j]=tmp;}else{decKey[j]=decTable[0][sbox[tmp>>>24]]^decTable[1][sbox[tmp>>16&255]]^decTable[2][sbox[tmp>>8&255]]^decTable[3][sbox[tmp&255]];}}};sjcl.cipher.aes.prototype={encrypt:function(data){return this._crypt(data,0);},decrypt:function(data){return this._crypt(data,1);},_tables:[[[],[],[],[],[]],[[],[],[],[],[]]],_precompute:function(){var encTable=this._tables[0],decTable=this._tables[1],sbox=encTable[4],sboxInv=decTable[4],i,x,xInv,d=[],th=[],x2,x4,x8,s,tEnc,tDec;for(i=0;i<256;i++){th[(d[i]=i<<1^(i>>7)*283)^i]=i;}
for(x=xInv=0;!sbox[x];x^=x2||1,xInv=th[xInv]||1){s=xInv^xInv<<1^xInv<<2^xInv<<3^xInv<<4;s=s>>8^s&255^99;sbox[x]=s;sboxInv[s]=x;x8=d[x4=d[x2=d[x]]];tDec=x8*0x1010101^x4*0x10001^x2*0x101^x*0x1010100;tEnc=d[s]*0x101^s*0x1010100;for(i=0;i<4;i++){encTable[i][x]=tEnc=tEnc<<24^tEnc>>>8;decTable[i][s]=tDec=tDec<<24^tDec>>>8;}}
for(i=0;i<5;i++){encTable[i]=encTable[i].slice(0);decTable[i]=decTable[i].slice(0);}},_crypt:function(input,dir){if(input.length!==4){throw new sjcl.exception.invalid("invalid aes block size");}
var key=this._key[dir],a=input[0]^key[0],b=input[dir?3:1]^key[1],c=input[2]^key[2],d=input[dir?1:3]^key[3],a2,b2,c2,nInnerRounds=key.length/4-2,i,kIndex=4,out=[0,0,0,0],table=this._tables[dir],t0=table[0],t1=table[1],t2=table[2],t3=table[3],sbox=table[4];for(i=0;i<nInnerRounds;i++){a2=t0[a>>>24]^t1[b>>16&255]^t2[c>>8&255]^t3[d&255]^key[kIndex];b2=t0[b>>>24]^t1[c>>16&255]^t2[d>>8&255]^t3[a&255]^key[kIndex+1];c2=t0[c>>>24]^t1[d>>16&255]^t2[a>>8&255]^t3[b&255]^key[kIndex+2];d=t0[d>>>24]^t1[a>>16&255]^t2[b>>8&255]^t3[c&255]^key[kIndex+3];kIndex+=4;a=a2;b=b2;c=c2;}
for(i=0;i<4;i++){out[dir?3&-i:i]=sbox[a>>>24]<<24^sbox[b>>16&255]<<16^sbox[c>>8&255]<<8^sbox[d&255]^key[kIndex++];a2=a;a=b;b=c;c=d;d=a2;}
return out;}};sjcl.bitArray={bitSlice:function(a,bstart,bend){a=sjcl.bitArray._shiftRight(a.slice(bstart/32),32-(bstart&31)).slice(1);return(bend===undefined)?a:sjcl.bitArray.clamp(a,bend-bstart);},extract:function(a,bstart,blength){var x,sh=Math.floor((-bstart-blength)&31);if((bstart+blength-1^bstart)&-32){x=(a[bstart/32|0]<<(32-sh))^(a[bstart/32+1|0]>>>sh);}else{x=a[bstart/32|0]>>>sh;}
return x&((1<<blength)-1);},concat:function(a1,a2){if(a1.length===0||a2.length===0){return a1.concat(a2);}
var out,i,last=a1[a1.length-1],shift=sjcl.bitArray.getPartial(last);if(shift===32){return a1.concat(a2);}else{return sjcl.bitArray._shiftRight(a2,shift,last|0,a1.slice(0,a1.length-1));}},bitLength:function(a){var l=a.length,x;if(l===0){return 0;}
x=a[l-1];return(l-1)*32+sjcl.bitArray.getPartial(x);},clamp:function(a,len){if(a.length*32<len){return a;}
a=a.slice(0,Math.ceil(len/32));var l=a.length;len=len&31;if(l>0&&len){a[l-1]=sjcl.bitArray.partial(len,a[l-1]&0x80000000>>(len-1),1);}
return a;},partial:function(len,x,_end){if(len===32){return x;}
return(_end?x|0:x<<(32-len))+len*0x10000000000;},getPartial:function(x){return Math.round(x/0x10000000000)||32;},equal:function(a,b){if(sjcl.bitArray.bitLength(a)!==sjcl.bitArray.bitLength(b)){return false;}
var x=0,i;for(i=0;i<a.length;i++){x|=a[i]^b[i];}
return(x===0);},_shiftRight:function(a,shift,carry,out){var i,last2=0,shift2;if(out===undefined){out=[];}
for(;shift>=32;shift-=32){out.push(carry);carry=0;}
if(shift===0){return out.concat(a);}
for(i=0;i<a.length;i++){out.push(carry|a[i]>>>shift);carry=a[i]<<(32-shift);}
last2=a.length?a[a.length-1]:0;shift2=sjcl.bitArray.getPartial(last2);out.push(sjcl.bitArray.partial(shift+shift2&31,(shift+shift2>32)?carry:out.pop(),1));return out;},_xor4:function(x,y){return[x[0]^y[0],x[1]^y[1],x[2]^y[2],x[3]^y[3]];}};sjcl.codec.hex={fromBits:function(arr){var out="",i,x;for(i=0;i<arr.length;i++){out+=((arr[i]|0)+0xF00000000000).toString(16).substr(4);}
return out.substr(0,sjcl.bitArray.bitLength(arr)/4);},toBits:function(str){var i,out=[],len;str=str.replace(/\s|0x/g,"");len=str.length;str=str+"00000000";for(i=0;i<str.length;i+=8){out.push(parseInt(str.substr(i,8),16)^0);}
return sjcl.bitArray.clamp(out,len*4);}};sjcl.codec.utf8String={fromBits:function(arr){var out="",bl=sjcl.bitArray.bitLength(arr),i,tmp;for(i=0;i<bl/8;i++){if((i&3)===0){tmp=arr[i/4];}
out+=String.fromCharCode(tmp>>>24);tmp<<=8;}
return decodeURIComponent(escape(out));},toBits:function(str){str=unescape(encodeURIComponent(str));var out=[],i,tmp=0;for(i=0;i<str.length;i++){tmp=tmp<<8|str.charCodeAt(i);if((i&3)===3){out.push(tmp);tmp=0;}}
if(i&3){out.push(sjcl.bitArray.partial(8*(i&3),tmp));}
return out;}};sjcl.codec.base64={_chars:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",fromBits:function(arr,_noEquals){var out="",i,bits=0,c=sjcl.codec.base64._chars,ta=0,bl=sjcl.bitArray.bitLength(arr);for(i=0;out.length*6<bl;){out+=c.charAt((ta^arr[i]>>>bits)>>>26);if(bits<6){ta=arr[i]<<(6-bits);bits+=26;i++;}else{ta<<=6;bits-=6;}}
while((out.length&3)&&!_noEquals){out+="=";}
return out;},toBits:function(str){str=str.replace(/\s|=/g,'');var out=[],i,bits=0,c=sjcl.codec.base64._chars,ta=0,x;for(i=0;i<str.length;i++){x=c.indexOf(str.charAt(i));if(x<0){throw new sjcl.exception.invalid("this isn't base64!");}
if(bits>26){bits-=26;out.push(ta^x>>>bits);ta=x<<(32-bits);}else{bits+=6;ta^=x<<(32-bits);}}
if(bits&56){out.push(sjcl.bitArray.partial(bits&56,ta,1));}
return out;}};if(sjcl.beware===undefined){sjcl.beware={};}
sjcl.beware["CBC mode is dangerous because it doesn't protect message integrity."]=function(){sjcl.mode.cbc={name:"cbc",encrypt:function(prp,plaintext,iv,adata){if(adata&&adata.length){throw new sjcl.exception.invalid("cbc can't authenticate data");}
if(sjcl.bitArray.bitLength(iv)!==128){throw new sjcl.exception.invalid("cbc iv must be 128 bits");}
var i,w=sjcl.bitArray,xor=w._xor4,bl=w.bitLength(plaintext),bp=0,output=[];if(bl&7){throw new sjcl.exception.invalid("pkcs#5 padding only works for multiples of a byte");}
for(i=0;bp+128<=bl;i+=4,bp+=128){iv=prp.encrypt(xor(iv,plaintext.slice(i,i+4)));output.splice(i,0,iv[0],iv[1],iv[2],iv[3]);}
bl=(16-((bl>>3)&15))*0x1010101;iv=prp.encrypt(xor(iv,w.concat(plaintext,[bl,bl,bl,bl]).slice(i,i+4)));output.splice(i,0,iv[0],iv[1],iv[2],iv[3]);return output;},decrypt:function(prp,ciphertext,iv,adata){if(adata&&adata.length){throw new sjcl.exception.invalid("cbc can't authenticate data");}
if(sjcl.bitArray.bitLength(iv)!==128){throw new sjcl.exception.invalid("cbc iv must be 128 bits");}
if((sjcl.bitArray.bitLength(ciphertext)&127)||!ciphertext.length){throw new sjcl.exception.corrupt("cbc ciphertext must be a positive multiple of the block size");}
var i,w=sjcl.bitArray,xor=w._xor4,bi,bo,output=[];adata=adata||[];for(i=0;i<ciphertext.length;i+=4){bi=ciphertext.slice(i,i+4);bo=xor(iv,prp.decrypt(bi));output.splice(i,0,bo[0],bo[1],bo[2],bo[3]);iv=bi;}
bi=output[i-1]&255;if(bi==0||bi>16){throw new sjcl.exception.corrupt("pkcs#5 padding corrupt");}
bo=bi*0x1010101;if(!w.equal(w.bitSlice([bo,bo,bo,bo],0,bi*8),w.bitSlice(output,output.length*32-bi*8,output.length*32))){throw new sjcl.exception.corrupt("pkcs#5 padding corrupt");}
return w.bitSlice(output,0,output.length*32-bi*8);}};};sjcl.hash.sha256=function(hash){if(!this._key[0]){this._precompute();}
if(hash){this._h=hash._h.slice(0);this._buffer=hash._buffer.slice(0);this._length=hash._length;}else{this.reset();}};sjcl.hash.sha256.hash=function(data){return(new sjcl.hash.sha256()).update(data).finalize();};sjcl.hash.sha256.prototype={blockSize:512,reset:function(){this._h=this._init.slice(0);this._buffer=[];this._length=0;return this;},update:function(data){if(typeof data==="string"){data=sjcl.codec.utf8String.toBits(data);}
var i,b=this._buffer=sjcl.bitArray.concat(this._buffer,data),ol=this._length,nl=this._length=ol+sjcl.bitArray.bitLength(data);for(i=512+ol&-512;i<=nl;i+=512){this._block(b.splice(0,16));}
return this;},finalize:function(){var i,b=this._buffer,h=this._h;b=sjcl.bitArray.concat(b,[sjcl.bitArray.partial(1,1)]);for(i=b.length+2;i&15;i++){b.push(0);}
b.push(Math.floor(this._length/0x100000000));b.push(this._length|0);while(b.length){this._block(b.splice(0,16));}
this.reset();return h;},_init:[],_key:[],_precompute:function(){var i=0,prime=2,factor;function frac(x){return(x-Math.floor(x))*0x100000000|0;}
outer:for(;i<64;prime++){for(factor=2;factor*factor<=prime;factor++){if(prime%factor===0){continue outer;}}
if(i<8){this._init[i]=frac(Math.pow(prime,1/2));}
this._key[i]=frac(Math.pow(prime,1/3));i++;}},_block:function(words){var i,tmp,a,b,w=words.slice(0),h=this._h,k=this._key,h0=h[0],h1=h[1],h2=h[2],h3=h[3],h4=h[4],h5=h[5],h6=h[6],h7=h[7];for(i=0;i<64;i++){if(i<16){tmp=w[i];}else{a=w[(i+1)&15];b=w[(i+14)&15];tmp=w[i&15]=((a>>>7^a>>>18^a>>>3^a<<25^a<<14)+
(b>>>17^b>>>19^b>>>10^b<<15^b<<13)+
w[i&15]+w[(i+9)&15])|0;}
tmp=(tmp+h7+(h4>>>6^h4>>>11^h4>>>25^h4<<26^h4<<21^h4<<7)+(h6^h4&(h5^h6))+k[i]);h7=h6;h6=h5;h5=h4;h4=h3+tmp|0;h3=h2;h2=h1;h1=h0;h0=(tmp+((h1&h2)^(h3&(h1^h2)))+(h1>>>2^h1>>>13^h1>>>22^h1<<30^h1<<19^h1<<10))|0;}
h[0]=h[0]+h0|0;h[1]=h[1]+h1|0;h[2]=h[2]+h2|0;h[3]=h[3]+h3|0;h[4]=h[4]+h4|0;h[5]=h[5]+h5|0;h[6]=h[6]+h6|0;h[7]=h[7]+h7|0;}};sjcl.random={randomWords:function(nwords,paranoia){var out=[],i,readiness=this.isReady(paranoia),g;if(readiness===this._NOT_READY){throw new sjcl.exception.notReady("generator isn't seeded");}else if(readiness&this._REQUIRES_RESEED){this._reseedFromPools(!(readiness&this._READY));}
for(i=0;i<nwords;i+=4){if((i+1)%this._MAX_WORDS_PER_BURST===0){this._gate();}
g=this._gen4words();out.push(g[0],g[1],g[2],g[3]);}
this._gate();return out.slice(0,nwords);},setDefaultParanoia:function(paranoia){this._defaultParanoia=paranoia;},addEntropy:function(data,estimatedEntropy,source){source=source||"user";var id,i,ty=0,tmp,t=(new Date()).valueOf(),robin=this._robins[source],oldReady=this.isReady();id=this._collectorIds[source];if(id===undefined){id=this._collectorIds[source]=this._collectorIdNext++;}
if(robin===undefined){robin=this._robins[source]=0;}
this._robins[source]=(this._robins[source]+1)%this._pools.length;switch(typeof(data)){case"number":data=[data];ty=1;break;case"object":if(estimatedEntropy===undefined){estimatedEntropy=0;for(i=0;i<data.length;i++){tmp=data[i];while(tmp>0){estimatedEntropy++;tmp=tmp>>>1;}}}
this._pools[robin].update([id,this._eventId++,ty||2,estimatedEntropy,t,data.length].concat(data));break;case"string":if(estimatedEntropy===undefined){estimatedEntropy=data.length;}
this._pools[robin].update([id,this._eventId++,3,estimatedEntropy,t,data.length]);this._pools[robin].update(data);break;default:throw new sjcl.exception.bug("random: addEntropy only supports number, array or string");}
this._poolEntropy[robin]+=estimatedEntropy;this._poolStrength+=estimatedEntropy;if(oldReady===this._NOT_READY){if(this.isReady()!==this._NOT_READY){this._fireEvent("seeded",Math.max(this._strength,this._poolStrength));}
this._fireEvent("progress",this.getProgress());}},isReady:function(paranoia){var entropyRequired=this._PARANOIA_LEVELS[(paranoia!==undefined)?paranoia:this._defaultParanoia];if(this._strength&&this._strength>=entropyRequired){return(this._poolEntropy[0]>this._BITS_PER_RESEED&&(new Date()).valueOf()>this._nextReseed)?this._REQUIRES_RESEED|this._READY:this._READY;}else{return(this._poolStrength>=entropyRequired)?this._REQUIRES_RESEED|this._NOT_READY:this._NOT_READY;}},getProgress:function(paranoia){var entropyRequired=this._PARANOIA_LEVELS[paranoia?paranoia:this._defaultParanoia];if(this._strength>=entropyRequired){return 1.0;}else{return(this._poolStrength>entropyRequired)?1.0:this._poolStrength/entropyRequired;}},startCollectors:function(){if(this._collectorsStarted){return;}
if(window.addEventListener){window.addEventListener("load",this._loadTimeCollector,false);window.addEventListener("mousemove",this._mouseCollector,false);}else if(document.attachEvent){document.attachEvent("onload",this._loadTimeCollector);document.attachEvent("onmousemove",this._mouseCollector);}
else{throw new sjcl.exception.bug("can't attach event");}
this._collectorsStarted=true;},stopCollectors:function(){if(!this._collectorsStarted){return;}
if(window.removeEventListener){window.removeEventListener("load",this._loadTimeCollector);window.removeEventListener("mousemove",this._mouseCollector);}else if(window.detachEvent){window.detachEvent("onload",this._loadTimeCollector);window.detachEvent("onmousemove",this._mouseCollector);}
this._collectorsStarted=false;},addEventListener:function(name,callback){this._callbacks[name][this._callbackI++]=callback;},removeEventListener:function(name,cb){var i,j,cbs=this._callbacks[name],jsTemp=[];for(j in cbs){if(cbs.hasOwnProperty(j)&&cbs[j]===cb){jsTemp.push(j);}}
for(i=0;i<jsTemp.length;i++){j=jsTemp[i];delete cbs[j];}},_pools:[new sjcl.hash.sha256()],_poolEntropy:[0],_reseedCount:0,_robins:{},_eventId:0,_collectorIds:{},_collectorIdNext:0,_strength:0,_poolStrength:0,_nextReseed:0,_key:[0,0,0,0,0,0,0,0],_counter:[0,0,0,0],_cipher:undefined,_defaultParanoia:6,_collectorsStarted:false,_callbacks:{progress:{},seeded:{}},_callbackI:0,_NOT_READY:0,_READY:1,_REQUIRES_RESEED:2,_MAX_WORDS_PER_BURST:65536,_PARANOIA_LEVELS:[0,48,64,96,128,192,256,384,512,768,1024],_MILLISECONDS_PER_RESEED:30000,_BITS_PER_RESEED:80,_gen4words:function(){for(var i=0;i<4;i++){this._counter[i]=this._counter[i]+1|0;if(this._counter[i]){break;}}
return this._cipher.encrypt(this._counter);},_gate:function(){this._key=this._gen4words().concat(this._gen4words());this._cipher=new sjcl.cipher.aes(this._key);},_reseed:function(seedWords){this._key=sjcl.hash.sha256.hash(this._key.concat(seedWords));this._cipher=new sjcl.cipher.aes(this._key);for(var i=0;i<4;i++){this._counter[i]=this._counter[i]+1|0;if(this._counter[i]){break;}}},_reseedFromPools:function(full){var reseedData=[],strength=0,i;this._nextReseed=reseedData[0]=(new Date()).valueOf()+this._MILLISECONDS_PER_RESEED;for(i=0;i<16;i++){reseedData.push(Math.random()*0x100000000|0);}
for(i=0;i<this._pools.length;i++){reseedData=reseedData.concat(this._pools[i].finalize());strength+=this._poolEntropy[i];this._poolEntropy[i]=0;if(!full&&(this._reseedCount&(1<<i))){break;}}
if(this._reseedCount>=1<<this._pools.length){this._pools.push(new sjcl.hash.sha256());this._poolEntropy.push(0);}
this._poolStrength-=strength;if(strength>this._strength){this._strength=strength;}
this._reseedCount++;this._reseed(reseedData);},_mouseCollector:function(ev){var x=ev.x||ev.clientX||ev.offsetX,y=ev.y||ev.clientY||ev.offsetY;sjcl.random.addEntropy([x,y],2,"mouse");},_loadTimeCollector:function(ev){var d=new Date();sjcl.random.addEntropy(d,2,"loadtime");},_fireEvent:function(name,arg){var j,cbs=sjcl.random._callbacks[name],cbsTemp=[];for(j in cbs){if(cbs.hasOwnProperty(j)){cbsTemp.push(cbs[j]);}}
for(j=0;j<cbsTemp.length;j++){cbsTemp[j](arg);}}};for(k in sjcl.beware){if(sjcl.beware.hasOwnProperty(k)){sjcl.beware[k]();}}
Braintree={pidCrypt:pidCrypt,pidCryptUtil:pidCryptUtil,sjcl:sjcl};Braintree.create=function(publicKey){var my={publicKey:publicKey};var generateAesKey=function(){return{key:sjcl.random.randomWords(8,0),encrypt:function(plainText){var aes=new sjcl.cipher.aes(this.key);var iv=sjcl.random.randomWords(4,0);var plainTextBits=sjcl.codec.utf8String.toBits(plainText);var cipherTextBits=sjcl.mode.cbc.encrypt(aes,plainTextBits,iv);return sjcl.codec.base64.fromBits(sjcl.bitArray.concat(iv,cipherTextBits));},encryptKeyWithRsa:function(rsaKey){var encryptedKeyHex=rsaKey.encryptRaw(sjcl.codec.base64.fromBits(this.key));return pidCryptUtil.encodeBase64(pidCryptUtil.convertFromHex(encryptedKeyHex));}};};var rsaKey=function(){var key=pidCryptUtil.decodeBase64(my.publicKey);var rsa=new pidCrypt.RSA();var keyBytes=pidCryptUtil.toByteArray(key);var asn=pidCrypt.ASN1.decode(keyBytes);var tree=asn.toHexTree();rsa.setPublicKeyFromASN(tree);return rsa;};var encrypt=function(text){var key=generateAesKey();var aesEncryptedData=key.encrypt(text);return"$bt2$"+key.encryptKeyWithRsa(rsaKey())+"$"+aesEncryptedData;};return{encrypt:encrypt,publicKey:my.publicKey,version:'1.1.1'};};return Braintree;})();
