/*
 * Thickbox 3.1 - One Box To Rule Them All.
 * By Cody Lindley (http://www.codylindley.com)
 * Copyright (c) 2007 cody lindley
 * Licensed under the MIT License: http://www.opensource.org/licenses/mit-license.php
*/

//Modified to not conflict with other tb installs

//on page load call mbt_tb_init
jQuery(document).ready(function(){   
	mbt_tb_init('a.thickbox, area.thickbox, input.thickbox');//pass where to apply thickbox
});

//add thickbox to href & area elements that have a class of .thickbox
function mbt_tb_init(domChunk){
	jQuery(domChunk).click(function(){
		mbt_tb_remove();
		var t = this.title || this.name || null;
		var a = this.href || this.alt;
		var g = this.rel || false;
		var x = jQuery(this).attr('data-thickbox');
		mbt_tb_show(t,a,g,x);
		this.blur();
		return false;
	});
}

function mbt_tb_show(caption, url, imageGroup, QueryString) {//function called when the user clicks on a thickbox link
	try {
		if (typeof document.body.style.maxHeight === "undefined") {//if IE 6
			jQuery("body","html").css({height: "100%", width: "100%"});
			jQuery("html").css("overflow","hidden");
			if (document.getElementById("mbt_tb_HideSelect") === null) {//iframe to hide select elements in ie6
				jQuery("body").append("<iframe id='mbt_tb_HideSelect'></iframe><div id='mbt_tb_overlay'></div><div id='mbt_tb_window'></div>");
				jQuery("#mbt_tb_overlay").click(mbt_tb_remove);
			}
		}else{//all others
			if(document.getElementById("mbt_tb_overlay") === null){
				jQuery("body").append("<div id='mbt_tb_overlay'></div><div id='mbt_tb_window'></div>");
				jQuery("#mbt_tb_overlay").click(mbt_tb_remove);
			}
		}
		
		if(mbt_tb_detectMacXFF()){
			jQuery("#mbt_tb_overlay").addClass("mbt_tb_overlayMacFFBGHack");//use png overlay so hide flash
		}else{
			jQuery("#mbt_tb_overlay").addClass("mbt_tb_overlayBG");//use background and opacity
		}
		
		if(caption===null){caption="";}
		
		var baseURL;
	   if(url.indexOf("?")!==-1){ //ff there is a query string involved
			baseURL = url.substr(0, url.indexOf("?"));
	   }else{ 
	   		baseURL = url;
	   }
	   
	   var urlString = /\.jpgjQuery|\.jpegjQuery|\.pngjQuery|\.gifjQuery|\.bmpjQuery/;
	   var urlType = baseURL.toLowerCase().match(urlString);

		if(urlType == '.jpg' || urlType == '.jpeg' || urlType == '.png' || urlType == '.gif' || urlType == '.bmp'){//code to show images
				
			mbt_tb_PrevCaption = "";
			mbt_tb_PrevURL = "";
			mbt_tb_PrevHTML = "";
			mbt_tb_NextCaption = "";
			mbt_tb_NextURL = "";
			mbt_tb_NextHTML = "";
			mbt_tb_imageCount = "";
			mbt_tb_FoundURL = false;
			if(imageGroup){
				mbt_tb_TempArray = jQuery("a[@rel="+imageGroup+"]").get();
				for (mbt_tb_Counter = 0; ((mbt_tb_Counter < mbt_tb_TempArray.length) && (mbt_tb_NextHTML === "")); mbt_tb_Counter++) {
					var urlTypeTemp = mbt_tb_TempArray[mbt_tb_Counter].href.toLowerCase().match(urlString);
						if (!(mbt_tb_TempArray[mbt_tb_Counter].href == url)) {						
							if (mbt_tb_FoundURL) {
								mbt_tb_NextCaption = mbt_tb_TempArray[mbt_tb_Counter].title;
								mbt_tb_NextURL = mbt_tb_TempArray[mbt_tb_Counter].href;
								mbt_tb_NextHTML = "<span id='mbt_tb_next'>&nbsp;&nbsp;<a href='#'>Next &gt;</a></span>";
							} else {
								mbt_tb_PrevCaption = mbt_tb_TempArray[mbt_tb_Counter].title;
								mbt_tb_PrevURL = mbt_tb_TempArray[mbt_tb_Counter].href;
								mbt_tb_PrevHTML = "<span id='mbt_tb_prev'>&nbsp;&nbsp;<a href='#'>&lt; Prev</a></span>";
							}
						} else {
							mbt_tb_FoundURL = true;
							mbt_tb_imageCount = "Image " + (mbt_tb_Counter + 1) +" of "+ (mbt_tb_TempArray.length);											
						}
				}
			}

			imgPreloader = new Image();
			imgPreloader.onload = function(){		
			imgPreloader.onload = null;
				
			// Resizing large images - orginal by Christian Montoya edited by me.
			var pagesize = mbt_tb_getPageSize();
			var x = pagesize[0] - 150;
			var y = pagesize[1] - 150;
			var imageWidth = imgPreloader.width;
			var imageHeight = imgPreloader.height;
			if (imageWidth > x) {
				imageHeight = imageHeight * (x / imageWidth); 
				imageWidth = x; 
				if (imageHeight > y) { 
					imageWidth = imageWidth * (y / imageHeight); 
					imageHeight = y; 
				}
			} else if (imageHeight > y) { 
				imageWidth = imageWidth * (y / imageHeight); 
				imageHeight = y; 
				if (imageWidth > x) { 
					imageHeight = imageHeight * (x / imageWidth); 
					imageWidth = x;
				}
			}
			// End Resizing
			
			mbt_tb_WIDTH = imageWidth + 30;
			mbt_tb_HEIGHT = imageHeight + 60;
			jQuery("#mbt_tb_window").append("<a href='' id='mbt_tb_ImageOff' title='Close'><img id='mbt_tb_Image' src='"+url+"' width='"+imageWidth+"' height='"+imageHeight+"' alt='"+caption+"'/></a>" + "<div id='mbt_tb_caption'>"+caption+"<div id='mbt_tb_secondLine'>" + mbt_tb_imageCount + mbt_tb_PrevHTML + mbt_tb_NextHTML + "</div></div><div id='mbt_tb_closeWindow'><a href='#' id='mbt_tb_closeWindowButton' title='Close'>close</a> or Esc Key</div>"); 		
			
			jQuery("#mbt_tb_closeWindowButton").click(mbt_tb_remove);
			
			if (!(mbt_tb_PrevHTML === "")) {
				function goPrev(){
					if(jQuery(document).unbind("click",goPrev)){jQuery(document).unbind("click",goPrev);}
					jQuery("#mbt_tb_window").remove();
					jQuery("body").append("<div id='mbt_tb_window'></div>");
					mbt_tb_show(mbt_tb_PrevCaption, mbt_tb_PrevURL, imageGroup);
					return false;	
				}
				jQuery("#mbt_tb_prev").click(goPrev);
			}
			
			if (!(mbt_tb_NextHTML === "")) {		
				function goNext(){
					jQuery("#mbt_tb_window").remove();
					jQuery("body").append("<div id='mbt_tb_window'></div>");
					mbt_tb_show(mbt_tb_NextCaption, mbt_tb_NextURL, imageGroup);				
					return false;	
				}
				jQuery("#mbt_tb_next").click(goNext);
				
			}

			document.onkeydown = function(e){ 	
				if (e == null) { // ie
					keycode = event.keyCode;
				} else { // mozilla
					keycode = e.which;
				}
				if(keycode == 27){ // close
					mbt_tb_remove();
				} else if(keycode == 190){ // display previous image
					if(!(mbt_tb_NextHTML == "")){
						document.onkeydown = "";
						goNext();
					}
				} else if(keycode == 188){ // display next image
					if(!(mbt_tb_PrevHTML == "")){
						document.onkeydown = "";
						goPrev();
					}
				}	
			};
			
			mbt_tb_position();
			jQuery("#mbt_tb_ImageOff").click(mbt_tb_remove);
			jQuery("#mbt_tb_window").css({display:"block"}); //for safari using css instead of show
			};
			
			imgPreloader.src = url;
		}else{//code to show html
			
			var queryString = QueryString; //url.replace(/^[^\?]+\??/,'');
			var params = mbt_tb_parseQuery( queryString );

			mbt_tb_WIDTH = (params['width']*1) || 630; //defaults to 630 if no paramaters were added to URL
			mbt_tb_HEIGHT = (params['height']*1) || 440; //defaults to 440 if no paramaters were added to URL
			ajaxContentW = mbt_tb_WIDTH;
			ajaxContentH = mbt_tb_HEIGHT;
			
			if(queryString.indexOf('mbt_tb_iframe') != -1){// either iframe or ajax window		
					urlNoQuery = url.split('mbt_tb_');
					jQuery("#mbt_tb_iframeContent").remove();
					if(params['modal'] != "true"){//iframe no modal
						jQuery("#mbt_tb_window").append("<div id='mbt_tb_title'><div id='mbt_tb_ajaxWindowTitle'>"+caption+"</div><div id='mbt_tb_closeAjaxWindow'><a href='#' id='mbt_tb_closeWindowButton' title='Close'></a></div></div><iframe frameborder='0' hspace='0' src='"+urlNoQuery[0]+"' id='mbt_tb_iframeContent' name='mbt_tb_iframeContent"+Math.round(Math.random()*1000)+"' onload='mbt_tb_showIframe()' style='width:"+(ajaxContentW)+"px;height:"+(ajaxContentH)+"px;' > </iframe>");
					}else{//iframe modal
					jQuery("#mbt_tb_overlay").unbind();
						jQuery("#mbt_tb_window").append("<iframe frameborder='0' hspace='0' src='"+urlNoQuery[0]+"' id='mbt_tb_iframeContent' name='mbt_tb_iframeContent"+Math.round(Math.random()*1000)+"' onload='mbt_tb_showIframe()' style='width:"+(ajaxContentW)+"px;height:"+(ajaxContentH)+"px;'> </iframe>");
					}
			}else{// not an iframe, ajax
					if(jQuery("#mbt_tb_window").css("display") != "block"){
						if(params['modal'] != "true"){//ajax no modal
						jQuery("#mbt_tb_window").append("<div id='mbt_tb_title'><div id='mbt_tb_ajaxWindowTitle'>"+caption+"</div><div id='mbt_tb_closeAjaxWindow'><a href='#' id='mbt_tb_closeWindowButton'></a></div></div><div id='mbt_tb_ajaxContent'></div>");
						}else{//ajax modal
						jQuery("#mbt_tb_overlay").unbind();
						jQuery("#mbt_tb_window").append("<div id='mbt_tb_ajaxContent' class='mbt_tb_modal'></div>");	
						}
					}else{//this means the window is already up, we are just loading new content via ajax
						//jQuery("#mbt_tb_ajaxContent")[0].style.width = ajaxContentW +"px";
						//jQuery("#mbt_tb_ajaxContent")[0].style.height = ajaxContentH +"px";
						jQuery("#mbt_tb_ajaxContent")[0].scrollTop = 0;
						jQuery("#mbt_tb_ajaxWindowTitle").html(caption);
					}
			}
					
			jQuery("#mbt_tb_closeWindowButton").click(mbt_tb_remove);
			
				if(url.indexOf('mbt_tb_inline') != -1){	
					jQuery("#mbt_tb_ajaxContent").append(jQuery('#' + params['inlineId']).children());
					jQuery("#mbt_tb_window").unload(function () {
						jQuery('#' + params['inlineId']).append( jQuery("#mbt_tb_ajaxContent").children() ); // move elements back when you're finished
					});
					mbt_tb_position();
					jQuery("#mbt_tb_window").css({display:"block"}); 
				}else if(queryString.indexOf('mbt_tb_iframe') != -1){
					mbt_tb_position();
					if(jQuery.browser.safari){//safari needs help because it will not fire iframe onload
						jQuery("#mbt_tb_window").css({display:"block"});
					}
				}else{
					jQuery("#mbt_tb_ajaxContent").load(url += "&random=" + (new Date().getTime()),function(){//to do a post change this load method
						mbt_tb_position();
						mbt_tb_init("#mbt_tb_ajaxContent a.thickbox");
						jQuery("#mbt_tb_window").css({display:"block"});
					});
				}
			
		}

		if(!params['modal']){
			document.onkeyup = function(e){ 	
				if (e == null) { // ie
					keycode = event.keyCode;
				} else { // mozilla
					keycode = e.which;
				}
				if(keycode == 27){ // close
					mbt_tb_remove();
				}	
			};
		}
		
	} catch(e) {
		//nothing here
	}
}

//helper functions below
function mbt_tb_showIframe(){
	jQuery("#mbt_tb_window").css({display:"block"});
}

function mbt_tb_remove() {
 	jQuery("#mbt_tb_imageOff").unbind("click");
	jQuery("#mbt_tb_closeWindowButton").unbind("click");
	jQuery('#mbt_tb_window,#mbt_tb_overlay,#mbt_tb_HideSelect').trigger("unload").unbind().remove();
	//jQuery("#mbt_tb_window").fadeOut("fast",function(){});
	if (typeof document.body.style.maxHeight == "undefined") {//if IE 6
		jQuery("body","html").css({height: "auto", width: "auto"});
		jQuery("html").css("overflow","");
	}
	document.onkeydown = "";
	document.onkeyup = "";
	return false;
}

function mbt_tb_position() {
	jQuery("#mbt_tb_window").css({marginLeft: '-' + parseInt((mbt_tb_WIDTH / 2),10) + 'px', width: mbt_tb_WIDTH + 'px'});
	jQuery("#mbt_tb_window").css({'top': '132px'});
	//jQuery("#mbt_tb_window").css({marginTop: '-' + parseInt((mbt_tb_HEIGHT / 2),10) + 'px'});
}

function mbt_tb_parseQuery ( query ) {
   var Params = {};
   if ( ! query ) {return Params;}// return empty object
   var Pairs = query.split(/[;&]/);
   for ( var i = 0; i < Pairs.length; i++ ) {
      var KeyVal = Pairs[i].split('=');
      if ( ! KeyVal || KeyVal.length != 2 ) {continue;}
      var key = unescape( KeyVal[0] );
      var val = unescape( KeyVal[1] );
      val = val.replace(/\+/g, ' ');
      Params[key] = val;
   }
   return Params;
}

function mbt_tb_getPageSize(){
	var de = document.documentElement;
	var w = window.innerWidth || self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
	var h = window.innerHeight || self.innerHeight || (de&&de.clientHeight) || document.body.clientHeight;
	arrayPageSize = [w,h];
	return arrayPageSize;
}

function mbt_tb_detectMacXFF() {
  var userAgent = navigator.userAgent.toLowerCase();
  if (userAgent.indexOf('mac') != -1 && userAgent.indexOf('firefox')!=-1) {
    return true;
  }
}

