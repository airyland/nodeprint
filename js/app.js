$(function(){
var content=$('#content');
var sidebar=$('#sidebar');
var app=Backbone.Router.extend({
routes:{
"!/t/:id/:page":"getSinglePost",
"!/node/:slug":"getSingleNode",
"!/node/:slug/add":"addPost",
"!/search/:query/:page":"getSearchResult",
"!/member/:username":"getProfile",
"!/register":"register",
"!/login":"login",
"!/setting":"getSetting",
"!/":"home",
"!/messages":"getMessages"
},

initialize:function(){
var profiles=new profile();
//var status=profile.get('status');
//console.log(status);
profiles.on("change:status", function(model, status) {
  if(status){
  var logined=new profileView();
  }
});
},

home:function(){

},

addPost:function(slug){
$($('#add-post-template').html()).appendTo($('#add-post-box'));
},


getSetting:function(){
alert('setting');
},

checkLogin:function(){
$.get('api/user/status');
},

getSinglePost:function(id,page){
comment=new commentList();
if(!page) page=1;
comment.fetch({data:{post_id:id,page:page}});
},

getSearchResult:function(query,page){

},

getSingleNode:function(slug){
var nodeInfo=new node();
nodeInfo.fetch({
data:{node_name:slug},
success:function(res){
//alert(typeof res);
//alert(res['node_id']);
text=JSON.stringify(res);
json=JSON.parse(text);
var nodeViewer=new nodeView();
nodeViewer.render(json);

}
});
},



register:function(){
var register=new registerView();

},

login:function(){
var login=new loginView();

},

getProfile:function(username){
console.log('hello');
//alert(username);
var members=new member();
//member.fetch({data:{user_name:username}});
}

});


var node=Backbone.Model.extend({
url:'api/node/info'
});

var nodeView=Backbone.View.extend({
template:$('#node-info-template').html(),
el:'#node-detail',
render:function(data){
var html=Mustache.render(this.template,data);
$(this.el).append(html);
}

});

//register view
var registerView=Backbone.View.extend({
template:$('#register-template').html(),
el:content,
render:function(){
content.html(this.template);
sidebar.empty();
return this;
},
events:{
'blur #user-name,keyup #user-name'     :"checkUserName",
'click #do-register'                                          :"register"

},
initialize:function(){
this.render();
},
checkUserName:function(){

},
register:function(){

}

});

//login view
var loginView=Backbone.View.extend({
el:content,
template:$('#login-template').html(),
render:function(){
content.html(this.template);
return this;
},
initialize:function(){
this.render();
}

});
//post model

var post=Backbone.Model.extend({
default:function(){
 return{
id:0,
title:'',
content:''
}
},
initialize:function(){
}
});

//post collection
var postList=Backbone.Collection.extend({
model:post,
});


//comment model
var comment=Backbone.Model.extend({
default:{
'id':0,
'replyTo':0,
'author':'',
'content':''
},
urlRoot:'/api/comment/get'
});

var commentList=Backbone.Collection.extend({
model:comment,
postId:0,
url:'/api/comment/get',
console:function(){
alert(this.url);
}
});

var commentView=Backbone.View.extend({


});


//post list view
var postListView=Backbone.View.extend({
tagName:"li",
//template:_.template($('#post-list-template').html()),
events:{
}

});


/**
*profile
*
*/
var profile=Backbone.Model.extend({
defaults:{
"status":false,
"userId":0,
"userName":''
},
url:'api/user/my',
parse:function(res){
this.set({status:res.error,userId:res.user_id,userName:res.user_name});
//console.log(this.get('status'));
},
initialize:function(){
this.fetch();
}
});

var profileView=Backbone.View.extend({
template:$('#profile-template').html(),
el:'#profile-box',
render:function(){
$('#site-nav').html($('#has-logined-template').html());
$(this.template).appendTo($(this.el)).hide().slideDown('fast');
return this;
},
initialize:function(){
this.render();
}
});


var member=Backbone.Model.extend({
url:'api/user/profile',
parse:function(res){
var memberViewer=new memberView();
memberViewer.render(JSON.stringify(res));
},
initialize:function(){

this.fetch();
}
});


var memberView=Backbone.View.extend({
template:$('#member-profile-template').html(),
el:'#member-profile',
render:function(data){
var html=Mustache.render(this.template,JSON.parse(data));
$(this.el).empty();
$(html).appendTo($(this.el)).hide().fadeIn();
console.log(html);
return this;
},
initialize:function(){

}
});




new app();
Backbone.history.start();
//Backbone.history.start({pushState: true});
//alert(comment.urlRoot);
//comment.console();
//console.log('fire');
});