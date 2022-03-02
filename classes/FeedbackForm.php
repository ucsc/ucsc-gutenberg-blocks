<?php

define('SECRET_KEY', '6LegGaceAAAAAE6q7ZQEaYeYZ1K9-sCBwudqbl5e');

const defaults = array(
  'name' => 'Name',
  'namePlaceholder' => 'Your name',
  'email' => 'Email',
  'emailPlaceholder' => 'Your email',
  'affiliation' => 'Who are you?',
  'affiliationOther' => 'Other',
  'affiliationOtherPlaceholder' => 'Your affiliation',
  'topic' => 'What is your comment about?',
  'topicOther' => 'Other',
  'topicOtherPlaceholder' => 'Your topic',
  'message' => 'Message',
  'messagePlaceholder' => 'Your message',
);

function checkDefault($attr, $key)
{
  if ($attr[$key] == '') {
    echo defaults[$key];
  } else {
    echo $attr[$key];
  }
}

class FeedbackForm
{

  function __construct()
  {
    add_action('init', array($this, 'adminAssets'));
    add_action('wp_enqueue_scripts', array($this, 'registerPluginStyles'));

    add_action('rest_api_init', function () {
      // create a new rest route for posting the feedback form
      register_rest_route('ucscgutenbergblocks/v1', '/feedbackform', array(
        'methods' => 'POST',
        'callback' => array($this, 'submit_feedback')
      ));
    });
  }

  function registerPluginStyles()
  {
    $file = '../src/components/FeedbackForm/feedbackform.css';
    wp_register_style(
      'feedbackform',
      plugins_url($file, __FILE__),
      array(),
      filemtime(plugin_dir_path(__FILE__) . $file)
    );
    wp_enqueue_style('feedbackform');

    $file = '../src/components/FeedbackForm/feedbackform.js';
    wp_register_script(
      'feedbackformjs',
      plugins_url($file, __FILE__),
      array(),
      filemtime(plugin_dir_path(__FILE__) . $file),
      true
    );
    wp_enqueue_script('feedbackformjs');
  }

  function adminAssets()
  {
    register_block_type('ucscblocks/feedback', array(
      'editor_script' => 'ucscblocks',
      'render_callback' => array($this, 'theHTML')
    ));
  }

  function theHTML($attributes)
  {
    ob_start(); ?>

    <script src="https://www.google.com/recaptcha/api.js?render=6LegGaceAAAAAAK4bYxcVAPjPzv4UYsLIZqS5fgK"></script>


    <div id="feedback-error" style="display:none;padding-bottom: 25px;">
      <h4 id="missing-fields" style="color:red;"></h4>
    </div>
    <form id="feedback-form">
      <div class="container">

        <h4 id="missing-fields" style="color:red;"></h4>

        <div class="label-1">
          <label class="form-label" for="name"><?php checkDefault($attributes, 'name'); ?></label>
        </div>
        <div class="input-1">
          <input type="text" class="form-input" id="name" name="name" placeholder="<?php checkDefault($attributes, 'namePlaceholder'); ?>" required>
        </div>

        <div class="label-2">
          <label class="form-label" for="email"><?php checkDefault($attributes, 'email'); ?></label>
        </div>
        <div class="input-2">
          <input type="email" class="form-input" id="email" name="email" placeholder="<?php checkDefault($attributes, 'emailPlaceholder'); ?>" required>
        </div>

        <div class="label-3">
          <label class="form-label" for="affiliations"><?php checkDefault($attributes, 'affiliation'); ?></label>
        </div>
        <div class="input-3">
          <select class="form-input" id="affiliations" name="affiliations" required>
            <option value="student">UCSC Student</option>
            <option value="faculty">UCSC Faculty</option>
            <option value="staff">UCSC Staff</option>
            <option value="alumni">UCSC Alumni</option>
            <option value="other">Other</option>
          </select>
          <div id="affiliation_other_div" class=form-label" style="display: none;">
            <label for="affiliation_other"><?php checkDefault($attributes, 'affiliationOther'); ?></label>
            <input type="text" class="form-input" aria-label="Affiliation Other" id="affiliation_other" name="affiliation_other" placeholder="<?php checkDefault($attributes, 'affiliationOtherPlaceholder') ?>" />
          </div>
        </div>

        <div class="label-4">
          <label class="form-label" for="topic"><?php checkDefault($attributes, 'topic'); ?></label>
        </div>
        <div class="input-4">
          <select class="form-input" id="topic" name="topic" required>
            <option value="accessibility">Accessibility</option>
            <option value="content">Content</option>
            <option value="design">Design</option>
            <option value="navigation">Navigation</option>
            <option value="other">Other</option>
          </select>
          <div id="topic_other_div" class=form-label" style="display: none;">
            <label for="topic_other"><?php checkDefault($attributes, 'topicOther'); ?></label>
            <input type="text" class="form-input" aria-label="Topic Other" id="topic_other" name="topic_other" placeholder="<?php checkDefault($attributes, 'topicOtherPlaceholder') ?>" />
          </div>
        </div>

        <div class="label-5">
          <label class="form-label" for="message"><?php checkDefault($attributes, 'message'); ?></label>
        </div>
        <div clas="input-5">
          <textarea class="form-input" id="message" name="message" rows="3" placeholder="<?php checkDefault($attributes, 'messagePlaceholder'); ?>" required></textarea>
        </div>

        <input type="hidden" name="block_level_to" value="<?php echo $attributes['to']; ?>" />
        <input type="hidden" id="feedbackform_token" name="feedbackform_token" value="" />

        <div class="submit">
          <button id="feedbackform-submit" class="form-input" type="submit" class="btn btn-primary">Submit</button>
        </div>

      </div>
    </form>
    <div id="feedback-success" style="display:none;">
      <p>Thank you for your feedback!</p>
    </div>
    </div>

<?php $output = ob_get_contents();
    ob_end_clean();

    return $output;
  }

  function submit_feedback($request)
  {
    $pass = true;
    $fields = ["name", "email", "affiliations", "affiliation_other", "topic", "topic_other", "message", "block_level_to", "feedbackform_token"];
    $skip_validation = ["affiliation_other", "topic_other", "block_level_to", "feedbackform_token"];
    $empty_fields = [];
    $sanitized_fields = [];
    $response = [];
    $data = $request->get_params();

    if (isset($data['feedbackform_token'])) {
      $token = sanitize_text_field($data['feedbackform_token']);
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => SECRET_KEY, 'response' => $token)));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $curl_response = curl_exec($ch);
      curl_close($ch);
      $decoded = json_decode($curl_response, true);
    } else {
      $pass = false;
      $response['message'] = "Something went wrong, please try again.";
    }

    // validate the request data
    foreach ($fields as $field) {
      if (in_array($field, $skip_validation)) {
        continue;
      }
      if (empty($data[$field])) {
        $empty_fields[] = $field;
        $pass = false;
      }
    }

    if (!$pass) {
      $response['message'] = 'Please fill out the following fields: ' . implode(', ', $empty_fields);
    }

    if ($pass) {
      // sanitize the request data
      foreach ($fields as $field) {
        if ($field == 'email') {
          $sanitized_fields[$field] = sanitize_email($data[$field]);
        } else {
          $sanitized_fields[$field] = sanitize_text_field($data[$field]);
        }
      }

      error_log("block_level_to: " . $sanitized_fields['block_level_to']);
      // send the email
      if ($sanitized_fields['block_level_to'] != '') {
        $to = $sanitized_fields['block_level_to'];
      } else if (get_option('feedbackform_email_to') != '') {
        $to = get_option('feedbackform_email_to');
      } else {
        $to = 'tmdanh@ucsc.edu';
      }
      $response['email'] = $to;

      $subject = 'Feedback Form Submission';
      $message = 'Name: ' . $sanitized_fields['name'] . "\n";
      $message .= 'Email: ' . $sanitized_fields['email'] . "\n";
      $message .= 'Affiliation: ' . $sanitized_fields['affiliations'] . "\n";
      if ($sanitized_fields['affiliations'] == 'other') {
        $message .= 'Affiliation Other: ' . $sanitized_fields['affiliation_other'] . "\n";
      }
      $message .= 'Topic: ' . $sanitized_fields['topic'] . "\n";
      if ($sanitized_fields['topic'] == 'other') {
        $message .= 'Topic Other: ' . $sanitized_fields['topic_other'] . "\n";
      }
      $message .= 'Message: ' . $sanitized_fields['message'] . "\n";

      $headers = array('Content-Type: text/plain; charset=UTF-8');

      if (wp_get_environment_type() == 'production') {
        if ($decoded['score'] >= 0.5 && !wp_mail($to, $subject, $message, $headers)) {
          $response['message'] = 'There was an error sending your email. Please try again later.';
          $pass = false;
        }
      }
    }

    if ($pass) {
      $response['message'] = "Thank you for your feedback!";
      $response['success'] = true;
    } else {
      $response['success'] = false;
      // send list of missing fields back to the client
      $response['missing_fields'] = $empty_fields;
    }

    return new WP_REST_Response($response);
  }
}
