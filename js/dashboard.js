/* globals Chart:false, feather:false */

/*(function () {
  'use strict'

  feather.replace({ 'aria-hidden': 'true' })

  // Graphs
  var ctx = document.getElementById('myChart')
  // eslint-disable-next-line no-unused-vars
  var myChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday'
      ],
      datasets: [{
        data: [
          15339,
          21345,
          18483,
          24003,
          23489,
          24092,
          12034
        ],
        lineTension: 0,
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        borderWidth: 4,
        pointBackgroundColor: '#007bff'
      }]
    },
    options: {
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: false
          }
        }]
      },
      legend: {
        display: false
      }
    }
  })
})()*/



+ function ($) {
  'use strict';

  var dt = new DataTransfer();
  var fileUpload = document.getElementById("upload");
  var dvPreview = document.getElementById("filePreview");

  var dropZone = document.getElementById('drop-zone');

  window.resetFile = resetFile;
  function resetFile(ev) {
    dt.items.clear();
    fileUpload.onchange();
  }
    
  var startUpload = function (files) {
    console.log(files)
  }

  dropZone.ondrop = function (e) {
    e.preventDefault();
    this.className = 'upload-drop-zone';

    fileUpload.files = e.dataTransfer.files;
    fileUpload.onchange();

    //startUpload(e.dataTransfer.files)
  }

  dropZone.ondragover = function () {
    this.className = 'upload-drop-zone drop';
    return false;
  }

  dropZone.ondragleave = function () {
    this.className = 'upload-drop-zone';
    return false;
  }

  fileUpload.onchange = function () {
    var regex_img = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
    var regex_vid = /^([a-zA-Z0-9\s_\\.\-:])+(.mp4|.mov)$/;
    for(var i = 0; i < fileUpload.files.length; i++){
      if(regex_img.test(fileUpload.files[i].name.toLowerCase()) || regex_vid.test(fileUpload.files[i].name.toLowerCase())){
        dt.items.add(fileUpload.files[i]);
      }
    }

    fileUpload.files = dt.files;
    var count = 0;
    var divs = [];

    if(fileUpload.files.length == 0){
      dvPreview.innerHTML = "";
    }

    for(var i = 0; i < fileUpload.files.length; i++){
      const file = fileUpload.files[i];
      const id = i;
      var reader = new FileReader();
      reader.onload = function (e) {
        var div = document.createElement("div");
        div.classList.add('d-inline-block');
        div.classList.add('position-relative');
        div.classList.add('mx-1');
        div.setAttribute('draggable', true);
        div.setAttribute('ondrop', 'drop(event, ' + id + ')');
        div.setAttribute('ondragover', 'allowDrop(event)');
        div.setAttribute('ondragstart', 'drag(event, ' + id + ')');
        var img = document.createElement("img");
        img.height = "100";
        img.width = "100";
        if(regex_vid.test(file.name.toLowerCase())){
          img.src = 'fontawesome-free-5.15.4-web/svgs/solid/video.svg';
          var span = document.createElement("span");
          span.classList.add('align-middle');
          span.classList.add('video-name');
          span.classList.add('bg-white');
          span.classList.add('text-center');
          span.innerHTML = file.name;
          div.appendChild(span);
        }else{
          img.src = e.target.result;
        }
        var btn = document.createElement("button");
        btn.classList.add('align-top');
        btn.classList.add('img-close-btn');
        btn.classList.add('btn');
        btn.classList.add('btn-danger');
        btn.classList.add('btn-sm');
        btn.innerHTML = 'X';
        btn.setAttribute('onclick', 'imgRemove(' + id + ')');
        div.appendChild(btn);
        div.appendChild(img);
        divs[id] = div
      }
      reader.onloadend = function(e) {
        if(++count === fileUpload.files.length){
          dvPreview.innerHTML = "";
          for(var i = 0; i < fileUpload.files.length; i++){
            dvPreview.appendChild(divs[i]);
          }
        }
      }
      reader.readAsDataURL(file);
    }
  }

  window.imgRemove = imgRemove;
  function imgRemove(index){
    dt.items.clear();
    for(var i = 0; i < fileUpload.files.length - 1; i++){
      if(i < index){
        dt.items.add(fileUpload.files[i]);
      }else if(i >= index){
        dt.items.add(fileUpload.files[i + 1]);
      }
    }

    fileUpload.files = new DataTransfer().files;
    fileUpload.onchange();
  }

  window.allowDrop = allowDrop;
  function allowDrop(ev) {
    ev.preventDefault();
  }
  
  window.drag = drag;
  function drag(ev, index) {
    ev.dataTransfer.setData("index", index);
  }
  
  window.drop = drop;
  function drop(ev, index) {
    ev.preventDefault();
    var drag_id = ev.dataTransfer.getData("index");
    if(index < drag_id){
      dt.items.clear();
      for(var i = 0; i < fileUpload.files.length; i++){
        if(i < index){
          dt.items.add(fileUpload.files[i]);
        }else if(i == index){
          dt.items.add(fileUpload.files[drag_id]);
        }else if(i > index && i <= drag_id){
          dt.items.add(fileUpload.files[i-1]);
        }else if(i > drag_id){
          dt.items.add(fileUpload.files[i]);
        }
      }

      fileUpload.files = new DataTransfer().files;
      fileUpload.onchange();
    }else if(drag_id < index){
      dt.items.clear();
      for(var i = 0; i < fileUpload.files.length; i++){
        if(i < drag_id){
          dt.items.add(fileUpload.files[i]);
        }else if(i >= drag_id && i < index){
          dt.items.add(fileUpload.files[i+1]);
        }else if(i == index){
          dt.items.add(fileUpload.files[drag_id]);
        }else if(i > index){
          dt.items.add(fileUpload.files[i]);
        }
      }

      fileUpload.files = new DataTransfer().files;
      fileUpload.onchange();
    }else{
      return;
    }
  }
}();