import { useEffect, useState } from '@wordpress/element'

const ClassSchedule = () => {
  wp.blocks.registerBlockType("ucscblocks/classschedule", {
    title: "Class Schedule",
    icon: "smiley",
    category: "common",
    attributes: {
    },
    edit: ({ setAttributes, attributes }) => {
      const [dept, setDept] = useState("");

      useEffect(() => {
        fetch('/wp-json/ucscgutenbergblocks/v1/classscheduledept')
          .then(res => res.text())
          .then((text) => {
            const resp = JSON.parse(text);
            setDept(resp.dept);
          });
      }, []);

      return (
        <>
          <h2>Class Schedule Block</h2>
          {dept === "" && (
            <>
              <h4>
                The Class Schedule Dept needs to be set here <a target="_blank" href="/wp-admin/options-general.php?page=ucsc_gutenberg_blocks_settings_page">here.</a>
              </h4>
            </>
          )}
        </>
      );
    },
    save: (props) => {
      return null;
    }
  })
}

export default ClassSchedule;
