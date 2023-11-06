const TestDemoUCSC = () => {
	wp.blocks.registerBlockType('ucscservice/gutenberg', {
		title: 'Test Demo UCSC',
		icon: 'smiley',
		category: 'common',
		attributes: {
			skyColor: {
				type: 'string',
			},
			grassColor: {
				type: 'string',
			},
		},
		edit: ({ setAttributes, attributes }) => {
			return (
				<>
					<input
						type="text"
						placeholder="sky"
						value={attributes.skyColor}
						onChange={(e) =>
							setAttributes({ skyColor: e.target.value })
						}
					/>
					<input
						type="text"
						placeholder="grass"
						value={attributes.grassColor}
						onChange={(e) =>
							setAttributes({ grassColor: e.target.value })
						}
					/>
				</>
			);
		},
		save: (props) => {
			return null;
		},
	});
};

export default TestDemoUCSC;
