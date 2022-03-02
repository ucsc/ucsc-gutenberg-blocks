function is_other(element, other) {
  if (element.target.value === 'other') {
    other.style.display = 'block';
  } else {
    other.style.display = 'none';
  }
}

var affiliation = document.getElementById('affiliations');
affiliation.addEventListener('change', e => { is_other(e, document.getElementById('affiliation_other_div')) });

var topic = document.getElementById('topic');
topic.addEventListener('change', e => { is_other(e, document.getElementById('topic_other_div')) });

var feedbackform = document.getElementById('feedback-form');
feedbackform.addEventListener("submit", function (e) {
  e.preventDefault();
  grecaptcha.ready(function () {
    grecaptcha.execute('6LegGaceAAAAAAK4bYxcVAPjPzv4UYsLIZqS5fgK', { action: 'submit' }).then(function (token) {
      document.getElementById('feedbackform_token').value = token;

      var formData = new FormData(feedbackform);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '/wp-json/ucscgutenbergblocks/v1/feedbackform/', true);
      xhr.onload = function () {
        if (this.status == 200) {
          var response = JSON.parse(this.responseText);
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
  });

});