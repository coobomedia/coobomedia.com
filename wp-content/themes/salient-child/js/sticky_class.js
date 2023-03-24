jQuery(document).ready(function($){

$(window).load(function(){
  //console.log($(this).pageYOffset);
  if(this.pageYOffset > 10) {
      $('body').addClass('sticky_body');
  } else {
       $('#body').removeClass('sticky_body');
  }
  

});

$(window).scroll(function(){
    if ($(this).scrollTop() > 10) {
       $('body').addClass('sticky_body');
    } else {
       $('body').removeClass('sticky_body');
    }
});

});


