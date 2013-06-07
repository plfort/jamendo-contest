function jamendoPlayer(musicPlayer){
this.name = "Jamendo";
this.cancelRequested = false;
this.interval;
this.musicPlayer = musicPlayer;
this.currentState = null;
this.soundmanagerPlayer = soundManager;
this.widgetElement =$("#jamendoWidgetContainer");
this.currentSoundObject=null;
this.currentSongId = null;

this.timeoutGetSong = null;
var self = this;
self.musicPlayer.cursor.progressbar();

this.requestCancel=function(){
	self.cancelRequested=true;
	if(self.currentSoundObject != null){
		loggerJamendo.debug('requestCanel currentSoundObject === null');
		self.currentSoundObject.destruct();
		self.cancelRequested=false;
	}
}

this.play = function(item) {
	var songId = item.entryId;
	self.currentSongId = songId;
	loggerJamendo.debug('Call play, cancelRequsted : '+item.title, songId);
	if(self.timeoutGetSong !==null){
		self.timeoutGetSong.clear();
	}
	self.timeoutGetSong = new Timer(function(){
	 	    	
	    	self.currentSoundObject=self.soundmanagerPlayer.createSound({
	  		  id: 'jam'+songId,
	  		  multiShot : false,
	  		  url: item.pluginProperties.url,
	  		  autoLoad: true,
	  		  autoPlay: true,
	  		  volume: self.musicPlayer.volume,
	  		  onload: function() {
	  			 loggerJamendo.debug("ONLOAD");
	  		  },
	  		  onplay:function(){
	  			  self.musicPlayer.enableControls();
	  				  			
		  			  self.musicPlayer.bindCursorStop(function(value) {
		  			
		  				  self.currentSoundObject.setPosition(value*1000);
		  				});
			 
	  		  },
	  		  onpause: function(){
	  			loggerJamendo.debug("ONPAUSE");
	  		
	  		  },
	  		  onstop: function(){
	  			loggerJamendo.debug("ONSTOP");
	  			 this.destruct();
	  			 self.musicPlayer.cursor.slider("option", "max", 0).progressbar('value',0);
	  		  },
	  		  onfinish: function(){
	  			loggerJamendo.debug("ONFINISH");
 			  
	  			  this.destruct();
	  			  self.musicPlayer.next();
	  		  },
	  		  whileloading: function(){
	  			 loggerJamendo.debug("duration :"+this.duration);
	  			  
	  			  self.musicPlayer.cursor.slider("option","max",this.duration/1000).progressbar('value',(this.bytesLoaded/this.bytesTotal)*100 );

	  		  },
	  		  whileplaying: function(){
	  		
	  			if(songId != self.currentSongId){
	  				this.destruct();
	  				return;
	  			}
			  	if(self.musicPlayer.cursor.data('isdragging')==false){
			  		self.musicPlayer.cursor.slider("value", this.position/1000);
			  	}
	  			
	  		  },
	  		  
	  		});

	},500);



};
this.stop = function(){
	loggerJamendo.debug('call stop soundmanager');	
	if(self.currentSoundObject!=null){
		loggerJamendo.debug('-- currentSoundObject !== null');	
		self.currentSoundObject.stop();	
	}else{
		loggerJamendo.debug('-- currentSoundObject === null');
	}
}

this.pause = function(){
	loggerJamendo.debug('call pause soundmanager');
	if(self.currentSoundObject!=null){
		self.currentSoundObject.pause();
	}
	
}

this.resume = function(){
	loggerJamendo.debug('call resume soundmanager');
	if(self.currentSoundObject!=null){
		self.currentSoundObject.resume();
	}
}
this.setVolume = function(value){
	loggerJamendo.debug('call setvolume soundmanager');
	if(self.currentSoundObject!=null){
		self.currentSoundObject.setVolume(value);
	}
}
}

$("body").on('musicplayerReady',function(event){
	event.musicPlayer.addPlugin('jam',new jamendoPlayer(event.musicPlayer));
});
$(document).ready(function(){
	$("#jamendo-menu").on('click','#loginJamendoBtn',function(event){
		
		$.get(Routing.generate('_jamendo_login'),function(response){
			if(response.success == true){
				 window.open(response.data.authUrl,"login_jamendo_popup","left=300,location=false,menubar=no, status=no, scrollbars=no, menubar=no, width=800, height=600");
			}
		},'json');
		return false;
		
	});
	
	$("#jamendo-menu").on('click','#logoutJamendoBtn',function(event){
		var currentItem = $(this);
		$.get(Routing.generate('_jamendo_logout'),function(response){
			if(response.success == true){
				currentItem.replaceWith(response.data.loginLink);
   		        $("#jam-playlist-container").empty();
			}
		},'json');
		return false;
		
	});

	
	$("#playlist-container").on('click','.showPlaylistJamendoBtn',function(event){
		var playlistElement = $(this).closest('.jam-playlist-item');
		var playlistName = $(this).html();
		var playlistAlias = playlistElement.data('alias');
		$.get(Routing.generate('_jamendo_playlist_songs',{'id':playlistElement.data('id')}),function(response){
			if(response.success == true){
				renderResult(response.data.tracks,{tpl:'trackNoSortTpl',tabName:playlistName,alias:playlistAlias});
            	$("#wrap").animate({scrollTop:0});
	
			}else{
				loggerGrooveshark.debug('Error with jamendo');
			}
		},'json');
		return false;
	});
	
	$("#playlist-container").on('click','.playPlaylistJamendoBtn',function(event){
		
		$.get(Routing.generate('_jamendo_playlist_songs',{'id':$(this).closest('.jam-playlist-item').data('id')}),function(response){
			if(response.success == true){
				musicPlayer.removeAllSongs();
				musicPlayer.addSongs(response.data.tracks);
                musicPlayer.play();
			}else{
				loggerGrooveshark.debug('Error with jamendo');
			}
		},'json');
		return false;
	});

	


    $(".jam-playlist-item").draggable(draggableOptionsPlaylistListItem);
});


droppedHookArray['jam-playlist'] = function(droppedItem,callback){
		var playlistId=droppedItem.data('id');
		$.get(Routing.generate('_jamendo_playlist_songs',{'id':playlistId}),function(response){
            if(response.success==true){
                loggerJamendo.debug(response.data.tracks);
                callback(response.data.tracks);
                }
            },'json');
	
}

