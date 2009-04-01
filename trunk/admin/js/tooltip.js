
function tooltip(){}

// setup properties of tooltip object
tooltip.id="tooltip";
tooltip.offsetx = 10;
tooltip.offsety = 10;
tooltip.x = 0;
tooltip.y = 0;
tooltip.snow = 0;
tooltip.tooltipElement=null;
tooltip.title_saved='';
tooltip.saveonmouseover=null;
tooltip.ie4 = (document.all)? true:false;       // check if ie4
tooltip.ie5 = false;
tooltip.maxwidth = 200;

if(tooltip.ie4) tooltip.ie5 = (navigator.userAgent.indexOf('MSIE 5')>0 || navigator.userAgent.indexOf('MSIE 6')>0);
tooltip.dom2 = ((document.getElementById) && !(tooltip.ie4||tooltip.ie5))? true:false; // check the W3C DOM level2 compliance. ie4, ie5, ns4 are not dom level2 compliance !! grrrr >:-(


/**
* Open ToolTip. The title attribute of the htmlelement is the text of the tooltip
* Call this method on the mouseover event on your htmlelement
* ex :  <div id="myHtmlElement" onmouseover="tooltip.show(this)"...></div>
*/
tooltip.show = function (htmlelement,attribut) {


   if ( this.ie4 || this.dom2 ) {
      // we save text of title attribute to avoid the showing of tooltip generated by browser
      text=htmlelement.getAttribute(attribut);
      this.title_saved=text;
      htmlelement.setAttribute(attribut,"");
   }
    if(this.dom2){
        this.tooltipElement = document.getElementById(this.id);
        this.saveonmouseover=document.onmousemove;
        document.onmousemove = this.mouseMove;
    }else if ( this.ie4 ) {
      this.tooltipElement = document.all[this.id].style;
      this.saveonmouseover=document.onmousemove;
      document.onmousemove = this.mouseMove;
    }

   if ( this.ie4 || this.dom2 ) {
      if(this.ie4) document.all[this.id].innerHTML = text;
      else if(this.dom2) document.getElementById(this.id).innerHTML=text;

      //this.moveTo(this.x + this.offsetx , this.y + this.offsety);
	  var xyz = findPos(htmlelement);
	  //alert(xyz);
      this.moveTo(xyz[0]+this.offsetx,xyz[1]);
      //alert(findPos(this.tooltipElement));
      if(this.ie4) this.tooltipElement.visibility = "visible";
      else if(this.dom2) this.tooltipElement.style.visibility ="visible";
   }

   return false;
}

/**
* hide tooltip
* call this method on the mouseout event of the html element
* ex : <div id="myHtmlElement" ... onmouseout="tooltip.hide(this)"></div>
*/
tooltip.hide = function (htmlelement,attribut) {
	if(!htmlelement) {

      gid('tooltip').style.visibility = "hidden";	
	} else {
    if ( this.ie4 || this.dom2 ) {
      htmlelement.setAttribute(attribut,this.title_saved);
      this.title_saved="";

        if(this.ie4) this.tooltipElement.visibility = "hidden";
      else if(this.dom2) this.tooltipElement.style.visibility = "hidden";

      document.onmousemove=this.saveonmouseover;
    }
	}
}



// Moves the tooltip element
tooltip.mouseMove = function (e) {
   // we don't use "this", but tooltip because this method is assign to an event of document
   // and so is dreferenced

   if(tooltip.ie4 || tooltip.dom2){

      if(tooltip.dom2){
         tooltip.x = e.pageX;
         tooltip.y = e.pageY;
      }else{
         if(tooltip.ie4) { tooltip.x = event.x; tooltip.y = event.y; }
         if(tooltip.ie5) { tooltip.x = event.x + document.body.scrollLeft;
               tooltip.y = event.y + document.body.scrollTop; }
      }
      //tooltip.moveTo( tooltip.x +tooltip.offsetx , tooltip.y + tooltip.offsety);
   }
}

// Move the tooltip element
tooltip.moveTo = function (xL,yL) {
    if(this.dom2){
        this.tooltipElement.style.left = xL +"px";
      this.tooltipElement.style.top = yL +"px";

      CheckSize();
      //alert(divWidth+xL+" - "+frameWidth);
        if(divHeight+yL > ( frameHeight + window.pageYOffset )) {
            this.tooltipElement.style.top = (frameHeight - divHeight + window.pageYOffset )+"px";
        }

        if(divWidth+xL > frameWidth) {
            this.tooltipElement.style.left = frameWidth - divWidth+"px";
        }

    }else if(this.ie4){
      this.tooltipElement.left = xL;
      this.tooltipElement.top = yL;
   }
}



window.onload = function() {
    inputs = document.getElementsByTagName("a");

    for(p in inputs) {

        if(inputs[p].title && !inputs[p].onmouseover) {
            inputs[p].onmouseover = function() {tooltip.show(this,"title");}
            inputs[p].onmouseout = function() {tooltip.hide(this,"title");}
        }

    }


    inputs = document.getElementsByTagName("input");

    for(p in inputs) {

        if(inputs[p].title) {
            inputs[p].onmouseover = function() {tooltip.show(this,"title");}
            inputs[p].onmouseout = function() {tooltip.hide(this,"title");}
        }

    }
     inputs = document.getElementsByTagName("img");

    for(p in inputs) {
            if(inputs[p].alt) {
            inputs[p].onmouseover = function() {tooltip.show(this,"alt");}
            inputs[p].onmouseout = function() {tooltip.hide(this,"alt");}
        }

        }
}
