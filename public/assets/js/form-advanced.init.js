$(document).ready(function() {

  $('input#titleNoti').maxlength({
    threshold: 20,
    warningClass: "badge bg-info",
    limitReachedClass: "badge bg-warning"
  });
  $('input#subTitleNoti').maxlength({
    threshold: 20,
    warningClass: "badge bg-info",
    limitReachedClass: "badge bg-warning"
  }); 
});

  
