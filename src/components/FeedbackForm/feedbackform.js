function is_other(val, input_box) {
  var element = document.getElementById(input_box);

  if (val == 'other') {
    element.style.display = 'block';
  } else {
    element.style.display = 'none';
  }
}

var feedbackform = document.getElementById('feedback-form');
console.log(feedbackform);
feedbackform.addEventListener("submit", function (e) {
  e.preventDefault();
  var formData = new FormData(feedbackform);
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '/wp-json/ucscgutenbergblocks/v1/feedbackform/', true);
  xhr.onload = function () {
    if (this.status == 200) {
      var response = JSON.parse(this.responseText);
      console.log(response);
      if (response.success) {
        feedbackform.reset();
        feedbackform.style.display = 'none';
        document.getElementById('feedback-success').style.display = 'block';
      } else {
        document.getElementById('feedback-error').style.display = 'block';
        var missingFields = document.getElementById("missing-fields");
        missingFields.textContent = response.message;
      }
    }
  };
  xhr.send(formData);
});