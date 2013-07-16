(function($) {
	"use strict";

  var SimpleSidebars = function(){
        this.widget_wrap = $('.widget-liquid-right');
        this.widget_area = $('#widgets-right');
        this.widget_template = $('#simple-add-widget-template');
       
        this.add_form_html();
        this.add_del_button();
        this.bind_events();
  };
  
    SimpleSidebars.prototype = {
    
      add_form_html: function() {
          this.widget_wrap.append(this.widget_template.html());
          this.widget_name = this.widget_wrap.find('input[name="simple-add-widget-input"]');
          this.nonce       = this.widget_wrap.find('input[name="simple-nonce"]').val();
      },
      
      add_del_button: function() {
          this.widget_area.find('.sidebar-simple-custom .sidebar-name').append('<span class="simple-sidebar-area-delete"></span>');
      },
      
      bind_events: function() {
          this.widget_wrap.on('click', '.simple-sidebar-area-delete', $.proxy( this.delete_sidebar, this));
      },
      
      //delete the sidebar area with all widgets within, then re calculate the other sidebar ids and re save the order
      delete_sidebar: function(e) {
        
        var widget      = $(e.currentTarget).parents('.widgets-holder-wrap:eq(0)'),
            title       = widget.find('.sidebar-name h3'),
            spinner     = title.find('.spinner'),
            widget_name = $.trim(title.text()),
            obj         = this;
        widget.addClass('closed');
        spinner.css('display', 'inline-block');
        $.ajax({
          type: "POST",
          url: window.ajaxurl,
          data: {
             action: 'wp_ajax_delete_simple_sidebar',
             name: widget_name,
             _wpnonce: obj.nonce
          },
          
          success: function(response) {     
           if(response.trim() == 'sidebar-deleted') {
              widget.slideUp(200, function(){
                  
                $('.widget-control-remove', widget).trigger('click'); //delete all widgets inside
                widget.remove();
                
                obj.widget_area.find('.widgets-holder-wrap .widgets-sortables').each(function(i) { //re-calculate widget ids
                    $(this).attr('id','sidebar-' + (i + 1));
                });
                
                wpWidgets.saveOrder();
              });
           } 
          }
        });
      }
    };
  
  $(function() {
    new SimpleSidebars();
  });
  
})(jQuery);  