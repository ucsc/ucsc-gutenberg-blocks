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

  let localSubject;
  let setLocalSubject;
  if (typeof subject === 'undefined') {
    [localSubject, setLocalSubject] = useState('---');
    setAttributes({
      subject: '---'
    });
  } else {
    [localSubject, setLocalSubject] = useState(subject);
  }

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
            value={ subject }
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

