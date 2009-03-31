/*
 
Correctly handle PNG transparency in Win IE 5.5 & 6.
http://homepage.ntlworld.com/bobosola. Updated 18-Jan-2006.

Use in <HEAD> with DEFER keyword wrapped in conditional comments:
<!--[if lt IE 7]>
<script defer type="text/javascript" src="pngfix.js"></script>
<![endif]-->

*/
function doPNGS() {
	var arVersion = navigator.appVersion.split("MSIE")
	var version = parseFloat(arVersion[1])
	
	if ((version >= 5.5) && (document.body.filters)) 
	{
	
	   for(var i=0; i<document.images.length; i++)
	   {
	      var img = document.images[i];
  	  	var imgName = img.src.toUpperCase();
	      
	      //|| imgName.indexOf("GENERATE.PHP") > 0
	      if (( imgName.substring(imgName.length-3, imgName.length) == "PNG"  || imgName.indexOf("F=PNG") > 0 )  &&  imgName.indexOf("SPACER.PNG") <0   )
	      {
	      	doPNG(img,i);
	      	i = i-1;
	      }
	   }
	}
}

function doPNG(img,i) {
	
	  var imgName = img.src.toUpperCase();
	      
	      //|| imgName.indexOf("GENERATE.PHP") > 0
      if (( imgName.substring(imgName.length-3, imgName.length) == "PNG"  || imgName.indexOf("F=PNG") > 0 )   )
      {
      	// alert(imgName);
         var imgID = (img.id) ? "id='" + img.id + "' " : ""
         var imgClass = (img.className) ? "class='png-replaced " + img.className + "' " : "class='png-replaced'"
         var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
         var imgStyle = "display:inline-block;" + img.style.cssText 
         if (img.align == "left") imgStyle = "float:left;" + imgStyle
         if (img.align == "right") imgStyle = "float:right;" + imgStyle
         if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle
         var strNewHTML = "<span " + imgID + imgClass + imgTitle
         + " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
         + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
         + "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>" 
         img.outerHTML = strNewHTML;
         i = i-1;
      }
	      
}

SafeAddOnload(doPNGS);
doPNGS();