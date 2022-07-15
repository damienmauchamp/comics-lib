import Controller from './abstract_controller';

export default class extends Controller {

	static targets = ['element']

	connect() {
		// this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
	}

	read(event) {

		const button = event.target;
		const issueId = button.dataset.issueId;
		const issue = this.elementTargets.find(e => e.dataset.id === issueId)

		console.log({
			button: button,
			issueId: issueId,
			issue: issue,
		})

		event.preventDefault();

		const read = Number.parseInt(issue.dataset.read),
			readUnread = read ? 'unread' : 'read';
		const url = `/issue/${issueId}/${readUnread}`;
		const form = new FormData();

		fetch(url, {
			method: 'POST',
			body: form
		}).then(response => {
			if (response.ok) {
				console.log('response', response)
				return response.json();
			}
			throw new Error('Network response was not ok.');
		}).then(json => json.data).then(data => {

			this.setVolumeIssueRead(event, issueId, read);

			return data;

		}).then(data => {

			// if read is in the "Next to read" section, update the next to read section
			const section = document.querySelector('section#volume-next-to-read');

			if (section) {
				const volume = section.querySelector('div.volume');
				// this.setNextToRead(section, data);
				this.setVolumeNextToReadIssue(event, volume, data);
			}

			return data;

		}).catch(error => {
			console.log(error);
		});

	}
}