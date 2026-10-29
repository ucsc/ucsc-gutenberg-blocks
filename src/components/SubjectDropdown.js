import { SelectControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element'
import { fetchOptions } from './shared/fetchJson';

const SubjectDropdown = ({
  subject,
  setAttributes,
  label,
  disabled = false
}) => {
  const [subjects, setSubjects] = useState([]);
  const [loadFailed, setLoadFailed] = useState(false);

  // WPM-254: an unset subject shows as '---' but is not written back here;
  // calling setAttributes while rendering marked every opened page as edited.
  const [localSubject, setLocalSubject] = useState(subject ?? '---');

  useEffect(() => {
    fetchOptions('/wp-json/ucscgutenbergblocks/v1/subjectcode')
    .then(setSubjects)
    .catch(() => setLoadFailed(true));
  }, []);

  return (
    <>
      <div style={{width: "max-content"}}>
        {subjects.length > 0 && (
          <SelectControl
            label={ label }
            value={ subject ?? '---' }
            options={ subjects }
            onChange={ ( newSubject ) => {
              setAttributes( {subject: newSubject} );
              setLocalSubject(newSubject);
            }}
            disabled={disabled}
          />
        )}
        {!subjects.length && !loadFailed && (
          <span>Subject Dropdown Loading...</span>
        )}
        {loadFailed && (
          <span role="alert">Could not load subjects. Reload the page to try again.</span>
        )}
      </div>
    </>
  )
}

export default SubjectDropdown;

