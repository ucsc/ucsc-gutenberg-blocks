const UcscFeedbackForm = () => {
	wp.blocks.registerBlockType('ucscservice/feedback', {
		title: 'Feedback Form',
		icon: 'smiley',
		category: 'common',
		attributes: {
			name: {
				type: 'string',
			},
			namePlaceholder: {
				type: 'string',
			},
			email: {
				type: 'string',
			},
			emailPlaceholder: {
				type: 'string',
			},
			affiliation: {
				type: 'string',
			},
			affiliationPlaceholder: {
				type: 'string',
			},
			topic: {
				type: 'string',
			},
			topicPlaceholder: {
				type: 'string',
			},
			message: {
				type: 'string',
			},
			messagePlaceholder: {
				type: 'string',
			},
			to: {
				type: 'string',
			},
		},
		edit: ({ setAttributes, attributes }) => {
			return (
				<>
					<h4>Change the wording of the text field titles</h4>
					<div>
						<label>Name: </label>
						<div id="name-input">
							<input
								style={{ margin: 3 }}
								type="text"
								id="name"
								placeholder="Name"
								value={attributes.name}
								onChange={(e) =>
									setAttributes({ name: e.target.value })
								}
							/>
							<input
								style={{ margin: 3 }}
								type="text"
								id="namePlaceholder"
								placeholder="Name Placeholder"
								value={attributes.namePlaceholder}
								onChange={(e) =>
									setAttributes({
										namePlaceholder: e.target.value,
									})
								}
							/>
						</div>
					</div>

					<div>
						<label>Email: </label>
						<div id="email-input">
							<input
								style={{ margin: 3 }}
								type="text"
								placeholder="Email"
								value={attributes.email}
								onChange={(e) =>
									setAttributes({ email: e.target.value })
								}
							/>
							<input
								style={{ margin: 3 }}
								type="text"
								placeholder="Email Placeholder"
								value={attributes.emailPlaceholder}
								onChange={(e) =>
									setAttributes({
										emailPlaceholder: e.target.value,
									})
								}
							/>
						</div>
					</div>

					<div>
						<label>Affiliation: </label>
						<div id="affiliation-input">
							<input
								style={{ margin: 3 }}
								type="text"
								placeholder="Affiliation"
								value={attributes.affiliation}
								onChange={(e) =>
									setAttributes({
										affiliation: e.target.value,
									})
								}
							/>
						</div>
					</div>

					<div>
						<label>Topic: </label>
						<div id="topic-input">
							<input
								style={{ margin: 3 }}
								type="text"
								placeholder="Topic"
								value={attributes.topic}
								onChange={(e) =>
									setAttributes({ topic: e.target.value })
								}
							/>
						</div>
					</div>

					<div>
						<label>Message: </label>
						<div id="message-input">
							<input
								style={{ margin: 3 }}
								type="text"
								placeholder="Message"
								value={attributes.message}
								onChange={(e) =>
									setAttributes({ message: e.target.value })
								}
							/>
							<input
								style={{ margin: 3 }}
								type="text"
								placeholder="Message Placeholder"
								value={attributes.messagePlaceholder}
								onChange={(e) =>
									setAttributes({
										messagePlaceholder: e.target.value,
									})
								}
							/>
						</div>
					</div>

					<div>
						<label>Who is receiving the email</label>
						<div id="to-input">
							<input
								style={{ margin: 3 }}
								type="text"
								placeholder="email1, email2, email3..."
								value={attributes.to}
								onChange={(e) =>
									setAttributes({ to: e.target.value })
								}
							/>
						</div>
					</div>
				</>
			);
		},
		save: (props) => {
			return null;
		},
	});
};

export default UcscFeedbackForm;
