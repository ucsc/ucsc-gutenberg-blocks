import { SelectControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element'

const SubjectDropdown = ({
  subject,
  setAttributes,
  label,
  disabled = false
}) => {
  const [subjects, setSubjects] = useState([]);

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
    fetch('/wp-json/ucscgutenbergblocks/v1/subjectcode')
    .then(res => res.text())
    .then((text) => {
      const resp = JSON.parse(text);
      setSubjects(resp);
    });
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
        {!subjects.length && (
          <span>Subject Dropdown Loading...</span>
        )}
      </div>
    </>
  )
}

export default SubjectDropdown;

