<?php

/**
  Doc: a doc
 */
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
        'callback' => array($this, 'submit_feedback'),
        'permission_callback' => function() {return true;}
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

    <div class="feedback-div">
      <div id="feedback-error" style="display:none;padding-bottom: 25px;">
        <h4 id="missing-fields" style="color:red;"></h4>
      </div>
      <form id="feedback-form">
        <div class="form-group">
            <label for="name"><?php echo $attributes['name'] ?></label>
            <div id="name-group">
            <input type="text" class="form-control" id="name" name="name" placeholder="<?php echo $attributes['namePlaceholder']?>" required>
            </div>
        </div>
        <div class="form-group">
            <label for="email"><?php echo $attributes['email'] ?></label><br/>
            <input type="email" class="form-control" id="email" name="email" placeholder="<?php echo $attributes['emailPlaceholder']?>" required>
        </div>
        <div class="form-group">
            <label for="affilliations"><?php echo $attributes['affiliation'] ?></label><br/>
            <select class="form-control" id="affiliations" name="affiliations" required>
              <option value="student">UCSC Student</option>
              <option value="faculty">UCSC Faculty</option>
              <option value="staff">UCSC Staff</option>
              <option value="alumni">UCSC Alumni</option>
              <option value="other">Other</option>
            </select>
        </div>
        <div class=form-group">
            <input type="text" aria-label="Affiliation Other" id="affiliation_other" name="affiliation_other" placeholder="<?php echo $attributes['affiliationOtherPlaceholder']?>" style="display: none;"/>
        </div>
        <div class="form-group">
            <label for="topic"><?php echo $attributes['topic'] ?></label><br/>
            <select class="form-control" id="topic" name="topic" required>
              <option value="accessibility">Accessibility</option>
              <option value="content">Content</option>
              <option value="design">Design</option>
              <option value="navigation">Navigation</option>
              <option value="other">Other</option>
            </select>
        </div>
        <div class="form-group">
          <label for="message"><?php echo $attributes['message'] ?></label><br/>
          <textarea class="form-control" id="message" name="message" rows="3" placeholder="<?php echo $attributes['messagePlaceholder']?>" required></textarea><br/>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
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
    $fields = ["name", "email", "affiliations", "topic", "message"];
    $empty_fields = [];
    $sanitized_fields = [];
    $response = [];
    $data = $request->get_params();

    // validate the request data
    foreach ($fields as $field) {
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

      // send the email
      $to = 'tmdanh@ucsc.edu';

      $subject = 'Feedback Form Submission';
      $message = 'Name: ' . $sanitized_fields['name'] . "\n";
      $message .= 'Email: ' . $sanitized_fields['email'] . "\n";
      $message .= 'Affiliation: ' . $sanitized_fields['affiliations'] . "\n";
      $message .= 'Topic: ' . $sanitized_fields['topic'] . "\n";
      $message .= 'Message: ' . $sanitized_fields['message'] . "\n";

      $headers = array('Content-Type: text/plain; charset=UTF-8');

      // if (!wp_mail($to, $subject, $message, $headers)) {
      //   $response['message'] = 'There was an error sending your email. Please try again later.';
      //   $pass = false;
      // }
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
