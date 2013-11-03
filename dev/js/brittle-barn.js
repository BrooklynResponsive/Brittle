var brittlebarn = {};


(function(){

	
	'use strict';
	
	/*
	 * brittlebarn.Modal - wraps foundation.reveal
	 *
	 * @author Matt Lima
	 */


	/*
	*	   elementID: The DOM ID of the element to use. Will be created if not present.
	*	   url: optional ajax url to load
	* 	   postdata: JSON post data for the ajax load. 
	*	   options: JS object of key value pairs:
			 	modalClass : string representing the class (small, medium, large, expand)
			 	onReady : callback function when ajax content has loaded.
	*/
    function Modal(elementID, url, postdata, options) {
		this.data = {};
		this.data.elementID = elementID;
		this.data.url = (typeof(url)!='undefined') ? (url) : null ;
		this.data.postdata = (typeof(postdata)!='undefined') ? postdata : null ;
		this.options = (typeof(options)!='undefined') ? options : function(){ return null } ;
		
		var modalClass="expand";
		if(this.options.modalClass) modalClass=this.options.modalClass;
		this.onReady=$.noop;
		if(this.options.onReady) this.onReady=this.options.onReady;
		
		  
  		if($("#"+elementID).length == 0){ //first time called 
  			this.element = $("<div id='"+elementID+"'></div>").addClass('reveal-modal ' + modalClass).appendTo('body');
  		}else{
  			this.element = $("#"+elementID).detach().appendTo('body');	
  		}

		
		
		this.open();
        return this;
    };
    
    Modal.prototype = {
	    open : function(){
	    	if(!this.data.url){
		    	this.element.foundation('reveal', 'open');	
	    	}else{
	    		$.ajax({
	    			url: this.data.url,
	    			data: this.data.postdata,
	    			type:"POST",
	    			context: this,
	    			error: this.ajaxError
	    		}).done( function(d,s,o){
	    			d=$(d);
		    		if(s=="success"){
		    			this.element.html(d);
			    		if(this.element.find(".close-reveal-modal").length == 0){ 
				    		this.element.append($("<a class='close-reveal-modal'>&#215;</a>"));
			    		}
		    			this.element.foundation('reveal','open');
		    			this.onReady();
		    		}else this.ajaxError();
	    		});
	    	}
	    	$(".at4m-dock").hide(); //addthis floats over some forms on phones
	    },
	    
	    close: function(){
		  this.element.foundation('reveal','close'); 
	    },
	    
	    onCloseEvent: function(e){
		  $(".at4m-dock").show();
	    },
	    
	    ajaxError : function(){
			$(".reveal-modal-bg").remove();
	    	$("<div class='reveal-modal expand'><h2>Oops.</h2><p class='lead'>There was an error communicating with the server.</p><p>We'd like to fix this, and get you where you were going. Please let us know about this problem by e-mailing <a href='mailto:hello@brittlebarn.com'>hello@brittlebarn.com</a>.</p><a class='close-reveal-modal'>&#215;</a></div>").appendTo('body').foundation('reveal','open');
	    }
    }
    
    brittlebarn.Modal = Modal;
    
    
    
    
    
    
    /*
	 * brittlebarn.Connect - manages the connect form
	 *
	 * @author Matt Lima
	 */


	/*
	*	   id: The resource ID
	*/
    function Connect(id) {
    	if(typeof(id)=='undefined') return;
    	this.id = id;
/*     	logEvent_OPENCONNECTFORM(id); */
/* 		Load the modal */
        this.modal = new brittlebarn.Modal("connect-to-vendor","connect_to_vendor.php",{"rid":id},{"modalClass":"medium","onReady":$.proxy(this,'init')} );

		
    }

    /*
     * Prototype of ContactThisVendor
     */
    Connect.prototype = {

        init : function() {
            new brittlebarn.Log("OPENCONNECTFORM", this.id); // BB_ClickTracker
            $(document).foundation('abide','events');
            this.modal.element.find('a[rel=privacy]').click( $.proxy(this, 'showPrivacy') );
            this.modal.element.find('[rel=submit]').click( $.proxy(this, 'submit') );
        },
        

/*

        render : function() {

            var rules = {
                cfm_first_name: "required",
                cfm_last_name: "required",
                cfm_сompany: "required",
                cfm_phone: { required:function(){ return($("input[name=cfm_email]").val()==""); }},
                cfm_email: { required:function(){ return($("input[name=cfm_phone]").val()==""); }, email:true }
            }

            var messages = {
                cfm_first_name: "Please enter your firstname",
                cfm_last_name: "Please enter your lastname",
                cfm_email: { required: "Please enter a valid email address"},
                cfm_phone: { required: "Please enter a valid phone number"}

            }

            brittlebarn.modules.global.Validate.create(this.uiPopupContainer.find('form'), rules, messages);


        },
*/


        showPrivacy : function(e) {
        	e.preventDefault();
            this.modal.element.find("#privacy-policy").slideDown();
            return false;
        },


        submit : function(e) {
        	e.preventDefault();
        	 
        	var form=this.modal.element.find('form');
        	//console.log(form.attr('id'));
/*         	if(!form.valid()) return; */
            $.post("/ajax/connect_to_vendor.php", form.serialize(), $.proxy(this,'processResponse'));
            
        },
        
        processResponse : function(data){ 
            	if(data.substring(0,2)=="OK"){
                //BKR: Sebastian - improve in future iterations, make more graceful
            	 $('#submitBtn').hide('0');
            	  $('#thanks').show('250', $.proxy(this.modal, 'close'));            	  
            	}else{
            		this.modal.element.html(data);
            
            	}
            }
        
    }

    brittlebarn.Connect = Connect;

    
    
    
    /*
	 * brittlebarn.Action - Interaction manager
	 *
	 * DOM elements with attribute data-action="x" are passed to this function on click
	 
	 The data-action value is a string that specifies which action to take. Additional data-action-x properties can be specified as parameters.
	 
	 
	 
	 * @author Matt Lima
	 */
	 
	 function Action(event){
		 if($(this).attr('href')=="#") event.preventDefault(); //prevent link action or anchor tag jump
		 
	 	 switch($(this).data('action')){
	 	 	case "add-to-mlist":
	 	 		return( new brittlebarn.addToMList($("[name=mlist-e]").val()) );
	 	 	break;
	 	 	case "modal":
	 	 		return( new brittlebarn.Modal($(this).data('action-id')) );
	 	 	break;
	 	 	case "connect":
	 	 		return( new brittlebarn.Connect( $(this).data('action-id') ) );
	 	 	break;
	 	 	case "addToFav":
	 	 		return( new brittlebarn.AddToFav( $(this).data('action-rid'), $(this).data('action-lid') ) );
	 	 	break;
	 	 	
	 	 		
	 	 }
	 }
	 
	 brittlebarn.Action = Action;
	 
	 
	 function addToMList(email){
	 	if(!email.match( /^[_A-Za-z0-9-]+(\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,6})$/ ) ){
		 	$("#mlist-response").addClass("alert").removeClass("success").html("That email address appears to be invalid").slideDown(500, $.proxy(this,'mlistUp'));
		 	return;
	 	}
		$.post("add-to-mlist.php",{"email":$("[name=mlist-e]").val()}, $.proxy(this, 'processResponse'));
		 
	 }
	 
	 addToMList.prototype = {
		 processResponse : function(data){
			 
			 var t = data.split("|");



			 switch(t[0]){
				case "":
					$("#mlist-response").addClass("alert").removeClass("success").html("An error occured. Please let us know at <a href='mailto:hello@brittlebarn.com'>hello@brittlebarn.com</a>").slideDown(500, $.proxy(this,'mlistUp'));
				break;
				case "0":
					$("#mlist-response").addClass("alert").removeClass("success").html(t[1]).slideDown(500, $.proxy(this,'mlistUp'));
				break;
				case "1":
					$("#mlist-response").addClass("success").removeClass("alert").html(t[1]).slideDown(500);
					$("#mlist-signup").slideUp();
				break;				 
			 }
		 },
		 
		 mlistUp : function(){
			 setTimeout("$('#mlist-response').slideUp();", 3000);
		 }
	 }
	 
	 
	 brittlebarn.addToMList = addToMList;
	 
})(window || this);



// Add this configuration
var addthis_config={

	"data_track_clickback":true,
	"data_track_addressbar":true,
	"ui_use_css":false
	
};


//set up foundation
$(document).foundation()
.foundation('reveal', { animationSpeed: 125, close: brittlebarn.Modal.prototype.onCloseEvent } ) // Make Reveal Animations not take forever, make sure we can do cleanup on close  //
.foundation('abide', {
    patterns: {
      email: /^[_A-Za-z0-9-]+(\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,6})$/
    }
  });
  
$(document).ready(function(){
	$("[rel=add-to-cart],[rel=go-to-cart]").click(function(e){
		new brittlebarn.Modal('add-to-cart','ajax/add-to-cart.php',{},{modalClass:'medium'});
		e.preventDefault();
	});
	$("[name=mlist-e]").keypress(function(e){
		if(e.which==13){
			new brittlebarn.addToMList($("[name=mlist-e]").val());
			return false;
		}
	});
	
	$(document).on("click","[data-action]", brittlebarn.Action );
	$.getScript("//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-52741830692226e6",function(){ 
		
		  addthis.layers({
		    'theme' : 'transparent',
		    'share' : {
		      'position' : 'left',
		      'numPreferredServices' : 5
		    }, 
		    'follow' : {
		      'services' : [
		        {'service': 'facebook', 'id': 'brittlebarn'},
		        {'service': 'twitter', 'id': 'brittlebarn'},
		        /* {'service': 'google_follow', 'id': '100526909136236901329'}, */
		        /* {'service': 'linkedin', 'id': 'bizbash', 'usertype': 'company'}, */
		        /* {'service': 'pinterest', 'id': 'bizbash'}, */
		        {'service': 'instagram', 'id': 'brittlebarn'}
		      ]
		    }   
		  });

		
	});

});
