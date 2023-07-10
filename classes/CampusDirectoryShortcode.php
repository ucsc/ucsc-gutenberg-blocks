<?php


include_once(plugin_dir_path(__FILE__) . 'CampusDirectoryAPI.php');

class CampusDirectoryShortcode
{
  function __construct()
  {
  //  register shortcode
      add_shortcode('ucsc_profiles', array($this, 'ucsc_cdp_profile_render_shortcode'));
// 	  styles can be added later  
//    add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
	}
 
public function ucsc_cdp_profile_render_shortcode($attributes) {

	$sa = shortcode_atts(array(
		'cruzids' => 'cosmo',
		'photo' => true,
		'name' => true,
		'title' => false,
		'phone' => false,
		'email' => false,
		'websites' => false,
		'officelocation' => false,
		'officehours' => false,
		'expertise' => false,
		'profilelinks' => true,
		'biography' => false,
		'areas_of_expertise' => false,
		'research_interests' => false,
		'teaching_interests' => false,
		'awards' => false,
		'publications' => false,
		'displaystyle' => 'grid',
	), $attributes);
	foreach($sa as $key => $value) {
		if($key === 'cruzids' || $key === 'displaystyle') {
			continue;
		}
		if($value === 'true') {
			$sa[$key] = true;
		}
		if($value === 'false') {
			$sa[$key] = false;
		}
	}
	$attrs = array(
		'uids' => $sa['cruzids'],
		'jpegPhoto' => $sa['photo'],
		'cn' => $sa['name'],
		'title' => $sa['title'],
		'telephoneNumber' => $sa['phone'],
		'mail' => $sa['email'],
		'labeledURI' => $sa['websites'],
		'ucscPersonPubOfficeLocationDetail' => $sa['officelocation'],
		'ucscPersonPubOfficeHours' => $sa['officehours'],
		'ucscPersonPubAreaOfExpertise' => $sa['expertise'],
		'profLinks' => $sa['profilelinks'],
		'ucscPersonPubDescription' => $sa['biography'],
		'ucscPersonPubExpertiseReference' => $sa['areas_of_expertise'],
		'ucscPersonPubResearchInterest' => $sa['research_interests'],
		'ucscPersonPubTeachingInterest' => $sa['teaching_interests'],
		'ucscPersonPubAwardsHonorsGrants' => $sa['awards'],
		'ucscPersonPubSelectedPublication' => $sa['publications'],
		'displayStyle' => $sa['displaystyle'],
	);

	$strCruzids = $attrs['uids']; // string with comma separated uids 

	// get data for each profile  
	$campusDirectoryAPI = new CampusDirectoryAPI();
	$itemsShortcode = $campusDirectoryAPI->getCampusDirData($strCruzids, true);
	$uids = preg_split('/[\s,]+/', $attrs['uids']);

//  print " <hr>    ";
//  print_r ($itemsShortcode[0]  ) ;
//  print_r ($attrs);
//	print_r ($attributes['cruzids']);

	$result = '';
 	if($attrs['displayStyle'] === 'list') {
		$result .= $this->render_profiles_list($uids, $attrs, $options, $itemsShortcode);
	} else {
		  $result .= $this->render_profiles_grid($uids, $attrs, $options, $itemsShortcode);
	}
 	return $result;
}

public function render_profiles_grid($uids, $attrs, $options, $itemsShortcode) {
	// $index_map = gen_response_index_map($profiles);
	$attributes = $attrs;
//	$result = '<div class="cdp-profiles cdp-display-' . $attributes['displayStyle'] . ' ' . ucsc_cdp_block_classes($attributes) . '">';
	$result = '<div class="cdp-profiles cdp-display-' . $attributes['displayStyle'] . '">';
	$i = 0; // we are using simple counter to iterate through $itemsShortcode
	foreach($uids as $uid_value) {

	// 	print ' <p>this is uid for the faculty '. $uid_value .$i.'</p>';
	//	print( $itemsShortcode[0][$i]['cn'][0] ) ; 

		$entry = $itemsShortcode[0][$i]   ; 

	  //	print '<hr><pre>'; print_r ($entry);
	  // 	print_r ($entry['telephonenumber'][0] );
	 //	print_r ($attributes);
 
		$profile_uid = $entry['uid'][0];

		$result .= '<div class="cdp-profile grid" id="cdp-profile-';
		$result .= $entry['uid'][0] . '"><ul class="cdp-profile-ul">';
		if($attributes['jpegPhoto']) {
		 	$result .= $this->render_attr_photo($entry, 'jpegphoto');
		}
		if($attributes['cn'] && !empty($entry['cn'][0])) {
			$result .= $this->render_grid_attr('<strong>' . $this->render_attr_cn($entry, 'cn', $options, $attributes, $entry['uid'][0]) . '</strong>');
		}
		if($attributes['title'] && !empty($entry['title'][0])) {
			$result .= $this->render_grid_attr($this->render_attr_single_line($entry, 'title', $options));
		}
		if($attributes['telephoneNumber'] && !empty($entry['telephonenumber'][0])) {
			$result .= $this->render_grid_attr($this->render_attr_multi_line($entry, 'telephonenumber', $options));
		}
		if($attributes['mail'] && !empty($entry['mail'][0])) {
			$result .= $this->render_grid_attr($this->render_attr_mail($entry, 'mail', $options));
		}
		if($attributes['labeledURI'] && !empty($entry['labeledURI'][0])) {
			$result .= $this->render_grid_attr($this->render_attr_labeled_uri($entry, 'labeledURI', $options));
		}
		if($attributes['ucscPersonPubOfficeLocationDetail'] && !empty($entry['ucscPersonPubOfficeLocationDetail'][0])) {
			$office_info = $this->render_attr_multi_line($entry, 'ucscPrimaryLocationPubOfficialName', $options);
			$office_info .= $this->render_attr_multi_line($entry, 'ucscPersonPubOfficeLocationDetail', $options);
			$result .= $this->render_grid_attr($office_info);
		}
		/*
		if($attributes['ucscPersonPubOfficeHours'] && !empty($entry['ucscPersonPubOfficeHours'])) {
			$result .= render_grid_attr(render_attr_multi_line($entry, 'ucscPersonPubOfficeHours', $options));
		}
		if($attributes['ucscPersonPubAreaOfExpertise'] && !empty($entry['ucscPersonPubAreaOfExpertise'])) {
			if($attributes['ucscPersonPubAreaOfExpertise'] === 'short') {
				$result .= render_grid_attr(ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubAreaOfExpertise', $options, $attributes), $options, $profile_uid));
			} else {
				$result .= render_grid_attr(render_attr_single_line($entry, 'ucscPersonPubAreaOfExpertise', $options, $attributes));
			}
		}
		if($attributes['ucscPersonPubDescription'] && !empty($entry['ucscPersonPubDescription'])) {
			if($attributes['ucscPersonPubDescription'] === 'short') {
			        $result .= render_grid_attr(ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubDescription', $options, $attributes), $options, $profile_uid));
			} else {
			        $result .= render_grid_attr(render_attr_single_line($entry, 'ucscPersonPubDescription', $options, $attributes));
			}
		}
		if($attributes['ucscPersonPubExpertiseReference'] && !empty($entry['ucscPersonPubExpertiseReference'])) {
			$result .= render_grid_attr(render_attr_multi_line($entry, 'ucscPersonPubExpertiseReference', $options, $attributes));
		}
		if($attributes['ucscPersonPubResearchInterest'] && !empty($entry['ucscPersonPubResearchInterest'])) {
			if($attributes['ucscPersonPubResearchInterest'] === 'short') {
				$result .= render_grid_attr(ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubResearchInterest', $options, $attributes), $options, $profile_uid));
			} else {
				$result .= render_grid_attr(render_attr_single_line($entry, 'ucscPersonPubResearchInterest', $options, $attributes));
			}
		}
		if($attributes['ucscPersonPubTeachingInterest'] && !empty($entry['ucscPersonPubTeachingInterest'])) {
			if($attributes['ucscPersonPubTeachingInterest'] === 'short') {
				$result .= render_grid_attr(ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubTeachingInterest', $options, $attributes), $options, $profile_uid));
			} else {
				$result .= render_grid_attr(render_attr_single_line($entry, 'ucscPersonPubTeachingInterest', $options, $attributes));
			}
		}
		if($attributes['ucscPersonPubAwardsHonorsGrants'] && !empty($entry['ucscPersonPubAwardsHonorsGrants'])) {
			if($attributes['ucscPersonPubAwardsHonorsGrants'] === 'short') {
				$result .= render_grid_attr(ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubAwardsHonorsGrants', $options, $attributes), $options, $profile_uid));
			} else {
				$result .= render_grid_attr(render_attr_single_line($entry, 'ucscPersonPubAwardsHonorsGrants', $options, $attributes));
			}
		}
		if($attributes['ucscPersonPubSelectedPublication'] && !empty($entry['ucscPersonPubSelectedPublication'])) {
			if($attributes['ucscPersonPubSelectedPublication'] === 'short') {
				$result .= render_grid_attr(ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubSelectedPublication', $options, $attributes), $options, $profile_uid));
			} else {
				$result .= render_grid_attr(render_attr_single_line($entry, 'ucscPersonPubSelectedPublication', $options, $attributes));
			}
		}
		*/
		$result .= '</ul></div>';
	$i++;
	}
	$result .= '</div>';
	return $result;
}

public function render_profiles_list($uids, $attrs, $options, $itemsShortcode) {
	// $index_map = $this->gen_response_index_map($profiles);

	$attributes = $attrs;

	$result = '<div class="cdp-profiles cdp-display-' . $attributes['displayStyle'] . ' ' . $this->ucsc_cdp_block_classes($attributes) . '">';
	$i=0;
	foreach($uids as $uid_value) {
	// print '<p>this is uid for the faculty  '. $uid_value .$i.'</p>';

		$entry = $itemsShortcode[0][$i]   ; 

	//	print '<hr><pre>'; print_r ($entry);
	//	print_r ($entry['mail'][0]);
	//	print_r ($attributes);
	 
		$profile_uid = $entry['uid'][0];
		$result .= '<div class="cdp-list-profile" id="cdp-profile-';
		$result .= $profile_uid . '">';
		if($attributes['cn'] && !empty($entry['cn'])) {
			$result .= '<h4>' . $this->render_attr_cn($entry, 'cn', $options, $attributes, $profile_uid) . '</h4>';
		}
		$result .= '<div class="cdp-list-box"><div class="cdp-list-body"><ul class="cdp-list-render">';
		if($attributes['title'] && !empty($entry['title'])) {
			$result .= $this->render_list_attr('Title', '<li>' . $this->render_attr_single_line($entry, 'title', $options, $attributes) . '</li>');
		}
		if($attributes['telephoneNumber'] && !empty($entry['telephoneNumber'])) {
			$result .= $this->render_list_attr('Phone', '<li>' . $this->render_attr_multi_line($entry, 'telephoneNumber', $options, $attributes) . '</li>');
		}
		if($attributes['mail'] && !empty($entry['mail'])) {
			$result .= $this->render_list_attr('Email', '<li>' . $this->render_attr_mail($entry, 'mail', $options, $attributes) . '</li>');
		}
		if($attributes['labeledURI'] && !empty($entry['labeledURI'])) {
			$result .= $this->render_list_attr('Website', '<li>' . $this->render_attr_labeled_uri($entry, 'labeledURI', $options, $attributes). '</li>');
		}
		if($attributes['ucscPersonPubOfficeLocationDetail'] && !empty($entry['ucscPersonPubOfficeLocationDetail'])) {
			$result .= $this->render_list_attr('Office Location', '<li>' . $this->render_attr_multi_line($entry, 'ucscPrimaryLocationPubOfficialName', $options) . '</li><li>' . render_attr_multi_line($entry, 'ucscPersonPubOfficeLocationDetail', $options, $attributes) . '</li>');
		}
		/*
		if($attributes['ucscPersonPubOfficeHours'] && !empty($entry['ucscPersonPubOfficeHours'])) {
			$result .= render_list_attr('Office Hours', '<li>' . render_attr_multi_line($entry, 'ucscPersonPubOfficeHours', $options, $attributes) . '</li>');
		}
		if($attributes['ucscPersonPubAreaOfExpertise'] && !empty($entry['ucscPersonPubAreaOfExpertise'])) {
			if($attributes['ucscPersonPubAreaOfExpertise'] === 'short') {
				$result .= render_list_attr('Summary of Expertise', '<li>' . ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubAreaOfExpertise', $options, $attributes), $options, $profile_uid) . '</li>');
			} else {
				$result .= render_list_attr('Summary of Expertise', '<li>' . render_attr_single_line($entry, 'ucscPersonPubAreaOfExpertise', $options, $attributes) . '</li>');
			}
		}
		if($attributes['ucscPersonPubDescription'] && !empty($entry['ucscPersonPubDescription'])) {
			if($attributes['ucscPersonPubDescription'] === 'short') {
				$result .= render_list_attr('Biography, Education, and Training', '<li>' . ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubDescription', $options, $attributes), $options, $profile_uid) . '</li>');
			} else {
				$result .= render_list_attr('Biography, Education, and Training', '<li>' . render_attr_single_line($entry, 'ucscPersonPubDescription', $options, $attributes) . '</li>');
			}
		}
		if($attributes['ucscPersonPubExpertiseReference'] && !empty($entry['ucscPersonPubExpertiseReference'])) {
			$result .= render_list_attr('Areas of Expertise', '<li>' . render_attr_multi_line($entry, 'ucscPersonPubExpertiseReference', $options, $attributes) . '</li>');
		}
		if($attributes['ucscPersonPubResearchInterest'] && !empty($entry['ucscPersonPubResearchInterest'])) {
			if($attributes['ucscPersonPubResearchInterest'] === 'short') {
				$result .= render_list_attr('Research Interests', '<li>' . ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubResearchInterest', $options, $attributes), $options, $profile_uid) . '</li>');
			} else {
				$result .= render_list_attr('Research Interests', '<li>' . render_attr_single_line($entry, 'ucscPersonPubResearchInterest', $options, $attributes) . '</li>');
			}
		}
		if($attributes['ucscPersonPubTeachingInterest'] && !empty($entry['ucscPersonPubTeachingInterest'])) {
			if($attributes['ucscPersonPubTeachingInterest'] === 'short') {
				$result .= render_list_attr('Teaching Interests', '<li>' . ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubTeachingInterest', $options, $attributes), $options, $profile_uid) . '</li>');
			} else {
				$result .= render_list_attr('Teaching Interests', '<li>' . render_attr_single_line($entry, 'ucscPersonPubTeachingInterest', $options, $attributes) . '</li>');
			}
		}
		if($attributes['ucscPersonPubAwardsHonorsGrants'] && !empty($entry['ucscPersonPubAwardsHonorsGrants'])) {
			if($attributes['ucscPersonPubAwardsHonorsGrants'] === 'short') {
				$result .= render_list_attr('Awards, Honors, and Grants', '<li>' . ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubAwardsHonorsGrants', $options, $attributes), $options, $profile_uid) . '</li>');
			} else {
				$result .= render_list_attr('Awards, Honors, and Grants', '<li>' . render_attr_single_line($entry, 'ucscPersonPubAwardsHonorsGrants', $options, $attributes) . '</li>');
			}
		}
		if($attributes['ucscPersonPubSelectedPublication'] && !empty($entry['ucscPersonPubSelectedPublication'])) {
			if($attributes['ucscPersonPubSelectedPublication'] === 'short') {
				$result .= render_list_attr('Selected Publications', '<li>' . ucsc_cdp_read_more(render_attr_single_line($entry, 'ucscPersonPubSelectedPublication', $options, $attributes), $options, $profile_uid) . '</li>');
			} else {
				$result .= render_list_attr('Selected Publications', '<li>' . render_attr_single_line($entry, 'ucscPersonPubSelectedPublication', $options, $attributes) . '</li>');
			}
		} */
		$result .= '</ul></div>';
		if($attributes['jpegPhoto']) {
			$result .= $this->render_attr_photo($entry, 'jpegPhoto', $options, $attributes);
		}
		$i++;
		$result .= '</div></div>';
	}
	$result .= '</div>';
	return $result;
}

public function render_attr_cn($values, $val_key, $options, $attributes, $uid) {
	$result = '';
//	print ' render_attr_cn function   ';
// 	print_r ($values)  ;
	if(!empty($values[$val_key])) {
		if($attributes['profLinks']) {
			$result .= '<a style="text-decoration: none" href="https://campusdirectory.ucsc.edu/cd_detail?uid=' . $uid . '">';
			$result .= $values[$val_key][0] . '</a>';
		} else {
			$values[$val_key][0];
		}
	}
	return $result;
}

public function ucsc_cdp_read_more($data, $options, $uid) {
	$original = strip_tags($data);
	$original_length = strlen($original);
	if($original_length < 128) {
		return wp_kses_post($data);
	}
	$result = '<p>' . substr(strip_tags($data), 0, 128);
	$result .= ' <a href="https://campusdirectory.ucsc.edu/cd_detail?uid=' . $uid . '">...more</a></p>';
	return $result;
}
public function render_attr_single_line($values, $val_key) {
	$result = '';
	if(!empty($values[$val_key])) {
		$result .= $values[$val_key][0];
	}
	return $result;
}
public function render_attr_multi_line($values, $val_key) {
	$result = '';
	if(!empty($values[$val_key])) {
		$result .= '<div>' . join('<br />', $values[$val_key]) . '</div>';
	}
	return $result;
}
public function render_attr_labeled_uri($values, $val_key) {
	$result = '';
	if(!empty($values[$val_key])) {
		$result .= '<div>' . join('<br />', array_map('render_attr_labeled_uri_map', $values[$val_key])) . '</div>';
	}
	return $result;
}
public function render_attr_mail($values, $val_key) {
	$result = '';
	if(!empty($values[$val_key])) {
	//	$result .= '<div>' . join('<br />', array_map('render_attr_mail_map', $values[$val_key])) . '</div>';
	 	$result .= '<div> <a style="text-decoration:none" href="mailto:' . $values[$val_key][0] . '">' . $values[$val_key][0] . '</a> </div>';
	}
	return $result;
}
public function render_attr_photo($values, $val_key) {
	$result = '';
//	print_r($values);
	if(!empty($values[$val_key])) {
		$result .= '<div class="square-img" style="background-image: url(\'data:image/jpeg;base64, ' . base64_encode($values[$val_key][0]) . '\')"></div>';
	} else {
		$result .= '<div class="square-img" style="background-image: url(\'//static.ucsc.edu/images/icon-slug.jpg\')"></div>';
	}

	return $result;
}
public function marshal_or_filter_from_uids($uids) { // unknown use 
	$result = '(|';
	foreach($uids as $entry) {
		$result .= '(uid=' . trim($entry) . ')';
	}
	$result .= ')';
	return $result;
}
public function render_attr_mail_map($email) {
	return '<a style="text-decoration:none" href="mailto:' . $email . '">' . $email . '</a>';
}
public function render_attr_labeled_uri_map($labeled_uri) {
	$split = explode(' ', $labeled_uri, 2);
	if(sizeof($split) < 2) {
		return join('<br/>', $labeled_uri);
	}
	return '<a href="' . $split[0] . '">' . $split[1] . '</a>';
}
public function render_list_attr($title, $content) {
	$result = '<li><span class="cdp-li-header">' . $title . '</span><ul class="cdp-inline-list">' . $content . '</ul></li>';
	return $result;
}
public function render_grid_attr($content) {
	$result = '<li>' . $content . '</li>';
	return $result;
}


public function ucsc_cdp_block_classes($attributes) {
        $classes = null;
	if(isset($attributes['align'])) {
		$classes = 'align' . $attributes['align'] . ' ';
	}
	if(isset($attributes['className'])) {
		$classes .= $attributes['className'];
	}
	if($classes === null) {
		return '';
	}
	return $classes;
}

}
