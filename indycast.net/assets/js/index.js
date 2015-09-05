ev('ampm', function(val){
  if(val) {
    $("#start").val($("#start").val().replace(/\s*([ap]m)\s*/i, val))
  }
});

ev('', function(map) {
  var 
    start_time = "Pick the start time", 
    todo = [],
    phrase = "Choose the ",
    url = '',
    showname = '',
    single = '',
    parts = [],
    station = "Choose the station", 
    name = '',
    is_ready = false,
    fullday = "Pick the days";

  for(var key in map) {
    if(!map[key]) {
      continue;
    }

    $("#" + key + " a").removeClass("selected");

    if(key == 'day') {
      _.each(map[key], function(what) {
        $("#" + key + " a:contains(" + what + ")").addClass("selected");
      });
    } else if(key != 'name') {
      $("#" + key + " a:contains(" + map[key] + ")").addClass("selected");
      $("#" + key + " a[data='" + map[key] + "']").addClass("selected");
    }
  }

  if(map.name && map.station && map.ampm && map.day && map.day.length && map.start && map.duration) {
    is_ready = true;
    phrase = false;

    $("#podcast-done").removeClass('disabled');
  } else {
    $("#podcast-done").addClass('disabled');
    name = "You're almost done";
  }

  if(map.station) {
    station = map.station.toLowerCase();
  } else {
    todo.push('station');
  }

  if(map.day && map.day.length) {
    var _map = _.map(map.day, function(what) { return fullName[what] });
    if(map.day.length == 1) {
      fullday = _map[0];
    } else {
      fullday = _map.slice(0, -1).join(', ') + ' and ' + _.last(_map);
    }
  } else {
    todo.push("day(s) of week");
  }

  if(!map.duration) {
    todo.push('duration');
  }

  // #27: dump the spaces, make it lower case and avoid a double am/pm
  if(map.start) {
    start_time = map.start.replace(/\s+/,'').toLowerCase().replace(/[ap]m/, '') + map.ampm;
  } else {
    todo.push('start time');
  }

  if(map.name) {
    showname = map.name;
  } else {
    todo.push('name');
  }


  if (is_ready) {
    url = 'http://' + [
      'indycast.net',
      station,
      map.day.join(',') + " ",
      start_time,
      map.duration,
      encodeURI(map.name).replace(/%20/g,'_')
    ].join('/') + ".xml";

    single = url.replace(' ', '');
    parts = url.split(' ');
  } 

  if(todo.length) {
    if(todo.length > 1) {
      phrase += todo.slice(0, -1).join(', ') + ' and ' + todo.slice(-1)[0];
    } else {
      phrase += todo[0];
    }
    phrase += '.';
  } else {
    name = "You're Done."
    phrase = "Hit the green button.";
  }

  $("#podcast-done").attr({'href': single }).html(
    tpl.podcast({
      name: name,
      day: fullday,
      time: start_time,
      is_ready: is_ready,
      showname: showname,
      station: station,
      single: single,
      phrase: phrase,
      parts: parts
    })
  );
  $("#podcast-container").css({width: $("#podcast-url").width() + 30});
});

ev.test('start', function(v, cb, meta) {
  var res = time_re.test(v);
  
  if(res) {
    if(/[ap]m/.test(v)) {
      ev('ampm', v.slice(-2));
    } 
  } 
   
  $(meta.node)[(res ? 'remove' : 'add') + 'Class']('error');
  cb(res);
});

$(function() {
  tpl.podcast = _.template($("#tpl-podcast").html());

  $(".radio-group a").hover(function(){
    $("#description").html("<h2>" + this.innerHTML + "</h2>" + htmldo(this.getAttribute('desc'))).show();
  });

  if(isiDevice) {
    $("#podcast-url").on(listenEvent, function(){
      // why why why ipad...
      document.location = this.getAttribute('href');
    });
  }

  $("#station-query").on('keyup', function(){
    var query = this.value, show_count = [];
    
    $("#station li").each(function(){
      var to_test = this.firstChild.innerHTML;
      if(to_test.search(query) == -1) {
        $(this).hide();
      } else {
        show_count.push(to_test)
        $(this).show();
      }
    });

    if(show_count.length == 1) {
      ev('station', show_count[0]);
    }

  })

  // #23 - multiday recordings
  $("#day a").on(listenEvent, function(){
    ev.setToggle('day', this.innerHTML);
  });

  $(".group a").on(listenEvent, function(){
    var mthis = this;

    // This tricks stupid iDevices into not fucking around and screwing with the user.
    setTimeout(function(){
      var node = $(mthis).parentsUntil("div").last();
      ev(node[0].id, mthis.getAttribute('data') || mthis.innerHTML);
    },0)
  });

  $("#start,#name").bind('blur focus change keyup',function(){
    ev(this.id, this.value, {node: this});
  });
  
  for(var el in {start:1, name:1}) {
    var 
      $node = $("#" + el),
      val = $node.val();

    if(val) {
      ev(el, val, {node: $node.get(0)});
    }
  }
  
  ev.fire(['start', 'name']);
});
