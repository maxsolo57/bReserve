String.prototype.lpad02 = function() {var str = this;   while (str.length < 2) str = '0' + str; return str;}
String.prototype.unpad0 = function() {var str = this;   while ((str.length > 1)&& str[0]=='0') str = str.substr(1); return str;}

function WebCam() {  }


WebCam.prototype.init = function(type, lang, curtime, months, timeline, curp, curpic, pagepath, src_base, cache_base) {
  this.type = type;
  this.lang = lang;
  this.curtime = curtime;
  this.months = months;
  this.timeline = timeline;
  this.curp = curp;
  this.curtd = null;
  this.tlto = 0;
  this.loading = 0;
  this.animation = 0;
  this.slide_row;
  this.sfound = 0;
  this.ffound = 0;
  this.mode = '';
  this.plmode = 2;
  this.pagepath = pagepath;
  this.src_base = src_base;
  this.cache_base = cache_base;
  this.isdraged = false;
  this.isclicked = false;
  this.urlpostfix = '';
}

WebCam.prototype.vSetClip = function(_name,_url) {
  flowplayer().play( {url: this.src_base + _url });
}

WebCam.prototype.vInitPlayer = function() {
  var txs = this;
  flowplayer("vplayer", {src:"php/_libs/flowplayer/flowplayer-3.2.7.swf", wmode:'transparent'},
  {
    clip: {
      url: this.src_base + this.timeline[this.curp].p + '/'+ this.timeline[this.curp].f + '.flv', autoPlay: true, loop:true, scaling:'fit', onBeforeFinish: function() {
        switch (txs.plmode) {
          case 1: return false; break;
          case 2: txs.NextPic(); break;
        }
      }
      ,duration:0
    },
    plugins: {
      controls: {
        play:true, time:true, fullscreen:true, volume:false, mute:false
      }
    }
  });
}

WebCam.prototype.vplayMode = function(mode) {
  $('.pm'+this.plmode).removeClass('cur');
  this.plmode = mode;
  $('.pm'+this.plmode).addClass('cur');
}

WebCam.prototype.lActivateStream = function(url) {
  flowplayer().play({url:url + ".stream"});
  $('.streamswitch div').addClass('green');
  $('.streamswitch div.'+url).removeClass('green');
}

WebCam.prototype.lInitPlayer = function() {
  var txs = this;
  flowplayer("vplayer", "php/_libs/flowplayer/flowplayer-3.2.7.swf", {
    clip: {
      url: 'orlan.stream', provider: 'rtmp', live: 'true', scaling:'fit'
    },
    plugins: {
      controls: null, rtmp: { url: 'php/_libs/flowplayer/flowplayer.rtmp-3.2.3.swf', netConnectionUrl: 'rtmp://91.218.124.59:443/orlan', proxyType: 'CONNECT' }
    }
  });
}

WebCam.prototype.InitTimeline = function() {
  var txs = this;
  switch (this.type) {
    case 'l':
    $('.timeline_box,.like,.prevbutton,.nextbutton').hide();
    this.lInitPlayer();
    break;
    case 'v':
    case 'p':
    $('.timeline').draggable({
      axis: "x", stop: function(e){txs.dragStop(e)}, drag: function(e){txs.dragMove(e)}
    });
    $('.prevbutton').click(function(){txs.PrevPic()});
    $('.nextbutton').click(function(){txs.NextPic()});
    $('.like').click(function(_e){$(_e.target).effect("bounce", { times:3 }, 300); txs.Mark('like'); alert("Спасибо! Ваш голос учтен");});
    for (var i in this.timeline) {
      var o = this.CreateTD(i);
      if (o) {
        if (i==this.curp) o.addClass('cur');
        $('.timeline tr').append(o);
      }
    }
    if (this.type=='v') {
      this.vInitPlayer();
    }
    this.TL_Center(1);
    jQuery.ajax({url:BACKENDSCRIPT, dataType: "json", data:{func:'getoday2', t:this.type, y:this.timeline[this.curp].y, m:this.timeline[this.curp].n, d:this.timeline[this.curp].d} ,success:function(result) { txs.BuildToday(result.dt); txs.switchCalendar(txs.curp); }});
    break;
  }
  this.GetBest(9);
  $(".bestshotsmore").click(function(){ txs.GetBest()});
  $('.picimga').click(function(){ txs.Mark('zoom')});
}

WebCam.prototype.GetBest = function(cnt) {
  var txs = this;
  var from = $(".bestshots a").length;
  jQuery.ajax({url:BACKENDSCRIPT, dataType: "json",
  data:{func:'getbest', t:this.type, l:cnt?cnt:15, f:from}
  ,success:function(result) {
    var dt = result.dt;
    for (var i in dt) {
      $(".bestshots").append($("<a>").append($("<img>").attr('src', 'php/pics' + dt[i].p + '/' + dt[i].f + '.thumb.jpg')).attr('href', '?a=' + dt[i].t + "i" + dt[i].id + txs.urlpostfix));
    }
  }});
}

WebCam.prototype.Mark = function(act) {
  var dt = {url:'php/webcam_server_wr.php', data:{func:'mark', a:act, id:this.timeline[this.curp].id, t:this.type}};
  jQuery.ajax(dt);
}

WebCam.prototype.GetCurTime = function() {
  if (this.curp) return this.timeline[this.curp]
  else return this.curtime;
}

WebCam.prototype.Sync = function() {
  var url = this.page_path + '?pid=' + this.timeline[curp].id + '&md=' + this.mode + "&t=" + this.type + this.urlpostfix;
  document.location = url;
}

WebCam.prototype.TL_Mode = function(mode) {
  if (!this.timeline[curp]) alert(this.lang=='eng'?'No photo selected!':'Не выбрана ни одна из картинок!');
  var url = this.page_path + '?pid=' + this.timeline[curp].id + '&md=' + this.mode + this.urlpostfix;
  document.location = url;
}

WebCam.prototype.TL_fit = function() {
  $('.timeline').css({width:(this.timeline.length+1+(this.ffound?1:0)+(this.sfound?1:0))*TIMELINE_ITEM_WIDTH});
}

WebCam.prototype.TL_Center = function(noanim) {
  var txs = this;
  var tl = $('.timeline');
  this.TL_fit();
  var indx = $('td', tl).index($('.timeline td:has(.im'+this.timeline[this.curp].id+')'));
  if (indx > 0) {
    var pos = -(indx*TIMELINE_ITEM_WIDTH)+ $('.timeline_box').width()/2 - TIMELINE_ITEM_WIDTH/2;
    tl.stop();
    if (noanim || this.loading) {
      tl.css({left:pos});
      this.TL_touchbounds(1);
    } else {
      this.animation = 1;
      tl.animate({left:pos}, 300, function(){txs.animation = 0;txs.TL_touchbounds(1);});
    }
  }
}

WebCam.prototype.TL_touchbounds = function(noanim) {
  var tl = $('.timeline');
  var tlbw = $('.timeline_box').width();
  var pos = tl.position();
  var txs = this;
  if (pos.left>-TIMELINE_ITEM_WIDTH*3) {
    this.AddPics(0, this.timeline[0].id);
    if (this.sfound&&!noanim) {
      tl.animate({left:TIMELINE_ITEM_WIDTH}, 400, function(){txs.animation = 0;});
    }
  }
  else
  if (pos.left + tl.width() < tlbw+TIMELINE_ITEM_WIDTH*3) {
    this.AddPics(1, this.timeline[this.timeline.length-1].id);
    if (this.ffound&&!noanim) {
      pos =-(tl.width() - (tlbw-TIMELINE_ITEM_WIDTH*2))
      tl.animate({left:pos}, 400, function(){txs.animation = 0;});
    }
  }
}

WebCam.prototype.MarkEnd = function(dir) {
  if (dir) {
    if (this.ffound) return;
    this.ffound = 1;
    var o = this.CreateTD(-1);
    if (this.lang=='eng') {
      o.html("This is last taken.");
    } else {
      o.html("Это последнее с камеры.");
    }
    $('.timeline tr').append(o.addClass('edge'));
  } else {
    if (this.sfound) return;
    this.sfound = 1;
    var o = this.CreateTD(-1);
    if (this.lang=='eng') {
      o.html("This is first taken.");
    } else {
      o.html("Более ранних нет.");
    }
    $('.timeline tr').prepend(o.addClass('edge'));
  }
  this.TL_fit();
}

WebCam.prototype.onHourClick = function(_e) {
  var o=$(_e.target);
  var h=o.attr('class').substr(1);
  this.ActivateHour(h,1)
  o = $('.today .h'+h+' > *').length;
  if (o) {
    document.location = $('.today .h'+h+' *:nth-child('+Math.round(o/2)+') a').attr('href');
  }
}

WebCam.prototype.ActivateHour = function(h,anim) {
  $('.today [class^=h]').removeClass('cur');
  $('.today .h'+h).addClass('cur');
  var o = $('.today .hours');
  var b = $('.h'+h, o)
  if (!b.length) return;
  var p = b.position();
  var k = -(p.left + b.width()/2 - $('.today .hoursbox').width()/2)
  if (anim)
  o.animate({left:k});
  else
  o.css({left:k});
}

WebCam.prototype.BuildToday = function(t) {
  var day=$('.today');
  day.empty().append($("<DIV>").addClass("hoursbox")).append($("<DIV>").addClass("minutes"));
  $('.hoursbox').append($("<DIV>").addClass("hourstitle").html("часы"));
  $('.hoursbox').append($("<DIV>").addClass("hours"));
  for (var h in t) {
    var hour = t[h];
    $('.hours', day).append($("<DIV>").addClass("h"+h).html(h));
    var o=$("<SPAN>").addClass("h"+h);
    o.appendTo($('.minutes', day));
    for (var m in hour) {
      var oid = hour[m];
      o.append($("<SPAN>").addClass("i"+oid).html("<a href="+this.pagepath + "?a=" + this.type+'i'+oid+">"+m+"</a>"));
    }
  }
  $('.minutes').append($("<DIV>").addClass("minutestitle").html("минуты"));
  var txs = this;
  $('.hours', day).click(function(_e){txs.onHourClick(_e)});
  var h=this.timeline[this.curp].h;
  h=h.unpad0()
  this.ActivateHour(parseInt(h));
}

WebCam.prototype.switchCalendar = function(ne,ol) {
  var ch = typeof(ol)!='undefined'?this.timeline[ol].h:-1;
  var nh = this.timeline[ne].h;
  if (typeof(ol)!='undefined') $('.i'+this.timeline[ol].id).removeClass('cur');
  var cur=$('.i'+this.timeline[ne].id);
  if (cur.length) { cur.addClass('cur');
  if (ch != nh) {
    this.ActivateHour(parseInt(nh.unpad0()));
  }
  return;
}
var txs = this;
var day = this.timeline[ne].d;
if (!day) return;
var td =  $('.d'+parseInt(day));
if (!td.length) return;
td.addClass('cur');
if (typeof(ol)!='undefined') {
  $('.d'+parseInt(this.timeline[ol].d.unpad0())).removeClass('cur');
}
jQuery.ajax({url:BACKENDSCRIPT, dataType: "json",
data:{func:'getoday2', t:this.type, y:this.timeline[ne].y, m:this.timeline[ne].n, d:day}
,success:function(result) {
  txs.BuildToday(result.dt);
  txs.switchCalendar(ne);
}});
}

WebCam.prototype.ActivatePic = function(num) {
  var txs = this;
  if (!this.timeline[num]) return 0;
  if (this.type == 'v') {
    this.vSetClip('Problems???', this.timeline[num].p + '/' + this.timeline[num].f + '.flv');
  } else {
    var txstimeline = this.timeline[num].p + '/'+this.timeline[num].f;
    $('.loadproxy').load(function(){
      var src = $(this).attr('src');
      $('.bigthumb').attr('src', src);
      $('.ogmetaimg').attr('content', src);
      $('.picimga').attr('href', txs.src_base + txs.timeline[num].p + '/'+txs.timeline[num].f + '.jpg');
      $(this).unbind('load');
    }).attr('src', this.cache_base + txstimeline + '.main.jpg');
  }
  this.switchCalendar(num,this.curp);
  $('.im'+this.timeline[this.curp].id).parent().removeClass('cur');
  $('.im'+this.timeline[num].id).parent().addClass('cur');
  this.curp = num;
  this.TL_Center();
  return 1;
}

WebCam.prototype.PicClick = function(obj) {
  var id = $(obj).attr('class');
  var num = -1;
  id = parseInt(id.substr(2));
  for (var i=0; i< this.timeline.length; i++) {
    if (this.timeline[i].id==id) { num = i; break;}
  }
  if (num>=0)  {
    this.ActivatePic(num);
    this.Mark('click');
  }
}

WebCam.prototype.NextPic = function() { return this.ActivatePic(this.curp+1); }

WebCam.prototype.PrevPic = function() { return this.ActivatePic(this.curp-1); }

WebCam.prototype.AddPics = function(dir,limit,clb) {
  var txs = this;
  if (dir&&this.ffound) return;
  if (!dir&&this.sfound) return;
  if (this.loading) return;
  this.loading = 1;

  // document.write("<br>dir=" + dir + "&limit=" + limit + "&mode=" + this.mode + "&t=" + this.type + "&cday=" + this.curtime.d);
  // dir=1&limit=6191&mode=&t=v&cday=26

  jQuery.ajax({url:BACKENDSCRIPT,
    dataType: "json",
    data:{func:'getpics', dir:dir, limit:limit, mode:this.mode, t:this.type,
    cday:this.curtime.d},
    success:function(result) {
      txs.loading = 0;
      if (dir) txs.AddNextPics(result);
      else   txs.AddPrevPics(result);;
      if (clb) clb();
    }} );
  }

  WebCam.prototype.AddPrevPics = function(dat) {
    var dt = dat.dt;
    var o;
    var i;
    var pos = parseInt($('.timeline').css('left'));
    for (i=0; i < dt.length; i++) {
      this.timeline.unshift(dt[i]);
      o = this.CreateTD(0);
      $('.timeline tr').prepend(o);
    }
    if (dat.sf) { this.MarkEnd(0); }
    $('.timeline').css('left', pos - i*TIMELINE_ITEM_WIDTH);
    this.curp += i;
    this.TL_fit();
    this.TL_touchbounds();
  }

  WebCam.prototype.AddNextPics = function(dat) {
    var dt = dat.dt;
    for (var i in dt) {
      this.timeline.push(dt[i]);
      o = this.CreateTD(this.timeline.length-1);
      $('.timeline tr').append(o);
    }
    if (dat.ff) { this.MarkEnd(1); }
    this.TL_fit();
    this.TL_touchbounds();
  }

  WebCam.prototype.CreateTD = function(i) {
    var txs = this;
    var o = $("<TD>");
    if (!this.timeline[i]) return o;
    var o2 = $("<IMG>");
    o2.attr('src',this.cache_base + this.timeline[i].p + '/' + this.timeline[i].f + '.thumb.jpg');
    o2.addClass('im' + this.timeline[i].id);
    o2.mousedown(function(){txs.mouseDw()});
    o2.mouseup(function(){txs.mouseUp(this)});
    o.append(o2);
    o2 = $("<SPAN>").html("<br>" + this.timeline[i].d + " " + (this.months[this.timeline[i].n-1]) + " " + this.timeline[i].h + ":" + (this.timeline[i].m + "").lpad02());
    o.append(o2);
    return o;
  }

  WebCam.prototype.dragStop = function(){
    this.isdraged = false;
    this.TL_touchbounds();
  }

  WebCam.prototype.dragMove = function(){
    this.isdraged = true;
  }

  WebCam.prototype.mouseUp = function(obj){
    if (!this.isdraged) this.PicClick(obj)
  }

  WebCam.prototype.mouseDw = function(e,ui){
  }
