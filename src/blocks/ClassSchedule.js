const ClassSchedule = () => {
  wp.blocks.registerBlockType("ucscblocks/classschedule", {
    title: "Class",
    icon: "smiley",
    category: "common",
    attributes: {
    },
    edit: ({ setAttributes, attributes }) => {
      return (
        <>
          <h2>Class Schedule Edit Block</h2>
        </>
      );
    },
    save: (props) => {
      return null;
    }
  })
}

export default ClassSchedule;
