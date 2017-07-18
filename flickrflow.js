
function Flickrflow(fid)
{ // javascipt object
	
	this.init = function(){
		this.bind_scroll();
	}
	
	
	this.bind_scroll = function(){
		var ff = this;
		$(window).scroll(function () { 
			var frame = $('#flickrflow_frame').height();
			//var body = $(document).height();
			var scrollTop = $(window).scrollTop();
	
			var offset = $('#flickrflow_frame').offset();
			var scroll_procent = (100*(scrollTop - offset.top)/frame);
			if ( scroll_procent>66 ) {
				$(window).unbind('scroll');
				ff.load_ff_frame(ff.ff_frame+1);
				ff.ff_frame++;
			}
			//$('#scrollframe').html((100*(scrollTop - offset.top)/frame) + '%<br/>' +  ff_frame);
			//alert(scrollTop + '/' + body);
		});
	}
		
	
	this.load_ff_frame = function(frame_nr) {
		var ff = this;

		// Ajax magic
		var data = {
			action: 'flickr_flow_action',
			fid: this.fid,
			ff_p: frame_nr
		};
		
		jQuery.post(this.ajaxurl, data, function(response) {
			//alert('resp: ' + response );
			$('#flickrflow_frame').append(response);
			ff.bind_scroll();
		});
		
		/* Oude versie zonder wordpress ajax => veel trager
		$.get(window.location.pathname, { ff_p: frame_nr }, function(data){
			
			var ff_html = $(data).find('#flickrflow_frame').html();
			$('#flickrflow_frame').append(ff_html);
			bind_scroll();
			//alert("Data Loaded: " + data);
		});
		*/
		
	}
	
	this.fid = fid;
	this.ff_frame = 1;
	var $ = jQuery.noConflict();
}