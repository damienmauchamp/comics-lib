import Controller from './abstract_controller';

export default class extends Controller {

	static targets = ['element']

	connect() {
		// this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
	}

	read(event) {

		const button = event.target;
		const volumeId = button.dataset.volumeId;
		const volume = this.elementTargets.find(e => e.dataset.id === volumeId)

		// console.log('button', button);
		// console.log('volumeId', volumeId);
		// console.log('volume', volume)
		// console.log('this.targets....', this.targets.findAll('element'));
		// console.log('this.elementTargets', this.elementTargets);

		const issueId = volume.dataset.issueId,
			issueNumber = volume.dataset.issueNumber;

		console.log({
			volumeId: volumeId,
			issueId: issueId,
			issueNumber: issueNumber,
		})

		event.preventDefault();

		const url = `/issue/${issueId}/read`;
		// const url = `/volume/${volumeId}/issue/${issueNumber}/read`;
		const form = new FormData();
		// form.append('volumeId', volumeId);
		// form.append('issueId', issueId);
		// form.append('issueNumber', issueNumber);

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

			// marking as read
			this.setVolumeNextToReadIssue(event, volume, data);

			if (!this.isHomepage()) {
				// mark issue as read if we're on the volume page since it'd be listed
				this.setVolumeIssueRead(event, issueId, false);
			}

			return data;

		}).then(data => {

			this.moveVolumeToNextToRead(event, volume);

			return data;

		}).catch(error => {
			console.log(error);
		});
		// issue-id
		// issue-number

		// console.log('event, element', element, volumeId, element.dataset)
		// console.log(volume_id, element.dataset)

		// console.log(element, event.currentTarget)
		// }
		//
		// read(event) {
		// 	const element = event.target
		// 	console.log(element, event.currentTarget)

		// const element = this.elementTarget;
		// console.log('this.elementTarget', this.elementTarget);
		// console.log('this.elementTargets', this.elementTargets);
		// console.log('this.targets', this.targets);


		//
		// const volume_id = this.volume_idTarget.value;
		// const issue_id = this.issue_idTarget.value;
		// const issue_number = this.issue_numberTarget.value;
		// console.log(volume_id, issue_id, issue_number);
	}
}
