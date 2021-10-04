$(document).ready(function () {
  $("#form").submit(function(e) {
    e.preventDefault();
    $(".loading").show();

    let req = new XMLHttpRequest();
    let formData = new FormData($('#form')[0]);

    for(var i = 0; i < document.getElementById("upload").files.length; i++){
      formData.append("file" + i, document.getElementById("upload").files[i]);
    }
    
    req.open("POST", 'post');
    req.send(formData);

    req.onreadystatechange = function() {
      if (req.readyState == XMLHttpRequest.DONE) {
        $(".loading").hide();
        console.log(req.responseText);
        if(req.responseText == 'success'){
          $('#form')[0].reset();
          resetFile();
          $('.toast-body').text('success');
          var toastElList = [].slice.call(document.querySelectorAll('.toast'))
          var toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, {delay:5000})
          })
          toastList.forEach(toast => toast.show());
        }else{
          $('.toast-body').text('failed');
          var toastElList = [].slice.call(document.querySelectorAll('.toast'))
          var toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, {delay:5000})
          })
          toastList.forEach(toast => toast.show());
        }
      }
    }
  });
});